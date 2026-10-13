@extends('backend.layouts.app')

@section('content')
<div class="row justify-content-center my-5">
    <div class="col-lg-8">
        {{ html()->form('PUT', route('site-content.update', $siteContent))->open() }}
        <fieldset>
            <legend>{{ $cms_content['page_title'] }}: {{ $siteContent->label }}</legend>
            <div class="row">

                <div class="col-lg-12 mb-3">
                    <div class="alert alert-info py-2">
                        <strong>Key:</strong> <code>{{ $siteContent->key }}</code>
                        &nbsp;&mdash;&nbsp; Use in templates: <code>{{ '{' }}{{ '{' }} \App\Models\SiteContent::get('{{ $siteContent->key }}') {{ '}' }}{{ '}' }}</code>
                    </div>
                </div>

                <div class="col-lg-6 mb-4 position-relative">
                    {{ html()->label('Label', 'label') }}
                    <x-required />
                    {{ html()->text('label')->class('form-control')->value($siteContent->label) }}
                    @error('label') <p class="validation-message">{{ $message }}</p> @enderror
                </div>

                <div class="col-lg-6 mb-4 position-relative">
                    {{ html()->label('Content Type', 'type') }}
                    <x-required />
                    {{ html()->select('type', \App\Models\SiteContent::TYPE_LIST)->class('form-select')->id('type-select')->value($siteContent->type) }}
                    @error('type') <p class="validation-message">{{ $message }}</p> @enderror
                </div>

                {{-- Text field --}}
                <div class="col-lg-12 mb-4 position-relative" id="field-text" style="display:none;">
                    {{ html()->label('Value', 'value') }}
                    {{ html()->text('value')->class('form-control')->value($siteContent->type === 'text' ? $siteContent->value : '') }}
                </div>

                {{-- Textarea field --}}
                <div class="col-lg-12 mb-4 position-relative" id="field-textarea" style="display:none;">
                    {{ html()->label('Value', 'value') }}
                    {{ html()->textarea('value')->class('form-control')->rows(6)->id('value_textarea')->value($siteContent->type === 'textarea' ? $siteContent->value : '') }}
                </div>

                {{-- HTML/TinyMCE field --}}
                <div class="col-lg-12 mb-4 position-relative" id="field-html" style="display:none;">
                    {{ html()->label('Value', 'value') }}
                    {{ html()->textarea('value')->class('form-control')->id('value_html')->rows(10)->value($siteContent->type === 'html' ? $siteContent->value : '') }}
                </div>

            </div>
            <div class="text-center">
                {{ html()->submit('Update Content')->class('btn btn-primary px-4') }}
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
    const typeSelect    = document.getElementById('type-select');
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

        document.querySelectorAll('[name^="value"]').forEach(el => el.removeAttribute('name'));
        if (type === 'text')     fieldText.querySelector('input').name     = 'value';
        if (type === 'textarea') fieldTextarea.querySelector('textarea').name = 'value';
        if (type === 'html')     fieldHtml.querySelector('textarea').name  = 'value';
    }

    typeSelect.addEventListener('change', toggleFields);
    toggleFields();

    $('form').on('submit', function () { tinymce.triggerSave(); });
});
</script>
@endpush
