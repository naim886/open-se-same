@extends('backend.layouts.app')

@section('content')
    <div class="row justify-content-center my-5">
        <div class="col-lg-9">

            @if(!empty($importStatus))
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title mb-2">Latest import status</h5>

                        @if(($importStatus['state'] ?? '') === 'queued')
                            <p class="mb-0">
                                <span class="badge bg-info">Queued</span>
                                Queued {{ $importStatus['queued_at'] ?? '' }}. A background queue worker will start it shortly.
                            </p>
                        @elseif(($importStatus['state'] ?? '') === 'running')
                            <p class="mb-0">
                                <span class="badge bg-warning text-dark">Running</span>
                                Started {{ $importStatus['started_at'] ?? '' }}. You can leave this page — the background worker will continue.
                            </p>
                        @elseif(($importStatus['state'] ?? '') === 'finished')
                            <p class="mb-3">
                                <span class="badge bg-success">Finished</span>
                                {{ $importStatus['finished_at'] ?? '' }}
                            </p>
                            @php $stats = $importStatus['stats'] ?? []; @endphp
                            <div class="table-responsive">
                                <table class="table table-sm theme-table mb-0">
                                    <tbody>
                                    <tr><td>CSV rows</td><td>{{ $stats['rows'] ?? 0 }}</td></tr>
                                    <tr><td>Products created</td><td>{{ $stats['products_created'] ?? 0 }}</td></tr>
                                    <tr><td>Products updated</td><td>{{ $stats['products_updated'] ?? 0 }}</td></tr>
                                    <tr><td>Products skipped</td><td>{{ $stats['products_skipped'] ?? 0 }}</td></tr>
                                    <tr><td>Variations created</td><td>{{ $stats['variations_created'] ?? 0 }}</td></tr>
                                    <tr><td>Variations updated</td><td>{{ $stats['variations_updated'] ?? 0 }}</td></tr>
                                    <tr><td>Legacy variation products migrated</td><td>{{ $stats['source_products_migrated'] ?? 0 }}</td></tr>
                                    <tr><td>Categories created</td><td>{{ $stats['categories_created'] ?? 0 }}</td></tr>
                                    <tr><td>Brands created</td><td>{{ $stats['brands_created'] ?? 0 }}</td></tr>
                                    <tr><td>Images downloaded</td><td>{{ $stats['images_downloaded'] ?? 0 }}</td></tr>
                                    <tr><td>Images failed</td><td>{{ $stats['images_failed'] ?? 0 }}</td></tr>
                                    </tbody>
                                </table>
                            </div>
                            @if(!empty($stats['errors']))
                                <details class="mt-3">
                                    <summary>Row errors ({{ count($stats['errors']) }})</summary>
                                    <ul class="mb-0 mt-2">
                                        @foreach(array_slice($stats['errors'], 0, 50) as $error)
                                            <li><small>{{ $error }}</small></li>
                                        @endforeach
                                    </ul>
                                </details>
                            @endif
                        @elseif(($importStatus['state'] ?? '') === 'failed')
                            <p class="mb-0">
                                <span class="badge bg-danger">Failed</span>
                                {{ $importStatus['error'] ?? 'Unknown error' }}
                            </p>
                        @endif
                    </div>
                </div>
            @endif

            {{ html()->form('POST', route('product.import.store'))->acceptsFiles()->open() }}
            <fieldset>
                <legend>{{ $cms_content['page_title'] ?? 'Product CSV Import' }}</legend>

                <p class="text-muted">
                    Upload a WooCommerce-style product CSV. Categories and brands are created automatically.
                    The first category path root becomes the brand. <strong>variable</strong> rows become
                    variable products, and each <strong>variation</strong> row is attached to its parent using
                    the variation name after the parent name (for example <em>Black</em>).
                    The first image becomes the primary photo; remaining images become gallery photos.
                    A stock value of <strong>0</strong> is imported as <strong>10</strong>.
                </p>

                <div class="mb-4">
                    {{ html()->label('CSV File', 'csv') }}
                    <x-required />
                    {{ html()->file('csv')->class('form-control')->accept('.csv,text/csv') }}
                    @error('csv') <p class="validation-message">{{ $message }}</p> @enderror
                    <small class="text-muted">
                        Expected columns: Type, SKU, Name, Published, Short description, Description, In stock?,
                        Stock, Sale price, Regular price, Categories, Images.
                    </small>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-lg-6">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="download_images"
                                   name="download_images" value="1" checked>
                            <label class="form-check-label" for="download_images">Download product images</label>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="import_variations"
                                   name="import_variations" value="1" checked>
                            <label class="form-check-label" for="import_variations">Create variations for variable products</label>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="update_existing"
                                   name="update_existing" value="1" checked>
                            <label class="form-check-label" for="update_existing">Update products with matching SKU</label>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="replace_images"
                                   name="replace_images" value="1">
                            <label class="form-check-label" for="replace_images">Replace existing images</label>
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    {{ html()->label('Row limit (optional)', 'limit') }}
                    <input type="number" name="limit" id="limit" class="form-control" min="0"
                           placeholder="0 = import everything">
                    <small class="text-muted">Use a small number for a test import.</small>
                </div>

                <div class="text-center">
                    {{ html()->submit('Upload & Import CSV')->class('btn btn-primary px-4') }}
                    <a href="{{ route('product.index') }}" class="btn btn-secondary px-4 ms-2">Back to Products</a>
                </div>
            </fieldset>
            {{ html()->form()->close() }}
        </div>
    </div>
@endsection
