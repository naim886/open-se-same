@extends('backend.layouts.app')

@section('content')
<div class="row justify-content-center my-5">
    <div class="col-lg-12">

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="table-responsive">
            <table class="table theme-table" id="topSellingTable">
                <thead>
                    <tr>
                        <th style="width:40px;"><i class="fas fa-grip-vertical text-muted"></i></th>
                        <th style="width:60px;">Sort</th>
                        <th style="width:60px;">Photo</th>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="sortableBody">
                @forelse($items as $item)
                    <tr data-id="{{ $item->id }}">
                        <td class="drag-handle" style="cursor:grab; color:#aaa;"><i class="fas fa-grip-vertical"></i></td>
                        <td>{{ $item->sort_order }}</td>
                        <td>
                            @if($item->product && $item->product->primary_photo_thumb)
                                <img src="{{ asset('storage/'.$item->product->primary_photo_thumb) }}"
                                     style="width:44px;height:44px;object-fit:cover;border-radius:6px;">
                            @else
                                <div style="width:44px;height:44px;background:#f0f0f0;border-radius:6px;"></div>
                            @endif
                        </td>
                        <td>
                            <strong>{{ $item->product->name ?? '—' }}</strong><br>
                            <small class="text-muted">ID: {{ $item->product_id }}</small>
                        </td>
                        <td>
                            @if($item->product)
                                @if($item->product->product_type === 'group_with_variant')
                                    <span class="text-muted" style="font-size:12px;">Variant Pricing</span>
                                @elseif($item->product->sale_price && $item->product->price > $item->product->sale_price)
                                    ৳{{ number_format($item->product->sale_price, 0) }}
                                    <br><small class="text-muted text-decoration-line-through">৳{{ number_format($item->product->price, 0) }}</small>
                                @elseif($item->product->price)
                                    ৳{{ number_format($item->product->price, 0) }}
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            @endif
                        </td>
                        <td>
                            @if($item->status)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-secondary">Inactive</span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group" role="group">
                                <a href="{{ route('top-selling.edit', $item->id) }}" class="btn btn-sm btn-warning">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('top-selling.destroy', $item->id) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('Remove from Top Selling?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">No products added yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        {{ $items->links() }}

    </div>
</div>
@endsection

@push('scripts')
<script>
// Drag-to-reorder using HTML5 (no library required)
(function () {
    var tbody = document.getElementById('sortableBody');
    if (!tbody) return;
    var dragging = null;

    tbody.querySelectorAll('tr').forEach(function (row) {
        row.draggable = true;
        row.addEventListener('dragstart', function () { dragging = row; row.style.opacity = '.4'; });
        row.addEventListener('dragend',   function () { row.style.opacity = '1'; dragging = null; saveOrder(); });
        row.addEventListener('dragover',  function (e) { e.preventDefault(); var bounding = row.getBoundingClientRect(); var offset = e.clientY - bounding.top; if (offset > bounding.height / 2) { row.parentNode.insertBefore(dragging, row.nextSibling); } else { row.parentNode.insertBefore(dragging, row); } });
    });

    function saveOrder() {
        var ids = Array.from(tbody.querySelectorAll('tr[data-id]')).map(function (r) { return r.dataset.id; });
        fetch('{{ route('top-selling.reorder') }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ ids: ids }),
        });
    }
})();
</script>
@endpush
