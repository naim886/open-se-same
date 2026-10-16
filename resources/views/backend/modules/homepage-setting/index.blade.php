@extends('backend.layouts.app')
@section('content')
    <div class="row justify-content-center my-5">
        <div class="col-lg-12">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{html()->form('PUT', route('homepage-settings.update'))->open()}}
            <fieldset>
                <legend>{{ $cms_content['page_title'] ?? 'Homepage Settings' }}</legend>

                <p class="text-muted">
                    The homepage renders these blocks from top to bottom. Add a <strong>Category Products</strong>
                    block to feature a category's products with its banner above the list. If no block is active, the
                    homepage falls back to the banner slider and Shop by Category tiles.
                </p>

                <div class="mb-4 d-flex flex-wrap gap-2">
                    @foreach(\App\Models\HomepageSection::TYPE_LIST as $type => $label)
                        <button type="button" class="btn btn-sm btn-outline-primary add-block" data-type="{{ $type }}">
                            <i class="fa fa-plus"></i> {{ $label }}
                        </button>
                    @endforeach
                </div>

                <div id="blocksContainer">
                    @foreach($sections as $index => $section)
                        @include('backend.modules.homepage-setting._row', ['index' => $index, 'section' => $section])
                    @endforeach
                </div>

                <p id="emptyBlocks" class="text-muted my-3" style="{{ $sections->isEmpty() ? '' : 'display:none;' }}">
                    No blocks configured yet — add one with the buttons above.
                </p>

                <div class="row mt-4 justify-content-center">
                    <div class="col-lg-4">
                        <div class="d-grid">
                            <button type="submit" class="btn theme-button-outline">
                                <i class="fa fa-save"></i> Save Homepage
                            </button>
                        </div>
                    </div>
                </div>
            </fieldset>
            {{html()->form()->close()}}
        </div>
    </div>

    <template id="blockRowTemplate">
        @include('backend.modules.homepage-setting._row', ['index' => '__INDEX__', 'section' => null])
    </template>
@endsection

@push('scripts')
<script>
    $(function () {
        var container = document.getElementById('blocksContainer');
        var template = document.getElementById('blockRowTemplate');
        var emptyState = document.getElementById('emptyBlocks');
        var nextIndex = {{ $sections->count() }};

        var CATEGORY_TYPES = @json(\App\Models\HomepageSection::CATEGORY_TYPES);

        function refreshEmptyState() {
            if (!emptyState) return;
            emptyState.style.display = container.querySelectorAll('.homepage-block').length ? 'none' : '';
        }

        function toggleCategoryField(block) {
            var wrap = block.querySelector('.block-category-wrap');
            if (!wrap) return;
            wrap.style.display = CATEGORY_TYPES.indexOf(block.querySelector('.block-type').value) !== -1 ? '' : 'none';
        }

        $('.add-block').on('click', function () {
            var html = template.innerHTML.replace(/__INDEX__/g, nextIndex++);
            container.insertAdjacentHTML('beforeend', html);
            var block = container.lastElementChild;

            block.querySelector('.block-type').value = $(this).data('type');
            toggleCategoryField(block);
            refreshEmptyState();
        });

        // Changing a block's type shows or hides its category picker.
        $(document).on('change', '.block-type', function () {
            toggleCategoryField(this.closest('.homepage-block'));
        });

        $(document).on('click', '.remove-block', function () {
            $(this).closest('.homepage-block').remove();
            refreshEmptyState();
        });

        $(document).on('click', '.block-up', function () {
            var block = this.closest('.homepage-block');
            if (block.previousElementSibling) {
                block.parentNode.insertBefore(block, block.previousElementSibling);
            }
        });

        $(document).on('click', '.block-down', function () {
            var block = this.closest('.homepage-block');
            if (block.nextElementSibling) {
                block.parentNode.insertBefore(block.nextElementSibling, block);
            }
        });

        refreshEmptyState();
    });
</script>
@endpush
