@extends('backend.layouts.app')

@section('content')
    <div class="row justify-content-center my-5">
        <div class="col-lg-8">
            <fieldset>
                <legend>{{$cms_content['page_title'] ?? 'Banner Details' }}</legend>
                <div class="row">
                    <div class="col-lg-8">
                        <table class="table table-bordered">
                            <tr>
                                <th width="30%">Title</th>
                                <td>{{ $banner->title }}</td>
                            </tr>
                            <tr>
                                <th>Description</th>
                                <td>{{ $banner->description ?? 'No description provided' }}</td>
                            </tr>
                            <tr>
                                <th>Button Text</th>
                                <td>{{ $banner->button_text ?? 'No button text' }}</td>
                            </tr>
                            <tr>
                                <th>Button URL</th>
                                <td>
                                    @if($banner->button_url)
                                        <a href="{{ $banner->button_url }}" target="_blank" class="text-primary">
                                            {{ $banner->button_url }} <i class="fas fa-external-link-alt"></i>
                                        </a>
                                    @else
                                        No button URL
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Sort Order</th>
                                <td>{{ $banner->sort_order }}</td>
                            </tr>
                            <tr>
                                <th>Status</th>
                                <td>
                                    @if($banner->isActive())
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-danger">Inactive</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Created At</th>
                                <td>{{ $banner->created_at->format('M d, Y h:i A') }}</td>
                            </tr>
                            <tr>
                                <th>Updated At</th>
                                <td>{{ $banner->updated_at->format('M d, Y h:i A') }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-lg-4">
                        <div class="text-center">
                            <h5>Banner Image</h5>
                            @if($banner->image)
                                <div class="banner-image-container">
                                    <img src="{{ asset('storage/' . $banner->image) }}"
                                         alt="{{ $banner->title }}"
                                         class="img-fluid rounded shadow"
                                         style="max-width: 100%; height: auto;">
                                </div>
                                <div class="mt-2">
                                    <a href="{{ asset('storage/' . $banner->image) }}"
                                       target="_blank"
                                       class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-eye"></i> View Full Size
                                    </a>
                                </div>
                            @else
                                <div class="text-center p-4 bg-light rounded">
                                    <i class="fas fa-image fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">No image uploaded</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="text-center mt-4">
                    <a href="{{ route('banner.edit', $banner) }}" class="btn btn-warning px-4">
                        <i class="fas fa-edit"></i> Edit Banner
                    </a>
                    <a href="{{ route('banner.index') }}" class="btn btn-secondary px-4 ms-2">
                        <i class="fas fa-list"></i> Back to List
                    </a>
                                </div>
            </fieldset>
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="btn-group">
                            <a href="{{ route('banner.edit', $banner) }}" class="btn btn-warning">
                                <i class="fas fa-edit"></i> Edit Banner
                            </a>
                            <form action="{{ route('banner.destroy', $banner) }}"
                                  method="POST"
                                  style="display: inline-block;"
                                  onsubmit="return confirm('Are you sure you want to delete this banner?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger">
                                    <i class="fas fa-trash"></i> Delete Banner
                                </button>
                            </form>
                        </div>
                    </div>
                                </div>
            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="btn-group">
                            <a href="{{ route('banner.edit', $banner) }}" class="btn btn-warning">
                                <i class="fas fa-edit"></i> Edit Banner
                            </a>
                            <form action="{{ route('banner.destroy', $banner) }}"
                                  method="POST"
                                  style="display: inline-block;"
                                  onsubmit="return confirm('Are you sure you want to delete this banner?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger">
                                    <i class="fas fa-trash"></i> Delete Banner
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
