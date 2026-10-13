@extends('backend.layouts.app')

@section('content')
<div class="row justify-content-center my-5">
    <div class="col-lg-10">
        {{ html()->form('POST', route('blog.store'))->acceptsFiles()->open() }}
        <fieldset>
            <legend>{{ $cms_content['page_title'] }}</legend>
            <div class="row">

                <div class="col-lg-8 mb-4 position-relative">
                    {{ html()->label('Blog Title', 'title') }}
                    <x-required />
                    {{ html()->text('title')->placeholder('Enter blog title')->class('form-control')->id('title') }}
                    @error('title') <p class="validation-message">{{ $message }}</p> @enderror
                </div>

                <div class="col-lg-4 mb-4 position-relative">
                    {{ html()->label('Status', 'status') }}
                    <x-required />
                    {{ html()->select('status', \App\Models\Blog::STATUS_LIST)->placeholder('Select Status')->class('form-select') }}
                    @error('status') <p class="validation-message">{{ $message }}</p> @enderror
                </div>

                <div class="col-lg-3 mb-4 position-relative">
                    {{ html()->label('Slug (auto-generated)', 'slug') }}
                    {{ html()->text('slug')->placeholder('auto-generated-from-title')->class('form-control')->id('slug') }}
                    @error('slug') <p class="validation-message">{{ $message }}</p> @enderror
                </div>

                <div class="col-lg-3 mb-4 position-relative">
                    {{ html()->label('Meta Title (auto-generated)', 'meta_title') }}
                    {{ html()->text('meta_title')->placeholder('auto-generated from title')->class('form-control')->id('meta_title') }}
                    @error('meta_title') <p class="validation-message">{{ $message }}</p> @enderror
                </div>

                <div class="col-lg-3 mb-4 position-relative">
                    {{ html()->label('Author', 'author') }}
                    {{ html()->text('author')->placeholder('Author name')->class('form-control') }}
                    @error('author') <p class="validation-message">{{ $message }}</p> @enderror
                </div>

                <div class="col-lg-3 mb-4 position-relative">
                    {{ html()->label('Read Time (minutes)', 'read_time') }}
                    {{ html()->number('read_time')->placeholder('Auto')->class('form-control')->attribute('min', 1) }}
                    @error('read_time') <p class="validation-message">{{ $message }}</p> @enderror
                    <small class="text-muted">Leave blank to auto-calculate.</small>
                </div>

                <div class="col-lg-12 mb-4 position-relative">
                    {{ html()->label('Meta Description', 'meta_description') }}
                    {{ html()->textarea('meta_description')->placeholder('Enter meta description (max 500 chars)')->class('form-control')->rows(2)->maxlength(500) }}
                    @error('meta_description') <p class="validation-message">{{ $message }}</p> @enderror
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

                <div class="col-lg-12 mb-4 position-relative">
                    {{ html()->label('Description', 'description') }}
                    {{ html()->textarea('description')->class('form-control')->id('description')->rows(8) }}
                    @error('description') <p class="validation-message">{{ $message }}</p> @enderror
                </div>

                <!-- FAQ Section -->
                <div class="col-lg-12 mb-4">
                    <div class="card border">
                        <div class="card-header">
                            <strong><i class="fa fa-question-circle me-1"></i> FAQs</strong>
                        </div>
                        <div class="card-body" id="faq-container">
                            <p class="text-muted mb-0" id="faq-empty-msg">No FAQs added yet. Click "Add FAQ" to add one.</p>
                        </div>
                        <div class="card-footer">
                            <button type="button" class="btn btn-sm btn-primary" id="add-faq-btn">
                                <i class="fa fa-plus me-1"></i> Add FAQ
                            </button>
                        </div>
                    </div>
                </div>

            </div>
            <div class="text-center">
                {{ html()->submit('Create Blog')->class('btn btn-primary px-4') }}
                <a href="{{ route('blog.index') }}" class="btn btn-secondary px-4 ms-2">Cancel</a>
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
    // Auto slug + meta title
    const titleInput = document.getElementById('title');
    const slugInput  = document.getElementById('slug');
    const metaInput  = document.getElementById('meta_title');
    if (titleInput) {
        titleInput.addEventListener('input', function () {
            if (!slugInput.dataset.manual) slugInput.value = titleInput.value.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '');
            if (!metaInput.dataset.manual) metaInput.value = titleInput.value;
        });
        slugInput.addEventListener('input', function () { slugInput.dataset.manual = '1'; });
        metaInput.addEventListener('input', function () { metaInput.dataset.manual = '1'; });
    }

    // Cover preview
    const coverInput = document.querySelector('input[name=cover_photo]');
    if (coverInput) {
        coverInput.addEventListener('change', function (e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = e => { document.getElementById('cover-preview-img').src = e.target.result; document.getElementById('cover-preview').style.display = 'block'; };
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
        setup: function (editor) { editor.on('change', function () { editor.save(); }); }
    });
    $('form').on('submit', function () { tinymce.triggerSave(); });

    // FAQ
    let faqCount = 0;
    const container  = document.getElementById('faq-container');
    const emptyMsg   = document.getElementById('faq-empty-msg');

    document.getElementById('add-faq-btn').addEventListener('click', function () {
        if (emptyMsg) emptyMsg.style.display = 'none';
        const idx = faqCount++;
        const div = document.createElement('div');
        div.className = 'border rounded p-3 mb-3 faq-item';
        div.innerHTML = `
            <div class="d-flex justify-content-between align-items-center mb-2">
                <strong>FAQ #${idx + 1}</strong>
                <button type="button" class="btn btn-sm btn-outline-danger remove-faq"><i class="fa fa-times"></i></button>
            </div>
            <div class="mb-2">
                <label class="form-label">Question <span class="text-danger">*</span></label>
                <input type="text" name="faq_question[]" class="form-control" placeholder="Enter question" required>
            </div>
            <div>
                <label class="form-label">Answer <span class="text-danger">*</span></label>
                <textarea name="faq_answer[]" class="form-control" rows="3" placeholder="Enter answer" required></textarea>
            </div>`;
        container.appendChild(div);
        div.querySelector('.remove-faq').addEventListener('click', function () {
            div.remove();
            if (!container.querySelector('.faq-item') && emptyMsg) emptyMsg.style.display = '';
        });
    });
});
</script>
@endpush
