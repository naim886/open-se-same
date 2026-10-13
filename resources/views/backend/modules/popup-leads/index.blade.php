@extends('backend.layouts.app')

@section('content')
<div class="row justify-content-center my-5">
    <div class="col-lg-12">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0">📋 Popup Leads</h4>
            <span class="badge bg-secondary fs-6">{{ $leads->total() }} Total</span>
        </div>

        {{-- Search --}}
        <form method="GET" class="mb-3">
            <div class="input-group" style="max-width:400px">
                <input type="text" name="search" class="form-control" placeholder="Search name, phone, address, IP…"
                       value="{{ request('search') }}">
                <button class="btn btn-outline-secondary" type="submit"><i class="fas fa-search"></i></button>
                @if(request('search'))
                    <a href="{{ route('popup-leads.index') }}" class="btn btn-outline-danger">✕</a>
                @endif
            </div>
        </form>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="table-responsive">
            <table class="table theme-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Phone</th>
                        <th>Address</th>
                        <th>IP Address</th>
                        <th>User Agent</th>
                        <th>Submitted At</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($leads as $lead)
                    <tr>
                        <td>{{ $leads->firstItem() + $loop->index }}</td>
                        <td><strong>{{ $lead->name }}</strong></td>
                        <td>
                            <a href="tel:{{ $lead->phone }}">{{ $lead->phone }}</a>
                        </td>
                        <td>{{ $lead->address }}</td>
                        <td><small class="text-muted">{{ $lead->ip_address }}</small></td>
                        <td>
                            <small class="text-muted" style="max-width:220px;display:inline-block;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"
                                   title="{{ $lead->user_agent }}">{{ $lead->user_agent }}</small>
                        </td>
                        <td>{{ $lead->created_at->format('d M Y, h:ia') }}</td>
                        <td>
                            <form action="{{ route('popup-leads.destroy', $lead->id) }}" method="POST"
                                  onsubmit="return confirm('Delete this lead?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">No leads yet.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        {{ $leads->links() }}

    </div>
</div>
@endsection
