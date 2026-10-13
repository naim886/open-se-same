@extends('backend.layouts.app')

@section('content')
<div class="row justify-content-center my-5">
    <div class="col-lg-12">

        <div class="table-responsive">
            <table class="table theme-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Starts At</th>
                        <th>Ends At</th>
                        <th>Products</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($flashSales as $sale)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td><strong>{{ $sale->name }}</strong></td>
                        <td>{{ $sale->starts_at->format('d M Y, h:ia') }}</td>
                        <td>{{ $sale->ends_at->format('d M Y, h:ia') }}</td>
                        <td>
                            <span class="badge bg-secondary">{{ $sale->products_count }} products</span>
                        </td>
                        <td>
                            @php $label = $sale->statusLabel(); @endphp
                            @if($label === 'Live')
                                <span class="badge bg-success">🔴 Live</span>
                            @elseif($label === 'Scheduled')
                                <span class="badge bg-info text-dark">Scheduled</span>
                            @elseif($label === 'Expired')
                                <span class="badge bg-secondary">Expired</span>
                            @else
                                <span class="badge bg-danger">Inactive</span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group" role="group">
                                <a href="{{ route('flash-sale.edit', $sale->id) }}" class="btn btn-sm btn-warning">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('flash-sale.destroy', $sale->id) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('Delete this flash sale?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">No flash sales found.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        @if($flashSales->hasPages())
            <div class="d-flex justify-content-center mt-3">
                {{ $flashSales->links() }}
            </div>
        @endif

    </div>
</div>
@endsection
