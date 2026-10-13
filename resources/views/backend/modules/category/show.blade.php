@extends('backend.layouts.app')
@section('content')
    <div class="row justify-content-center my-5">
        <div class="col-lg-8">
            <fieldset>
                <legend>{{$cms_content['page_title'] ?? 'Category Details' }}</legend>
                <div class="row">
                    <div class="col-lg-8">
                        <table class="table table-bordered">
                            <tr>
                                <th width="30%">Name</th>
                                <td>{{ $category->name }}</td>
                            </tr>
                            <tr>
                                <th>Slug</th>
                                <td>{{ $category->slug }}</td>
                            </tr>
                            <tr>
                                <th>Short Description</th>
                                <td>{{ $category->short_description ?? 'No description provided' }}</td>
                            </tr>
                            <tr>
                                <th>Sort Order</th>
                                <td>{{ $category->sort_order }}</td>
                            </tr>
                            <tr>
                                <th>Status</th>
                                <td>
                                    @if($category->isActive())
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-danger">Inactive</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Products Count</th>
                                <td>{{ $category->products()->count() }} products</td>
                            </tr>
                            <tr>
                                <th>Created At</th>
                                <td>{{ $category->created_at->format('M d, Y h:i A') }}</td>
                            </tr>
                            <tr>
                                <th>Updated At</th>
                                <td>{{ $category->updated_at->format('M d, Y h:i A') }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-lg-4">
                        <div class="text-center">
                            <h5>Category Images</h5>

                            @if($category->getRawOriginal('icon'))
                                <div class="mb-4">
                                    <h6>Icon (128x128)</h6>
                                    <img src="{{ $category->icon_url }}"
                                         alt="{{ $category->name }}"
                                         class="img-fluid rounded shadow"
                                         style="max-width: 128px; height: auto;"
                                         onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                                    <div style="display: none; padding: 20px; background: #f8f9fa; border: 1px dashed #dee2e6; text-align: center; border-radius: 5px;">
                                        <i class="fa fa-exclamation-triangle fa-2x text-warning mb-2"></i>
                                        <p class="text-muted mb-0">Icon image not found</p>
                                    </div>
                                </div>
                            @endif

                            @if($category->getRawOriginal('banner'))
                                <div class="mb-4">
                                    <h6>Banner (1920x480)</h6>
                                    <img src="{{ $category->banner_url }}"
                                         alt="{{ $category->name }}"
                                         class="img-fluid rounded shadow"
                                         style="max-width: 100%; height: auto;"
                                         onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                                    <div style="display: none; padding: 20px; background: #f8f9fa; border: 1px dashed #dee2e6; text-align: center; border-radius: 5px;">
                                        <i class="fa fa-exclamation-triangle fa-2x text-warning mb-2"></i>
                                        <p class="text-muted mb-0">Banner image not found</p>
                                    </div>
                                </div>
                            @endif

                            @if(!$category->getRawOriginal('icon') && !$category->getRawOriginal('banner'))
                                <div class="text-center p-4 bg-light rounded">
                                    <i class="fas fa-image fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">No images uploaded</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="text-center mt-4">
                    <a href="{{ route('category.edit', $category) }}" class="btn btn-warning px-4">
                        <i class="fas fa-edit"></i> Edit Category
                    </a>
                    <a href="{{ route('category.index') }}" class="btn btn-secondary px-4 ms-2">
                        <i class="fas fa-list"></i> Back to List
                    </a>
                </div>
            </fieldset>
        </div>
    </div>
@endsection
