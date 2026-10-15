@extends('backend.layouts.app')
@section('content')
    <div class="row justify-content-center my-5">
        <div class="col-lg-8">
            {{html()->form('POST', route('category.store'))->acceptsFiles()->open()}}
            <fieldset>
                <legend>{{$cms_content['page_title'] ?? null }}</legend>
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
                        {{html()->select('parent_id', $parentCategories ?? [])->placeholder('None (Top Level)')->class('form-select')}}
                        <small class="text-muted">Leave empty to create a top level category.</small>
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
                        {{html()->select('status', ['1' => 'Active', '0' => 'Inactive'])->placeholder('Select status')->class('form-select')}}
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
                        <div class="category-icon-display-area mt-4" style="display: none">
                            <img src="" alt="" class="img-fluid">
                        </div>
                        @error('icon')
                        <p class="validation-message">{{$message}}</p>
                        @enderror
                    </div>
                    <div class="col-lg-6 mb-4 position-relative">
                        {{html()->label('Category banner (1920px X 480px)', 'banner')}}
                        {{html()->file('banner')->class('form-control')}}
                        <div class="category-banner-display-area mt-4" style="display: none">
                            <img src="" alt="" class="img-fluid">
                        </div>
                        @error('banner')
                        <p class="validation-message">{{$message}}</p>
                        @enderror
                    </div>
                </div>
                <div class="row mt-4 justify-content-center">
                    <div class="col-lg-4">
                        <div class="d-grid">
                            <button type="submit" class="btn theme-button-outline"><i class="fa fa-plus"></i> Add Category</button>
                        </div>
                    </div>
                </div>
            </fieldset>
            {{html()->form()->close()}}
        </div>
    </div>
@endsection

@push('scripts')
<script>

    $('#name').on('input', function (){
        let str = $(this).val();
        str = String(str).normalize('NFD').replace(/[\u0300-\u036f]/g, '');
        str = str.replace(/[^\p{L}\p{N}]+/gu, '-');
        str = str.replace(/-+/g, '-').replace(/^-|-$/g, '');
        $('#slug').val(str)

    })

    document.addEventListener('DOMContentLoaded', function() {
        // Icon preview
        const iconInput = document.querySelector('input[name="icon"]');
        const iconPreview = document.querySelector('.category-icon-display-area');
        const iconImg = iconPreview.querySelector('img');

        if (iconInput) {
            iconInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        iconImg.src = e.target.result;
                        iconPreview.style.display = 'block';
                    }
                    reader.readAsDataURL(file);
                } else {
                    iconPreview.style.display = 'none';
                }
            });
        }

        // Banner preview
        const bannerInput = document.querySelector('input[name="banner"]');
        const bannerPreview = document.querySelector('.category-banner-display-area');
        const bannerImg = bannerPreview.querySelector('img');

        if (bannerInput) {
            bannerInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        bannerImg.src = e.target.result;
                        bannerPreview.style.display = 'block';
                    }
                    reader.readAsDataURL(file);
                } else {
                    bannerPreview.style.display = 'none';
                }
            });
        }
    });
</script>
@endpush
