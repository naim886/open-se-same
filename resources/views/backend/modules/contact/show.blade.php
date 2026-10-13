@extends('backend.layouts.app')

@section('content')
<div class="row justify-content-center my-5">
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-body">

                {{-- Header --}}
                <div class="d-flex justify-content-between align-items-start mb-4 flex-wrap gap-2">
                    <div>
                        <h5 class="fw-bold mb-1">{{ $contact->subject }}</h5>
                        <small style="color:var(--theme-text-color); opacity:0.7;">
                            Received {{ $contact->created_at->format('d M Y \a\t h:i A') }}
                            &nbsp;&bull;&nbsp; {{ $contact->created_at->diffForHumans() }}
                        </small>
                    </div>
                    <span class="badge bg-{{ \App\Models\ContactMessage::STATUS_COLORS[$contact->status] ?? 'secondary' }} fs-6">
                        {{ \App\Models\ContactMessage::STATUS_LIST[$contact->status] ?? '—' }}
                    </span>
                </div>

                {{-- Sender Details --}}
                <table class="table table-bordered table-sm mb-4">
                    <tr>
                        <th style="width:140px;">Name</th>
                        <td>{{ $contact->name }}</td>
                    </tr>
                    <tr>
                        <th>Email</th>
                        <td>
                            <a href="mailto:{{ $contact->email }}" style="color:inherit;">
                                {{ $contact->email }}
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <th>Phone</th>
                        <td>
                            @if($contact->phone)
                                <a href="tel:{{ $contact->phone }}" style="color:inherit;">{{ $contact->phone }}</a>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Subject</th>
                        <td>{{ $contact->subject }}</td>
                    </tr>
                </table>

                {{-- Message Body --}}
                <h6 class="fw-bold mb-2">Message</h6>
                <div class="border rounded p-3" style="white-space:pre-wrap; line-height:1.8; font-size:14.5px;">{{ $contact->message }}</div>

                {{-- Actions --}}
                <div class="mt-4 d-flex flex-wrap gap-2">
                    <a href="mailto:{{ $contact->email }}" class="btn btn-success">
                        <i class="fa fa-reply me-1"></i> Reply via Email
                    </a>
                    @if($contact->phone)
                        <a href="https://wa.me/{{ preg_replace('/\D/', '', $contact->phone) }}" target="_blank" class="btn btn-success">
                            <i class="fab fa-whatsapp me-1"></i> WhatsApp
                        </a>
                    @endif
                    <a href="{{ route('contact.index') }}" class="btn btn-secondary">
                        <i class="fa fa-arrow-left me-1"></i> Back to List
                    </a>
                    <form action="{{ route('contact.destroy', $contact) }}" method="POST" class="d-inline"
                          onsubmit="return confirm('Delete this message permanently?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-danger">
                            <i class="fa fa-trash me-1"></i> Delete
                        </button>
                    </form>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection
