@extends('backend.layouts.app')

@section('content')
<div class="row justify-content-center my-5">
    <div class="col-lg-12">

        {{-- Search & Filter Bar --}}
        <form method="GET" action="{{ route('contact.index') }}" class="row g-2 mb-4">
            <div class="col-md-5">
                <input type="text" name="search" value="{{ request('search') }}"
                       class="form-control" placeholder="Search name, email, subject…">
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    @foreach(\App\Models\ContactMessage::STATUS_LIST as $k => $v)
                        <option value="{{ $k }}" {{ request('status') == $k ? 'selected' : '' }}>{{ $v }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fa fa-search me-1"></i> Filter
                </button>
            </div>
            <div class="col-md-2">
                <a href="{{ route('contact.index') }}" class="btn btn-secondary w-100">
                    <i class="fa fa-times me-1"></i> Reset
                </a>
            </div>
        </form>

        {{-- Table --}}
        <div class="table-responsive">
            <table class="table theme-table">
                <thead>
                    <tr>
                        <th>SL</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Subject</th>
                        <th>Status</th>
                        <th>Received</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($messages as $msg)
                    <tr {{ $msg->isUnread() ? 'style=font-weight:600;' : '' }}>
                        <td>{{ $loop->iteration }}</td>
                        <td>
                            @if($msg->isUnread())
                                <span class="badge bg-warning text-dark me-1" title="Unread">
                                    <i class="fa fa-envelope"></i>
                                </span>
                            @endif
                            {{ $msg->name }}
                        </td>
                        <td><a href="mailto:{{ $msg->email }}" style="color:inherit;">{{ $msg->email }}</a></td>
                        <td>{{ $msg->phone ?? '—' }}</td>
                        <td>{{ Str::limit($msg->subject, 40) }}</td>
                        <td>
                            <span class="badge bg-{{ \App\Models\ContactMessage::STATUS_COLORS[$msg->status] ?? 'secondary' }}">
                                {{ \App\Models\ContactMessage::STATUS_LIST[$msg->status] ?? '—' }}
                            </span>
                        </td>
                        <td>{{ $msg->created_at->format('d M Y, h:i A') }}</td>
                        <td>
                            <a href="{{ route('contact.show', $msg) }}" class="btn btn-sm btn-info me-1" title="View">
                                <i class="fa fa-eye"></i>
                            </a>
                            <form action="{{ route('contact.destroy', $msg) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('Delete this message permanently?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-danger" title="Delete">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">
                            <i class="fa fa-inbox fa-2x d-block mb-2"></i> No messages found.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">{{ $messages->withQueryString()->links() }}</div>
    </div>
</div>
@endsection
