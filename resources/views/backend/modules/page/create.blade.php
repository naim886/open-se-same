@extends('backend.layouts.app')

@section('content')
<div class="row justify-content-center my-5">
    <div class="col-lg-9">
        {{ html()->form('POST', route('page.store'))->acceptsFiles()->open() }}
        <fieldset>
            <legend>{{ $cms_content['page_title'] }}</legend>
            <div class="row">

                <div class="col-lg-8 mb-4 position-relative">
                    {{ html()->label('Page Title', 'title') }}
                    <x-required />
                    {{ html()->text('title')->placeholder('Enter page title')->class('form-control')->id('title') }}
                    @error('title') <p class="validation-message">{{ $message }}</p> @enderror
                </div>

                <div class="col-lg-4 mb-4 position-relative">
                    {{ html()->label('Status', 'status') }}
                    <x-required />
                    {{ html()->select('status', \App\Models\Page::STATUS_LIST)->placeholder('Select Status')->class('form-select') }}
                    @error('status') <p class="validation-message">{{ $message }}</p> @enderror
                </div>

                <div class="col-lg-6 mb-4 position-relative">
                    {{ html()->label('Slug (auto-generated)', 'slug') }}
                    {{ html()->text('slug')->placeholder('auto-generated-from-title')->class('form-control')->id('slug') }}
                    @error('slug') <p class="validation-message">{{ $message }}</p> @enderror
                    <small class="text-muted">Leave blank to auto-generate from title.</small>
                </div>

                <div class="col-lg-6 mb-4 position-relative">
                    {{ html()->label('Meta Title (auto-generated)', 'meta_title') }}
                    {{ html()->text('meta_title')->placeholder('auto-generated from title')->class('form-control')->id('meta_title') }}
                    @error('meta_title') <p class="validation-message">{{ $message }}</p> @enderror
                </div>

                <div class="col-lg-12 mb-4 position-relative">
                    {{ html()->label('Meta Description', 'meta_description') }}
                    {{ html()->textarea('meta_description')->placeholder('Enter meta description (max 500 chars)')->class('form-control')->rows(2)->maxlength(500) }}
                    @error('meta_description') <p class="validation-message">{{ $message }}</p> @enderror
                </div>

                <div class="col-lg-12 mb-4 position-relative">
                    {{ html()->label('Description', 'description') }}
                    {{ html()->textarea('description')->class('form-control')->id('description')->rows(8) }}
                    @error('description') <p class="validation-message">{{ $message }}</p> @enderror
                </div>

                <div class="col-lg-12 mb-4 position-relative">
                    {{ html()->label('Cover Photo', 'cover_photo') }}
                    {{ html()->file('cover_photo')->class('form-control')->accept('image/*') }}
                    @error('cover_photo') <p class="validation-message">{{ $message }}</p> @enderror
                    <small class="text-muted">Max 2MB. jpg, png, webp accepted.</small>
                    <div id="cover-preview" class="mt-2" style="display:none;">
                        <img id="cover-preview-img" src="" alt="Preview" class="img-thumbnail" style="max-width:200px;">
                    </div>
                </div>

            </div>
            <div class="text-center">
                {{ html()->submit('Create Page')->class('btn btn-primary px-4') }}
                <a href="{{ route('page.index') }}" class="btn btn-secondary px-4 ms-2">Cancel</a>
            </div>
        </fieldset>
        {{ html()->form()->close() }}
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.tiny.cloud/1/{{ config('services.tinymce.api_key') }}/tinymce/7/tinymce.min.js" referrerpolicy="origin"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Auto-generate slug and meta_title from title
    const titleInput    = document.getElementById('title');
    const slugInput     = document.getElementById('slug');
    const metaInput     = document.getElementById('meta_title');

    if (titleInput) {
        titleInput.addEventListener('input', function () {
            if (!slugInput.dataset.manual) {
                slugInput.value = titleInput.value.toLowerCase().replace(/[^a-z0-9\u0980-\u09FF]+/g, '-').replace(/^-+|-+$/g, '');
            }
            if (!metaInput.dataset.manual) {
                metaInput.value = titleInput.value;
            }
        });
        slugInput.addEventListener('input', function () { slugInput.dataset.manual = '1'; });
        metaInput.addEventListener('input', function () { metaInput.dataset.manual = '1'; });
    }

    // Cover photo preview
    const coverInput = document.querySelector('input[name=cover_photo]');
    if (coverInput) {
        coverInput.addEventListener('change', function () {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = e => {
                    document.getElementById('cover-preview-img').src = e.target.result;
                    document.getElementById('cover-preview').style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        });
    }

    // TinyMCE
    tinymce.init({
        selector: '#description',
        height: 450,
        skin: 'oxide-dark',
        content_css: 'dark',
        plugins: 'anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount',
        toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link image | numlist bullist | table | removeformat | code',
        images_upload_handler: function (blobInfo, progress) {
            return new Promise(function (resolve, reject) {
                var fd = new FormData();
                fd.append('file', blobInfo.blob(), blobInfo.filename());
                fetch('{{ route("tinymce.upload") }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
                    body: fd
                })
                .then(function (r) { return r.json(); })
                .then(function (d) { d.location ? resolve(d.location) : reject('Upload failed'); })
                .catch(function () { reject('Upload failed'); });
            });
        },
        setup: function (editor) {
            editor.on('change', function () { editor.save(); });
        }
    });

    $('form').on('submit', function () { tinymce.triggerSave(); });
});
</script>
@endpush
