@extends('backend.layouts.app')

@section('content')
<div class="row justify-content-center my-5">
    <div class="col-lg-8">
        {{ html()->form('POST', route('site-content.store'))->open() }}
        <fieldset>
            <legend>{{ $cms_content['page_title'] }}</legend>
            <div class="row">

                <div class="col-lg-6 mb-4 position-relative">
                    {{ html()->label('Label', 'label') }}
                    <x-required />
                    {{ html()->text('label')->placeholder('e.g. Return Policy')->class('form-control')->id('label-input') }}
                    @error('label') <p class="validation-message">{{ $message }}</p> @enderror
                    <small style="color:var(--theme-text-color); opacity:0.7;">Friendly display name.</small>
                </div>

                <div class="col-lg-6 mb-4 position-relative">
                    {{ html()->label('Key', 'key') }}
                    <x-required />
                    {{ html()->text('key')->placeholder('return_policy')->class('form-control')->id('key-input') }}
                    @error('key') <p class="validation-message">{{ $message }}</p> @enderror
                    <small style="color:var(--theme-text-color); opacity:0.7;">Lowercase letters, numbers, underscores only. Used in code: <code>SiteContent::get('your_key')</code></small>
                </div>

                <div class="col-lg-12 mb-4 position-relative">
                    {{ html()->label('Content Type', 'type') }}
                    <x-required />
                    {{ html()->select('type', \App\Models\SiteContent::TYPE_LIST)->class('form-select')->id('type-select') }}
                    @error('type') <p class="validation-message">{{ $message }}</p> @enderror
                </div>

                <div class="col-lg-12 mb-4 position-relative" id="field-text" style="display:none;">
                    {{ html()->label('Value', 'value') }}
                    {{ html()->text('value')->placeholder('Enter value')->class('form-control') }}
                    @error('value') <p class="validation-message">{{ $message }}</p> @enderror
                </div>

                <div class="col-lg-12 mb-4 position-relative" id="field-textarea" style="display:none;">
                    {{ html()->label('Value', 'value') }}
                    {{ html()->textarea('value')->placeholder('Enter content')->class('form-control')->rows(6)->id('value_textarea') }}
                    @error('value') <p class="validation-message">{{ $message }}</p> @enderror
                </div>

                <div class="col-lg-12 mb-4 position-relative" id="field-html" style="display:none;">
                    {{ html()->label('Value', 'value') }}
                    {{ html()->textarea('value')->class('form-control')->id('value_html')->rows(10) }}
                    @error('value') <p class="validation-message">{{ $message }}</p> @enderror
                </div>

            </div>
            <div class="text-center">
                {{ html()->submit('Save Content')->class('btn btn-primary px-4') }}
                <a href="{{ route('site-content.index') }}" class="btn btn-secondary px-4 ms-2">Cancel</a>
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
    const typeSelect   = document.getElementById('type-select');
    const labelInput   = document.getElementById('label-input');
    const keyInput     = document.getElementById('key-input');
    const fieldText     = document.getElementById('field-text');
    const fieldTextarea = document.getElementById('field-textarea');
    const fieldHtml     = document.getElementById('field-html');

    let tinyInitialized = false;

    function toggleFields() {
        const type = typeSelect.value;
        fieldText.style.display     = type === 'text'     ? '' : 'none';
        fieldTextarea.style.display = type === 'textarea' ? '' : 'none';
        fieldHtml.style.display     = type === 'html'     ? '' : 'none';

        if (type === 'html' && !tinyInitialized) {
            tinyInitialized = true;
            tinymce.init({
                selector: '#value_html',
                height: 400,
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
        }

        // Rename all value fields to 'value' so only visible one submits
        document.querySelectorAll('[name^="value"]').forEach(el => el.removeAttribute('name'));
        if (type === 'text')     fieldText.querySelector('input').name     = 'value';
        if (type === 'textarea') fieldTextarea.querySelector('textarea').name = 'value';
        if (type === 'html')     fieldHtml.querySelector('textarea').name  = 'value';
    }

    typeSelect.addEventListener('change', toggleFields);
    toggleFields();

    // Auto-generate key from label
    labelInput.addEventListener('input', function () {
        if (!keyInput.dataset.manual) {
            keyInput.value = labelInput.value.toLowerCase().replace(/[^a-z0-9]+/g, '_').replace(/^_+|_+$/g, '');
        }
    });
    keyInput.addEventListener('input', function () { keyInput.dataset.manual = '1'; });

    $('form').on('submit', function () { tinymce.triggerSave(); });
});
</script>
@endpush
