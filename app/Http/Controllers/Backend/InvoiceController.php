<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Setting;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class InvoiceController extends Controller
{
    /**
     * A4 invoice / money receipt.
     */
    public function invoice(Order $order): Response
    {
        $order->load(['orderItems']);

        $pdf = Pdf::loadView('backend.modules.order.pdf.invoice', [
            'order'   => $order,
            'company' => $this->companyDetails(),
        ])
            ->setPaper('a4')
            ->setOption('isFontSubsettingEnabled', true);

        return $pdf->download('invoice-' . $order->order_number . '.pdf');
    }

    /**
     * Courier consignment label (4in x 6in thermal label).
     */
    public function label(Order $order): Response
    {
        $order->load(['orderItems']);

        // 1 inch = 72 points, so 4in x 6in = 288 x 432.
        $pdf = Pdf::loadView('backend.modules.order.pdf.label', [
            'order'   => $order,
            'company' => $this->companyDetails(),
        ])
            ->setPaper([0, 0, 288, 432])
            ->setOption('isFontSubsettingEnabled', true);

        return $pdf->download('label-' . $order->order_number . '.pdf');
    }

    /**
     * Branding details used on printed documents.
     *
     * @return array<string, mixed>
     */
    private function companyDetails(): array
    {
        return [
            'name'    => Setting::get('invoice.company_name'),
            'address' => Setting::get('invoice.company_address'),
            'phone'   => Setting::get('invoice.company_phone'),
            'email'   => Setting::get('invoice.company_email'),
            'footer'  => Setting::get('invoice.footer_note'),
            'terms'   => Setting::get('invoice.terms'),
        ];
    }
}
