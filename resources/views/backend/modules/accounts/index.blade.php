@extends('backend.layouts.app')

@section('title', 'Accounts')

@section('content')
<div class="container-fluid my-4">

    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
        <div>
            <h4 class="mb-0 fw-bold"><i class="fas fa-wallet me-2 text-primary"></i>Accounts</h4>
            <div class="small text-muted mt-1">Track money across bKash, Bank, and Cash</div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show"><i class="fas fa-check-circle me-2"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    {{-- ── Account Balance Cards ── --}}
    <div class="row g-3 mb-4">
        @php
            $totalBadgeClass = $totalBalance >= 0 ? 'success' : 'danger';
        @endphp
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100 text-center">
                <div class="card-body py-4">
                    <div class="text-muted small mb-1">Total Balance</div>
                    <div class="fw-bold fs-2 {{ $totalBalance >= 0 ? 'text-success' : 'text-danger' }}">৳{{ number_format($totalBalance, 0) }}</div>
                    <div class="text-muted small">All accounts combined</div>
                </div>
            </div>
        </div>
        @foreach(\App\Models\AccountTransaction::ACCOUNT_LIST as $key => $accLabel)
        @php $bal = $balances[$key] ?? 0; $color = \App\Models\AccountTransaction::ACCOUNT_COLORS[$key]; $icon = \App\Models\AccountTransaction::ACCOUNT_ICONS[$key]; @endphp
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100 text-center">
                <div class="card-body py-4">
                    <div class="mb-2">
                        <i class="{{ $icon }} fa-2x text-{{ $color }}"></i>
                    </div>
                    <div class="text-muted small mb-1">{{ $accLabel }}</div>
                    <div class="fw-bold fs-2 {{ $bal >= 0 ? 'text-success' : 'text-danger' }}">৳{{ number_format($bal, 0) }}</div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <div class="row g-4">
        {{-- ── Add Transaction Form ── --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-bottom fw-semibold py-3">
                    <i class="fas fa-plus-circle text-primary me-2"></i>Add Transaction
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('accounts.store') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Account <span class="text-danger">*</span></label>
                            <select name="account" class="form-select form-select-sm @error('account') is-invalid @enderror" required>
                                <option value="">Select Account</option>
                                @foreach(\App\Models\AccountTransaction::ACCOUNT_LIST as $key => $lbl)
                                    <option value="{{ $key }}" {{ old('account') == $key ? 'selected' : '' }}>{{ $lbl }}</option>
                                @endforeach
                            </select>
                            @error('account')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Type <span class="text-danger">*</span></label>
                            <div class="d-flex gap-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="type" id="typeCredit" value="credit" {{ old('type','credit') == 'credit' ? 'checked' : '' }} required>
                                    <label class="form-check-label text-success fw-semibold" for="typeCredit"><i class="fas fa-arrow-down me-1"></i>Credit (In)</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="type" id="typeDebit" value="debit" {{ old('type') == 'debit' ? 'checked' : '' }}>
                                    <label class="form-check-label text-danger fw-semibold" for="typeDebit"><i class="fas fa-arrow-up me-1"></i>Debit (Out)</label>
                                </div>
                            </div>
                            @error('type')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Amount (৳) <span class="text-danger">*</span></label>
                            <input type="number" name="amount" step="0.01" min="0.01" class="form-control form-control-sm @error('amount') is-invalid @enderror" value="{{ old('amount') }}" placeholder="0.00" required>
                            @error('amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Date <span class="text-danger">*</span></label>
                            <input type="date" name="date" class="form-control form-control-sm @error('date') is-invalid @enderror" value="{{ old('date', date('Y-m-d')) }}" required>
                            @error('date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Purpose</label>
                            <input type="text" name="purpose" class="form-control form-control-sm" value="{{ old('purpose') }}" placeholder="e.g. Order payment, Salary, Expense...">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Reference #</label>
                            <input type="text" name="reference" class="form-control form-control-sm" value="{{ old('reference') }}" placeholder="Order #, Invoice #...">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Note</label>
                            <textarea name="note" class="form-control form-control-sm" rows="2" placeholder="Optional note...">{{ old('note') }}</textarea>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-plus me-1"></i>Add Transaction
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- ── Transaction List ── --}}
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-bottom fw-semibold py-3 d-flex align-items-center justify-content-between">
                    <div><i class="fas fa-list text-primary me-2"></i>Transaction History</div>
                    <span class="badge bg-secondary fw-normal">{{ $transactions->total() }} total</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-sm mb-0 align-middle">
                            <thead class="thead-dm">
                                <tr>
                                    <th class="ps-3">Date</th>
                                    <th>Account</th>
                                    <th>Type</th>
                                    <th>Purpose</th>
                                    <th>Reference</th>
                                    <th class="text-end">Amount</th>
                                    <th class="text-end pe-3">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($transactions as $txn)
                                <tr>
                                    <td class="ps-3 text-muted small">{{ $txn->date->format('d M Y') }}</td>
                                    <td>
                                        @php $aColor = \App\Models\AccountTransaction::ACCOUNT_COLORS[$txn->account] ?? 'secondary'; @endphp
                                        <span class="badge bg-{{ $aColor }} bg-opacity-10 text-{{ $aColor }} border border-{{ $aColor }}">
                                            <i class="{{ \App\Models\AccountTransaction::ACCOUNT_ICONS[$txn->account] ?? '' }} me-1"></i>
                                            {{ \App\Models\AccountTransaction::ACCOUNT_LIST[$txn->account] ?? $txn->account }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($txn->type === 'credit')
                                            <span class="badge bg-success bg-opacity-15 text-success border border-success"><i class="fas fa-arrow-down me-1"></i>Credit</span>
                                        @else
                                            <span class="badge bg-danger bg-opacity-15 text-danger border border-danger"><i class="fas fa-arrow-up me-1"></i>Debit</span>
                                        @endif
                                    </td>
                                    <td class="small">{{ $txn->purpose ?: '—' }}</td>
                                    <td class="small text-muted">{{ $txn->reference ?: '—' }}</td>
                                    <td class="text-end fw-bold {{ $txn->type === 'credit' ? 'text-success' : 'text-danger' }}">
                                        {{ $txn->type === 'credit' ? '+' : '-' }}৳{{ number_format($txn->amount, 0) }}
                                    </td>
                                    <td class="text-end pe-3">
                                        <form method="POST" action="{{ route('accounts.destroy', $txn->id) }}" onsubmit="return confirm('Delete this transaction?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-2">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="7" class="text-center text-muted py-4">No transactions yet. Add one using the form on the left.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($transactions->hasPages())
                    <div class="px-3 py-3">
                        {{ $transactions->links() }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
