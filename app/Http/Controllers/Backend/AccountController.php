<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\AccountTransaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function index(): View
    {
        // Per-account balance
        $balances = [];
        foreach (AccountTransaction::ACCOUNT_LIST as $key => $label) {
            $balances[$key] = AccountTransaction::balanceFor($key);
        }
        $totalBalance = AccountTransaction::totalBalance();

        // Recent 100 transactions, newest first
        $transactions = AccountTransaction::with('user')
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->paginate(50);

        $cms_content = [
            'page_title' => 'Accounts',
            'module'     => 'Financial',
            'sub_module' => 'Accounts',
        ];

        return view('backend.modules.accounts.index', compact(
            'cms_content', 'balances', 'totalBalance', 'transactions'
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
