@extends('backend.layouts.app')

@section('content')
<div class="row justify-content-center my-5">
    <div class="col-lg-12">
        <div class="table-responsive">
            <table class="table theme-table">
                <thead>
                <tr>
                    <th>SL</th>
                    <th>Label</th>
                    <th>Key</th>
                    <th>Type</th>
                    <th>Value Preview</th>
                    <th>Action</th>
                </tr>
                </thead>
                <tbody>
                @forelse($contents as $item)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td><strong>{{ $item->label }}</strong></td>
                        <td><code>{{ $item->key }}</code></td>
                        <td>
                            @if($item->type === 'html')
                                <span class="badge bg-info">Rich Text</span>
                            @elseif($item->type === 'textarea')
                                <span class="badge bg-secondary">Textarea</span>
                            @else
                                <span class="badge bg-secondary">Text</span>
                            @endif
                        </td>
                        <td>
                            @if($item->type === 'html')
                                <em class="text-muted" style="font-size:12px;">{{ Str::limit(strip_tags($item->value), 80) }}</em>
                            @else
                                {{ Str::limit($item->value, 80) }}
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('site-content.edit', $item) }}" class="btn btn-sm btn-warning me-1">
                                <i class="fa fa-edit"></i>
                            </a>
                            <form action="{{ route('site-content.destroy', $item) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('Delete this content?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-danger"><i class="fa fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted">No content entries found.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">{{ $contents->links() }}</div>
    </div>
</div>
@endsection
