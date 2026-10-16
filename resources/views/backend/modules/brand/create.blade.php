@extends('backend.layouts.app')
@section('content')
    <div class="row justify-content-center my-5">
        <div class="col-lg-8">
            {{html()->form('POST', route('brand.store'))->acceptsFiles()->open()}}
            <fieldset>
                <legend>{{$cms_content['page_title'] ?? null }}</legend>
                <div class="row">
                    <div class="col-lg-6 mb-4 position-relative">
                        {{html()->label('Brand Name', 'name')}}
                        <x-required/>
                        {{html()->text('name')->placeholder('Ex. Yellow')->class('form-control')}}
                        @error('name')
                        <p class="validation-message">{{$message}}</p>
                        @enderror
                    </div>
                    <div class="col-lg-6 mb-4 position-relative">
                        {{html()->label('Brand Slug', 'slug')}}
                        <x-required/>
                        {{html()->text('slug')->placeholder('Ex. yellow')->class('form-control')}}
                        @error('slug')
                        <p class="validation-message">{{$message}}</p>
                        @enderror
                    </div>
                    <div class="col-lg-12 mb-4 position-relative">
                        {{html()->label('Brand short description', 'short_description')}}
                        <x-required/>
                        {{html()->textarea('short_description')->placeholder('Ex. Premium clothing brand')->class('form-control')}}
                        @error('short_description')
                        <p class="validation-message">{{$message}}</p>
                        @enderror
                    </div>
                    <div class="col-lg-6 mb-4 position-relative">
                        {{html()->label('Brand status', 'status')}}
                        <x-required/>
                        {{html()->select('status', \App\Models\Brand::STATUS_LIST)->placeholder('Select status')->class('form-select')}}
                        @error('status')
                        <p class="validation-message">{{$message}}</p>
                        @enderror
                    </div>
                    <div class="col-lg-6 mb-4 position-relative">
                        {{html()->label('Brand sort order', 'sort_order')}}
                        <x-required/>
                        {{html()->number('sort_order')->placeholder('Ex. 1')->class('form-control')}}
                        @error('sort_order')
                        <p class="validation-message">{{$message}}</p>
                        @enderror
                    </div>
                    <div class="col-lg-6 mb-4 position-relative">
                        {{html()->label('Brand logo (400px X 400px)', 'logo')}}
                        {{html()->file('logo')->class('form-control')}}
                        <div class="brand-logo-display-area mt-4" style="display: none">
                            <img src="" alt="" class="img-fluid">
                        </div>
                        @error('logo')
                        <p class="validation-message">{{$message}}</p>
                        @enderror
                    </div>
                    <div class="col-lg-6 mb-4 position-relative">
                        {{html()->label('Brand banner (1920px X 480px)', 'banner')}}
                        {{html()->file('banner')->class('form-control')}}
                        <div class="brand-banner-display-area mt-4" style="display: none">
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
                            <button type="submit" class="btn theme-button-outline"><i class="fa fa-plus"></i> Add Brand</button>
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
        function bindPreview(inputName, previewSelector) {
            const input = document.querySelector('input[name="' + inputName + '"]');
            const preview = document.querySelector(previewSelector);
            if (!input || !preview) return;

            const img = preview.querySelector('img');

            input.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        img.src = e.target.result;
                        preview.style.display = 'block';
                    }
                    reader.readAsDataURL(file);
                } else {
                    preview.style.display = 'none';
                }
            });
        }

        bindPreview('logo', '.brand-logo-display-area');
        bindPreview('banner', '.brand-banner-display-area');
    });
</script>
@endpush
