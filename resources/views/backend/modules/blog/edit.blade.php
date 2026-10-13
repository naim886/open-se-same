@extends('backend.layouts.app')

@section('content')
<div class="row justify-content-center my-5">
    <div class="col-lg-10">
        {{ html()->form('PUT', route('blog.update', $blog))->acceptsFiles()->open() }}
        <fieldset>
            <legend>{{ $cms_content['page_title'] }}</legend>
            <div class="row">

                <div class="col-lg-8 mb-4 position-relative">
                    {{ html()->label('Blog Title', 'title') }}
                    <x-required />
                    {{ html()->text('title')->class('form-control')->id('title')->value($blog->title) }}
                    @error('title') <p class="validation-message">{{ $message }}</p> @enderror
                </div>

                <div class="col-lg-4 mb-4 position-relative">
                    {{ html()->label('Status', 'status') }}
                    <x-required />
                    {{ html()->select('status', \App\Models\Blog::STATUS_LIST)->placeholder('Select Status')->class('form-select')->value($blog->status) }}
                    @error('status') <p class="validation-message">{{ $message }}</p> @enderror
                </div>

                <div class="col-lg-3 mb-4 position-relative">
                    {{ html()->label('Slug', 'slug') }}
                    {{ html()->text('slug')->class('form-control')->value($blog->slug) }}
                    @error('slug') <p class="validation-message">{{ $message }}</p> @enderror
                </div>

                <div class="col-lg-3 mb-4 position-relative">
                    {{ html()->label('Meta Title', 'meta_title') }}
                    {{ html()->text('meta_title')->class('form-control')->value($blog->meta_title) }}
                    @error('meta_title') <p class="validation-message">{{ $message }}</p> @enderror
                </div>

                <div class="col-lg-3 mb-4 position-relative">
                    {{ html()->label('Author', 'author') }}
                    {{ html()->text('author')->class('form-control')->value($blog->author) }}
                    @error('author') <p class="validation-message">{{ $message }}</p> @enderror
                </div>

                <div class="col-lg-3 mb-4 position-relative">
                    {{ html()->label('Read Time (min)', 'read_time') }}
                    {{ html()->number('read_time')->class('form-control')->value($blog->read_time)->attribute('min', 1) }}
                    @error('read_time') <p class="validation-message">{{ $message }}</p> @enderror
                </div>

                <div class="col-lg-2 mb-4 position-relative">
                    {{ html()->label('Views', 'view_count') }}
                    {{ html()->text('_view_count')->class('form-control')->value(number_format($blog->view_count))->attribute('readonly', true) }}
                    <small class="text-muted">Read-only</small>
                </div>

                <div class="col-lg-12 mb-4 position-relative">
                    {{ html()->label('Meta Description', 'meta_description') }}
                    {{ html()->textarea('meta_description')->class('form-control')->rows(2)->maxlength(500)->value($blog->meta_description) }}
                    @error('meta_description') <p class="validation-message">{{ $message }}</p> @enderror
                </div>

                <div class="col-lg-12 mb-4 position-relative">
                    {{ html()->label('Cover Photo', 'cover_photo') }}
                    @if($blog->cover_photo)
                        <div class="mb-2">
                            <img src="{{ asset('storage/' . $blog->cover_photo) }}" alt="Cover" class="img-thumbnail" style="max-height:120px;">
                            <small class="d-block text-muted mt-1">Upload a new image to replace.</small>
                        </div>
                    @endif
                    {{ html()->file('cover_photo')->class('form-control')->accept('image/*') }}
                    @error('cover_photo') <p class="validation-message">{{ $message }}</p> @enderror
                </div>

                <div class="col-lg-12 mb-4 position-relative">
                    {{ html()->label('Description', 'description') }}
                    {{ html()->textarea('description')->class('form-control')->id('description')->rows(8)->value($blog->description) }}
                    @error('description') <p class="validation-message">{{ $message }}</p> @enderror
                </div>

                <!-- FAQ Section -->
                <div class="col-lg-12 mb-4">
                    <div class="card border">
                        <div class="card-header">
                            <strong><i class="fa fa-question-circle me-1"></i> FAQs</strong>
                        </div>
                        <div class="card-body" id="faq-container">
                            @forelse($blog->faqs as $faq)
                                <div class="border rounded p-3 mb-3 faq-item">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <strong>FAQ #{{ $loop->iteration }}</strong>
                                        <button type="button" class="btn btn-sm btn-outline-danger remove-faq"><i class="fa fa-times"></i></button>
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label">Question <span class="text-danger">*</span></label>
                                        <input type="text" name="faq_question[]" class="form-control" value="{{ $faq->question }}" required>
                                    </div>
                                    <div>
                                        <label class="form-label">Answer <span class="text-danger">*</span></label>
                                        <textarea name="faq_answer[]" class="form-control" rows="3" required>{{ $faq->answer }}</textarea>
                                    </div>
                                </div>
                            @empty
                                <p class="text-muted mb-0" id="faq-empty-msg">No FAQs added yet.</p>
                            @endforelse
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
                {{ html()->submit('Update Blog')->class('btn btn-primary px-4') }}
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

    // FAQ remove buttons for existing faqs
    document.querySelectorAll('.remove-faq').forEach(function (btn) {
        btn.addEventListener('click', function () { btn.closest('.faq-item').remove(); });
    });

    // Add new FAQ
    const container = document.getElementById('faq-container');
    document.getElementById('add-faq-btn').addEventListener('click', function () {
        const emptyMsg = document.getElementById('faq-empty-msg');
        if (emptyMsg) emptyMsg.style.display = 'none';
        const count = container.querySelectorAll('.faq-item').length + 1;
        const div = document.createElement('div');
        div.className = 'border rounded p-3 mb-3 faq-item';
        div.innerHTML = `
            <div class="d-flex justify-content-between align-items-center mb-2">
                <strong>FAQ #${count}</strong>
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
        div.querySelector('.remove-faq').addEventListener('click', function () { div.remove(); });
    });
});
</script>
@endpush
