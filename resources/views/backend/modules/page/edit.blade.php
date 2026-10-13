@extends('backend.layouts.app')

@section('content')
<div class="row justify-content-center my-5">
    <div class="col-lg-9">
        {{ html()->form('PUT', route('page.update', $page))->acceptsFiles()->open() }}
        <fieldset>
            <legend>{{ $cms_content['page_title'] }}</legend>
            <div class="row">

                <div class="col-lg-8 mb-4 position-relative">
                    {{ html()->label('Page Title', 'title') }}
                    <x-required />
                    {{ html()->text('title')->placeholder('Enter page title')->class('form-control')->id('title')->value($page->title) }}
                    @error('title') <p class="validation-message">{{ $message }}</p> @enderror
                </div>

                <div class="col-lg-4 mb-4 position-relative">
                    {{ html()->label('Status', 'status') }}
                    <x-required />
                    {{ html()->select('status', \App\Models\Page::STATUS_LIST)->placeholder('Select Status')->class('form-select')->value($page->status) }}
                    @error('status') <p class="validation-message">{{ $message }}</p> @enderror
                </div>

                <div class="col-lg-6 mb-4 position-relative">
                    {{ html()->label('Slug', 'slug') }}
                    {{ html()->text('slug')->class('form-control')->id('slug')->value($page->slug) }}
                    @error('slug') <p class="validation-message">{{ $message }}</p> @enderror
                </div>

                <div class="col-lg-6 mb-4 position-relative">
                    {{ html()->label('Meta Title', 'meta_title') }}
                    {{ html()->text('meta_title')->class('form-control')->value($page->meta_title) }}
                    @error('meta_title') <p class="validation-message">{{ $message }}</p> @enderror
                </div>

                <div class="col-lg-12 mb-4 position-relative">
                    {{ html()->label('Meta Description', 'meta_description') }}
                    {{ html()->textarea('meta_description')->class('form-control')->rows(2)->maxlength(500)->value($page->meta_description) }}
                    @error('meta_description') <p class="validation-message">{{ $message }}</p> @enderror
                </div>

                <div class="col-lg-12 mb-4 position-relative">
                    {{ html()->label('Description', 'description') }}
                    {{ html()->textarea('description')->class('form-control')->id('description')->rows(8)->value($page->description) }}
                    @error('description') <p class="validation-message">{{ $message }}</p> @enderror
                </div>

                <div class="col-lg-12 mb-4 position-relative">
                    {{ html()->label('Cover Photo', 'cover_photo') }}
                    @if($page->cover_photo)
                        <div class="mb-2">
                            <img src="{{ asset('storage/' . $page->cover_photo) }}" alt="Cover" class="img-thumbnail" style="max-height:120px;">
                            <small class="d-block text-muted mt-1">Upload a new image to replace.</small>
                        </div>
                    @endif
                    {{ html()->file('cover_photo')->class('form-control')->accept('image/*') }}
                    @error('cover_photo') <p class="validation-message">{{ $message }}</p> @enderror
                </div>

            </div>
            <div class="text-center">
                {{ html()->submit('Update Page')->class('btn btn-primary px-4') }}
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
