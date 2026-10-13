@extends('backend.layouts.app')

@section('content')
<div class="row justify-content-center my-5">
    <div class="col-lg-6">
        {{ html()->form('PUT', route('top-selling.update', $topSelling->id))->open() }}
        @csrf
        <fieldset>
            <legend>Edit Top Selling Entry</legend>

            @if($topSelling->product)
            <div class="d-flex align-items-center gap-3 mb-4 p-3 border rounded bg-light">
                @if($topSelling->product->primary_photo_thumb)
                    <img src="{{ asset('storage/'.$topSelling->product->primary_photo_thumb) }}"
                         style="width:60px;height:60px;object-fit:cover;border-radius:8px;">
                @endif
                <div>
                    <strong>{{ $topSelling->product->name }}</strong><br>
                    <small class="text-muted">৳{{ number_format($topSelling->product->sale_price ?? $topSelling->product->price, 0) }}</small>
                </div>
            </div>
            @endif

            <div class="mb-4">
                {{ html()->label('Sort Order', 'sort_order') }}
                <input type="number" name="sort_order" id="sort_order" class="form-control"
                       value="{{ old('sort_order', $topSelling->sort_order) }}" min="0">
                <small class="text-muted">Lower number = appears first. You can also drag to reorder on the list page.</small>
                @error('sort_order')<p class="validation-message">{{ $message }}</p>@enderror
            </div>

            <div class="mb-4">
                {{ html()->label('Status', 'status') }}
                {{ html()->select('status', \App\Models\TopSellingProduct::STATUS_LIST, old('status', $topSelling->status))->class('form-select') }}
                @error('status')<p class="validation-message">{{ $message }}</p>@enderror
            </div>

            <div class="text-center">
                {{ html()->submit('Update')->class('btn btn-primary px-4') }}
                <a href="{{ route('top-selling.index') }}" class="btn btn-secondary px-4 ms-2">Cancel</a>
            </div>
        </fieldset>
        {{ html()->form()->close() }}
    </div>
</div>
@endsection
