<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Http\Requests\StoreExpenseRequest;
use App\Http\Requests\UpdateExpenseRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $query = Expense::with('user')->orderBy('date', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('date', '<=', $request->date_to);
        }
        if ($request->filled('search')) {
            $query->where('purpose', 'like', '%' . $request->search . '%');
        }

        $expenses = $query->paginate(20)->withQueryString();

        $stats = [
            'total'    => Expense::sum('amount'),
            'approved' => Expense::where('status', Expense::STATUS_APPROVED)->sum('amount'),
            'pending'  => Expense::where('status', Expense::STATUS_PENDING)->sum('amount'),
            'count'    => Expense::count(),
        ];

        return view('backend.modules.expense.index', compact('expenses', 'stats'));
    }

    public function create()
    {
        return view('backend.modules.expense.create');
    }

    public function store(StoreExpenseRequest $request)
    {
        try {
            $data = $request->validated();
            $data['user_id'] = auth()->id();

            if ($request->hasFile('attachment')) {
                $data['attachment'] = $request->file('attachment')
                    ->store('expenses/attachments', 'public');
            }

            Expense::create($data);

            return redirect()->route('expense.index')
                ->with('success', 'Expense added successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()
                ->with('error', 'Failed to add expense. Please try again.');
        }
    }

    public function show(Expense $expense)
    {
        $expense->load('user');
        return view('backend.modules.expense.show', compact('expense'));
    }

    public function edit(Expense $expense)
    {
        return view('backend.modules.expense.edit', compact('expense'));
    }

    public function update(UpdateExpenseRequest $request, Expense $expense)
    {
        try {
            $data = $request->validated();

            if ($request->hasFile('attachment')) {
                // delete old file
                if ($expense->attachment) {
                    Storage::disk('public')->delete($expense->attachment);
                }
                $data['attachment'] = $request->file('attachment')
                    ->store('expenses/attachments', 'public');
            }

            $expense->update($data);

            return redirect()->route('expense.index')
                ->with('success', 'Expense updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()
                ->with('error', 'Failed to update expense. Please try again.');
        }
    }

    public function destroy(Expense $expense)
    {
        try {
            if ($expense->attachment) {
                Storage::disk('public')->delete($expense->attachment);
            }
            $expense->delete();
            return redirect()->route('expense.index')
                ->with('success', 'Expense deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to delete expense.');
        }
    }
}
