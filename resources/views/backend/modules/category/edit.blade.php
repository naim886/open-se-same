@extends('backend.layouts.app')
@section('content')
    <div class="row justify-content-center my-5">
        <div class="col-lg-8">
            {{html()->modelForm($category, 'PUT', route('category.update', $category))->acceptsFiles()->open()}}
            <fieldset>
                <legend>{{$cms_content['page_title'] ?? 'Edit Category' }}</legend>
                <div class="row">
                    <div class="col-lg-6 mb-4 position-relative">
                        {{html()->label('Category Name', 'name')}}
                        <x-required/>
                        {{html()->text('name')->placeholder('Ex. Saree')->class('form-control')}}
                        @error('name')
                        <p class="validation-message">{{$message}}</p>
                        @enderror
                    </div>
                    <div class="col-lg-6 mb-4 position-relative">
                        {{html()->label('Category Slug', 'slug')}}
                        <x-required/>
                        {{html()->text('slug')->placeholder('Ex. saree')->class('form-control')}}
                        @error('slug')
                        <p class="validation-message">{{$message}}</p>
                        @enderror
                    </div>
                    <div class="col-lg-6 mb-4 position-relative">
                        {{html()->label('Parent Category', 'parent_id')}}
                        {{html()->select('parent_id', $parentCategories ?? [], $category->parent_id)->placeholder('None (Top Level)')->class('form-select')}}
                        <small class="text-muted">Leave empty to make it a top level category.</small>
                        @error('parent_id')
                        <p class="validation-message">{{$message}}</p>
                        @enderror
                    </div>
                    <div class="col-lg-12 mb-4 position-relative">
                        {{html()->label('Category short description', 'short_description')}}
                        <x-required/>
                        {{html()->textarea('short_description')->placeholder('Ex. Beautiful saree')->class('form-control')}}
                        @error('short_description')
                        <p class="validation-message">{{$message}}</p>
                        @enderror
                    </div>
                    <div class="col-lg-6 mb-4 position-relative">
                        {{html()->label('Category status', 'status')}}
                        <x-required/>
                        {{html()->select('status', \App\Models\Category::STATUS_LIST)->placeholder('Select status')->class('form-select')}}
                        @error('status')
                        <p class="validation-message">{{$message}}</p>
                        @enderror
                    </div>
                    <div class="col-lg-6 mb-4 position-relative">
                        {{html()->label('Category sort order', 'sort_order')}}
                        <x-required/>
                        {{html()->number('sort_order')->placeholder('Ex. 1')->class('form-control')}}
                        @error('sort_order')
                        <p class="validation-message">{{$message}}</p>
                        @enderror
                    </div>
                    <div class="col-lg-6 mb-4 position-relative">
                        {{html()->label('Category icon (128px X 128px)', 'icon')}}
                        {{html()->file('icon')->class('form-control')}}
                        @error('icon')
                        <p class="validation-message">{{$message}}</p>
                        @enderror
                        @if($category->getRawOriginal('icon'))
                            <div class="mt-2">
                                <label class="form-label">Current Icon:</label>
                                <div>
                                    <img src="{{ $category->icon_url }}"
                                         alt="{{ $category->name }}"
                                         class="img-thumbnail"
                                         style="max-width: 128px; height: auto;"
                                         onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                                    <div style="display: none; padding: 10px; background: var(--bg-surface-alt); border: 1px dashed var(--border-strong); text-align: center;">
                                        <i class="fa fa-exclamation-triangle text-warning"></i> Image not found
                                    </div>
                                </div>
                            </div>
                        @endif
                        <div class="category-icon-display-area" style="display:none;">
                            <img src="" alt="Icon Preview" class="img-thumbnail" style="max-width: 128px; height: auto;">
                        </div>
                    </div>
                    <div class="col-lg-6 mb-4 position-relative">
                        {{html()->label('Category banner (1920px X 480px)', 'banner')}}
                        {{html()->file('banner')->class('form-control')}}
                        @error('banner')
                        <p class="validation-message">{{$message}}</p>
                        @enderror
                        @if($category->getRawOriginal('banner'))
                            <div class="mt-2">
                                <label class="form-label">Current Banner:</label>
                                <div>
                                    <img src="{{ $category->banner_url }}"
                                         alt="{{ $category->name }}"
                                         class="img-thumbnail"
                                         style="max-width: 300px; height: auto;"
                                         onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                                    <div style="display: none; padding: 10px; background: var(--bg-surface-alt); border: 1px dashed var(--border-strong); text-align: center;">
                                        <i class="fa fa-exclamation-triangle text-warning"></i> Image not found
                                    </div>
                                </div>
                            </div>
                        @endif
                        <div class="category-banner-display-area" style="display:none;">
                            <img src="" alt="Banner Preview" class="img-thumbnail" style="max-width: 300px; height: auto;">
                        </div>
                    </div>
                </div>
                <div class="text-center">
                    {{html()->submit('Update Category')->class('btn btn-primary px-4')}}
                    <a href="{{ route('category.index') }}" class="btn btn-secondary px-4 ms-2">Cancel</a>
                </div>
            </fieldset>
            {{html()->closeModelForm()}}
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Icon preview
        const iconInput = document.querySelector('input[name="icon"]');
        const iconPreview = document.querySelector('.category-icon-display-area');
        const iconImg = iconPreview ? iconPreview.querySelector('img') : null;

        if (iconInput && iconPreview && iconImg) {
            iconInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        iconImg.src = e.target.result;
                        iconPreview.style.display = 'block';
                    }
                    reader.readAsDataURL(file);
                }
            });
        }

        // Banner preview
        const bannerInput = document.querySelector('input[name="banner"]');
        const bannerPreview = document.querySelector('.category-banner-display-area');
        const bannerImg = bannerPreview ? bannerPreview.querySelector('img') : null;

        if (bannerInput && bannerPreview && bannerImg) {
            bannerInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        bannerImg.src = e.target.result;
                        bannerPreview.style.display = 'block';
                    }
                    reader.readAsDataURL(file);
                }
            });
        }
    });
</script>
@endpush
