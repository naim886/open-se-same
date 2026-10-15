@extends('backend.layouts.app')

@section('content')
<div class="row justify-content-center my-5">
    <div class="col-lg-10">

        <div class="d-flex align-items-center justify-content-between mb-4">
            <h4 class="mb-0"><i class="fas fa-search me-2"></i>Search Keywords</h4>
            <span class="text-muted" style="font-size:13px;">
                {{ $keywords->total() }} unique keyword{{ $keywords->total() !== 1 ? 's' : '' }}
            </span>
        </div>

        <div class="table-responsive">
            <table class="table theme-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Keyword</th>
                        <th>Search Count</th>
                        <th>Last Searched</th>
                        <th>First Searched</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($keywords as $i => $kw)
                    <tr>
                        <td>{{ ($keywords->currentPage() - 1) * $keywords->perPage() + $i + 1 }}</td>
                        <td>
                            <span class="fw-600" style="font-size:14px;">{{ $kw->keyword }}</span>
                        </td>
                        <td>
                            <span class="badge bg-primary" style="font-size:13px;padding:5px 12px;">
                                {{ number_format($kw->count) }}
                            </span>
                        </td>
                        <td style="font-size:13px;color:var(--theme-link-color);">{{ $kw->updated_at?->format('d M Y, h:i A') }}</td>
                        <td style="font-size:13px;color:var(--theme-link-color);">{{ $kw->created_at?->format('d M Y, h:i A') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">No search keywords recorded yet.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $keywords->links() }}
        </div>

    </div>
</div>
@endsection
