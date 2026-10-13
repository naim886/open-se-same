@extends('backend.layouts.app')

@section('content')
    <div class="row justify-content-center my-5">
        <div class="col-lg-8">
            {{ html()->form('POST', route('banner.store'))->acceptsFiles()->open() }}
            <fieldset>
                <legend>{{ $cms_content['page_title'] ?? 'Create Banner' }}</legend>
                <div class="row">
                    <div class="col-lg-12 mb-4 position-relative">
                        {{ html()->label('Banner Title', 'title') }}
                        <x-required />
                        {{ html()->text('title')->placeholder('Enter banner title')->class('form-control') }}
                        @error('title')
                            <p class="validation-message">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="col-lg-12 mb-4 position-relative">
                        {{ html()->label('Description', 'description') }}
                        {{ html()->textarea('description')->placeholder('Enter banner description')->class('form-control')->rows(4) }}
                        @error('description')
                            <p class="validation-message">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="col-lg-6 mb-4 position-relative">
                        {{ html()->label('Button Text', 'button_text') }}
                        {{ html()->text('button_text')->placeholder('e.g., Shop Now')->class('form-control') }}
                        @error('button_text')
                            <p class="validation-message">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="col-lg-6 mb-4 position-relative">
                        {{ html()->label('Button URL', 'button_url') }}
                        {{ html()->text('button_url')->placeholder('https://example.com')->class('form-control') }}
                        @error('button_url')
                            <p class="validation-message">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="col-lg-12 mb-4 position-relative">
                        {{ html()->label('Banner Image (1920x600px recommended)', 'image') }}
                        <x-required />
                        {{ html()->file('image')->class('form-control')->accept('image/*') }}
                        @error('image')
                            <p class="validation-message">{{ $message }}</p>
                        @enderror
                        <small class="form-text text-muted">Max file size: 2MB. Supported formats: jpg, png, gif, webp
                        </small>
                        <div id="image-preview" class="mt-2" style="display: none;">
                            <img id="preview-img" src="" alt="Preview" class="img-thumbnail"
                                style="max-width: 200px; height: auto;">
                        </div>
                    </div>

                    <div class="col-lg-6 mb-4 position-relative">
                        {{ html()->label('Sort Order', 'sort_order') }}
                        <x-required />
                        {{ html()->number('sort_order')->placeholder('1')->class('form-control')->value(1) }}
                        @error('sort_order')
                            <p class="validation-message">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="col-lg-6 mb-4 position-relative">
                        {{ html()->label('Status', 'status') }}
                        <x-required />
                        {{ html()->select('status', \App\Models\Banner::STATUS_LIST)->placeholder('Select Status')->class('form-select') }}
                        @error('status')
                            <p class="validation-message">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                <div class="text-center">
                    {{ html()->submit('Create Banner')->class('btn btn-primary px-4') }}
                    <a href="{{ route('banner.index') }}" class="btn btn-secondary px-4 ms-2">Cancel</a>
                </div>
            </fieldset>
            {{ html()->form()->close() }}
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const imageInput = document.getElementById('image');
            const previewContainer = document.getElementById('image-preview');
            const previewImg = document.getElementById('preview-img');

            if (imageInput) {
                imageInput.addEventListener('change', function(e) {
                    const file = e.target.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            previewImg.src = e.target.result;
                            previewContainer.style.display = 'block';
                        }
                        reader.readAsDataURL(file);
                    } else {
                        previewContainer.style.display = 'none';
                    }
                });
            }
        });
    </script>
@endpush
