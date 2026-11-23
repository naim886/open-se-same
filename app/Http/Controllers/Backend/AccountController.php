<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\AccountTransaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function index(Request $request): View
    {
        // Per-account balance
        $balances = [];
        foreach (AccountTransaction::ACCOUNT_LIST as $key => $label) {
            $balances[$key] = AccountTransaction::balanceFor($key);
        }
        $totalBalance = AccountTransaction::totalBalance();

        $search  = trim((string) $request->input('search', ''));
        $account = (string) $request->input('account', '');
        $type    = (string) $request->input('type', '');

        // Recent transactions, newest first
        $transactions = AccountTransaction::with('user')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('purpose', 'like', "%{$search}%")
                        ->orWhere('reference', 'like', "%{$search}%")
                        ->orWhere('note', 'like', "%{$search}%");
                });
            })
            ->when($account !== '', fn ($query) => $query->where('account', $account))
            ->when($type !== '', fn ($query) => $query->where('type', $type))
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->paginate(50)
            ->withQueryString();

        $cms_content = [
            'page_title' => 'Accounts',
            'module'     => 'Financial',
            'sub_module' => 'Accounts',
        ];

        return view('backend.modules.accounts.index', compact(
            'cms_content', 'balances', 'totalBalance', 'transactions', 'search', 'account', 'type'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'account'   => 'required|in:bkash,bank,cash',
            'type'      => 'required|in:credit,debit',
            'amount'    => 'required|numeric|min:0.01',
            'purpose'   => 'nullable|string|max:255',
            'reference' => 'nullable|string|max:100',
            'note'      => 'nullable|string|max:1000',
            'date'      => 'required|date',
        ]);

        $data['user_id'] = auth()->id();

        AccountTransaction::create($data);

        return back()->with('success', 'Transaction added successfully.');
    }

    public function destroy(AccountTransaction $accountTransaction): RedirectResponse
    {
        $accountTransaction->delete();
        return back()->with('success', 'Transaction deleted.');
    }
}
