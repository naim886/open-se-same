<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Setting;
use App\Services\Payment\NagadService;
use App\Services\Payment\PaymentService;
use App\Services\Payment\SSLCommerzService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function __construct(private readonly PaymentService $payments) {}

    public function sslSuccess(Request $request): RedirectResponse
    {
        $order = $this->findOrder((string) $request->input('tran_id'));

        if (! $order) {
            Log::warning('[SSLCommerz] Success callback: order not found', $request->all());

            return redirect()->route('shop.home')->with('error', 'Payment completed, but the order could not be found.');
        }

        if ((int) $order->payment_status === Order::PAYMENT_STATUS_PAID) {
            return $this->redirectToSuccess($order);
        }

        $valId = (string) $request->input('val_id', '');

        if ($valId === '') {
            return $this->failedRedirect($order, 'sslcommerz', 'missing_val_id', null, $request->all(), 'Payment validation ID was missing.');
        }

        $validation = (new SSLCommerzService)->validate($valId);

        if (! $this->sslValidationPassed($order, $validation)) {
            return $this->failedRedirect(
                $order,
                'sslcommerz',
                (string) ($validation['status'] ?? $request->input('status', 'invalid')),
                $valId,
                ['callback' => $request->all(), 'validation' => $validation],
                $validation['status'] ?? 'Payment could not be validated.'
            );
        }

        $transactionId = (string) ($validation['bank_tran_id'] ?? $request->input('bank_tran_id', ''));

        $this->markPaid($order, 'sslcommerz', $transactionId ?: null, $valId, [
            'callback' => $request->all(),
            'validation' => $validation,
        ]);

        return $this->redirectToSuccess($order);
    }

    public function sslFail(Request $request): RedirectResponse
    {
        return $this->sslFailure($request, 'failed');
    }

    public function sslCancel(Request $request): RedirectResponse
    {
        return $this->sslFailure($request, 'cancelled');
    }

    public function sslIpn(Request $request): Response
    {
        $order = $this->findOrder((string) $request->input('tran_id'));

        if (! $order) {
            Log::warning('[SSLCommerz] IPN: order not found', $request->all());

            return response('ORDER_NOT_FOUND', 200);
        }

        if ((int) $order->payment_status === Order::PAYMENT_STATUS_PAID) {
            return response('OK', 200);
        }

        $valId = (string) $request->input('val_id', '');

        if ($valId === '') {
            return response('MISSING_VAL_ID', 200);
        }

        $validation = (new SSLCommerzService)->validate($valId);

        if (! $this->sslValidationPassed($order, $validation)) {
            Log::warning('[SSLCommerz] IPN validation failed', [
                'order' => $order->order_number,
                'callback' => $request->all(),
                'validation' => $validation,
            ]);

            return response('INVALID', 200);
        }

        $transactionId = (string) ($validation['bank_tran_id'] ?? $request->input('bank_tran_id', ''));

        $this->markPaid($order, 'sslcommerz', $transactionId ?: null, $valId, [
            'callback' => $request->all(),
            'validation' => $validation,
            'source' => 'ipn',
        ]);

        return response('OK', 200);
    }

    public function nagadCallback(Request $request): RedirectResponse
    {
        $orderNumber = (string) (
            $request->input('order_id')
            ?? $request->input('orderId')
            ?? ''
        );

        $paymentReferenceId = (string) (
            $request->input('payment_ref_id')
            ?? $request->input('paymentRefId')
            ?? ''
        );

        $order = $this->findOrder($orderNumber);

        // Some Nagad callback variants omit the order id; the payment reference
        // stored when checkout started identifies the order just as well.
        if (! $order && $paymentReferenceId !== '') {
            $order = $this->findOrder($paymentReferenceId);
        }

        if (! $order) {
            Log::warning('[Nagad] Callback: order not found', $request->all());

            return redirect()->route('shop.home')->with('error', 'Payment completed, but the order could not be found.');
        }

        if ((int) $order->payment_status === Order::PAYMENT_STATUS_PAID) {
            return $this->redirectToSuccess($order);
        }

        $status = strtolower((string) $request->input('status', ''));

        if ($status !== 'success' || $paymentReferenceId === '') {
            return $this->failedRedirect(
                $order,
                'nagad',
                $status ?: 'failed',
                $paymentReferenceId ?: null,
                $request->all(),
                (string) ($request->input('message') ?: 'Nagad payment was not successful.')
            );
        }

        $verification = (new NagadService)->verify($paymentReferenceId);

        if (! $this->nagadValidationPassed($order, $paymentReferenceId, $verification)) {
            return $this->failedRedirect(
                $order,
                'nagad',
                (string) ($verification['status'] ?? 'verification_failed'),
                $paymentReferenceId,
                ['callback' => $request->all(), 'verification' => $verification],
                (string) ($verification['status'] ?? 'Nagad payment could not be verified.')
            );
        }

        $transactionId = (string) (
            $verification['issuerPaymentRefNo']
            ?? $request->input('issuer_payment_ref')
            ?? $paymentReferenceId
        );

        $this->markPaid($order, 'nagad', $transactionId ?: null, $paymentReferenceId, [
            'callback' => $request->all(),
            'verification' => $verification,
        ]);

        return $this->redirectToSuccess($order);
    }

    /**
     * Re-initiate an unpaid online payment from a signed retry URL.
     */
    public function retry(Order $order): RedirectResponse
    {
        if ((int) $order->payment_status === Order::PAYMENT_STATUS_PAID) {
            return $this->redirectToSuccess($order);
        }

        if (! in_array((int) $order->payment_method, [
            Order::PAYMENT_METHOD_SSLCOMMERZ,
            Order::PAYMENT_METHOD_NAGAD,
        ], true)) {
            return redirect()->route('shop.checkout')->with('error', 'This order does not use an online payment method.');
        }

        try {
            $result = $this->payments->start($order);
        } catch (\Throwable $e) {
            Log::error('[Payment] Retry start failed', [
                'order' => $order->id,
                'message' => $e->getMessage(),
            ]);

            $result = [
                'status' => 'FAILED',
                'gateway' => $order->payment_gateway ?: 'gateway',
                'message' => $e->getMessage(),
            ];
        }

        if (($result['status'] ?? '') === 'SUCCESS' && ! empty($result['url'])) {
            return redirect()->away((string) $result['url']);
        }

        return $this->failedRedirect(
            $order,
            (string) ($result['gateway'] ?? 'gateway'),
            'initiation_failed',
            $result['reference'] ?? null,
            $result['response'] ?? $result,
            (string) ($result['message'] ?? 'Could not start the payment gateway.')
        );
    }

    public function failed(Order $order): View
    {
        $retryUrl = URL::temporarySignedRoute(
            'payment.retry',
            now()->addHours(24),
            ['order' => $order->id]
        );

        return view('frontend.pages.payment-failed', [
            'order' => $order,
            'retryUrl' => $retryUrl,
            'message' => session('payment_error', 'Your payment could not be completed.'),
        ]);
    }

    private function sslFailure(Request $request, string $status): RedirectResponse
    {
        $order = $this->findOrder((string) $request->input('tran_id'));

        if (! $order) {
            return redirect()->route('shop.home')->with('error', 'Payment was not completed and the order could not be found.');
        }

        return $this->failedRedirect(
            $order,
            'sslcommerz',
            $status,
            $request->input('val_id'),
            $request->all(),
            (string) ($request->input('failedreason') ?: "SSLCommerz payment {$status}.")
        );
    }

    private function findOrder(string $reference): ?Order
    {
        if ($reference === '') {
            return null;
        }

        return Order::query()
            ->where('order_number', $reference)
            ->orWhere('payment_reference', $reference)
            ->first();
    }

    /**
     * @param  array<string, mixed>  $validation
     */
    private function sslValidationPassed(Order $order, array $validation): bool
    {
        $status = strtoupper(trim((string) ($validation['status'] ?? '')));

        if (! in_array($status, ['VALID', 'VALIDATED'], true)) {
            return false;
        }

        if ((string) ($validation['tran_id'] ?? '') !== (string) $order->order_number) {
            return false;
        }

        if (strtoupper(trim((string) ($validation['currency'] ?? 'BDT'))) !== 'BDT') {
            return false;
        }

        return (float) ($validation['amount'] ?? 0) + 0.01 >= (float) $order->total_amount;
    }

    /**
     * @param  array<string, mixed>  $verification
     */
    private function nagadValidationPassed(Order $order, string $paymentReferenceId, array $verification): bool
    {
        $status = strtolower(trim((string) ($verification['status'] ?? '')));

        if ($status !== 'success') {
            return false;
        }

        $statusCode = trim((string) ($verification['statusCode'] ?? ''));

        if (! str_starts_with($statusCode, '000') && ! str_starts_with($statusCode, '00_0000')) {
            return false;
        }

        if ((string) ($verification['orderId'] ?? '') !== (string) $order->order_number) {
            return false;
        }

        $merchantId = trim((string) Setting::effective('payment.nagad.merchant_id', ''));

        if ($merchantId !== '' && (string) ($verification['merchantId'] ?? '') !== $merchantId) {
            return false;
        }

        if ((string) ($verification['paymentRefId'] ?? '') !== $paymentReferenceId) {
            return false;
        }

        return (float) ($verification['amount'] ?? 0) + 0.01 >= (float) $order->total_amount;
    }

    private function markPaid(Order $order, string $gateway, ?string $transactionId, ?string $reference, array $payload): string
    {
        return $this->payments->markPaid($order, $gateway, $transactionId, $reference, $payload);
    }

    private function redirectToSuccess(Order $order): RedirectResponse
    {
        $eventId = (string) ($order->purchase_event_id ?? '');

        if ($eventId === '') {
            $eventId = $this->payments->markPaid(
                $order,
                (string) ($order->payment_gateway ?: 'gateway'),
                $order->payment_transaction_id,
                $order->payment_reference,
                []
            );
        }

        return redirect()->route('shop.order.success')
            ->with('success', 'আপনার পেমেন্ট সফল হয়েছে!')
            ->with('order_number', $order->order_number)
            ->with('delivery_location', (int) $order->delivery_location)
            ->with('purchase_event_id', $eventId)
            ->with('order_details', $this->payments->orderDetails($order));
    }

    private function failedRedirect(
        Order $order,
        string $gateway,
        string $status,
        ?string $reference,
        array $payload,
        string $message
    ): RedirectResponse {
        $this->payments->markFailed($order, $gateway, $status, $reference, $payload);

        $url = URL::temporarySignedRoute(
            'payment.failed',
            now()->addHours(24),
            ['order' => $order->id]
        );

        return redirect($url)->with('payment_error', $message);
    }
}
