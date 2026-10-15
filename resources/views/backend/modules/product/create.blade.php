@extends('backend.layouts.app')

@push('styles')
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        /* Select2 theming lives in backend/css/styles.css; only the
           page-scoped layout tweak remains here. */
        .select2-container {
            width: 100% !important;
        }
    </style>
@endpush

@section('content')
    <div class="row justify-content-center my-5">
        <div class="col-lg-8">

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif


            {{html()->form('POST', route('product.store'))->acceptsFiles()->open()}}
            <fieldset>
                <legend>{{$cms_content['page_title'] ?? null }}</legend>

                <!-- Product Type Selection -->
                <div class="row mb-4">
                    <div class="col-lg-6 position-relative">
                        {{html()->label('Product Type', 'product_type')}}
                        <x-required/>
                        {{html()->select('product_type', [
                            'simple' => 'Simple Product',
                            'group_with_variant' => 'Group with Variant'
                        ])->placeholder('Select Product Type')->class('form-control')->id('productType')}}
                        @error('product_type')
                        <p class="validation-message">{{$message}}</p>
                        @enderror
                    </div>
                </div>

                <!-- Tab Navigation -->
                <ul class="nav nav-tabs mb-4" id="productTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="basic-info-tab" data-bs-toggle="tab" data-bs-target="#basic-info" type="button" role="tab">
                            <i class="fa fa-info-circle"></i> Basic Info
                        </button>
                    </li>
                    <li class="nav-item" role="presentation" id="variation-tab-li" style="display: none;">
                        <button class="nav-link" id="variation-tab" data-bs-toggle="tab" data-bs-target="#variation" type="button" role="tab">
                            <i class="fa fa-cogs"></i> Variations
                        </button>
                    </li>
                </ul>

                <!-- Tab Content -->
                <div class="tab-content" id="productTabsContent">
                    <!-- Basic Info Tab -->
                    <div class="tab-pane fade show active" id="basic-info" role="tabpanel">
                        <div class="row">
                            <div class="col-lg-6 mb-4 position-relative">
                                {{html()->label('Product Name', 'name')}}
                                <x-required/>
                                {{html()->text('name')->placeholder('Ex. Saree')->class('form-control')}}
                                @error('name')
                                <p class="validation-message">{{$message}}</p>
                                @enderror
                            </div>
                            <div class="col-lg-6 mb-4 position-relative">
                                {{html()->label('Product Category', 'category_id')}}
                                <x-required/>
                                {{html()->select('category_id', $categories)->placeholder('Select Category')->class('form-select')}}
                                @error('category_id')
                                <p class="validation-message">{{$message}}</p>
                                @enderror
                            </div>
                            <div class="col-lg-6 mb-4 position-relative">
                                {{html()->label('Product Slug', 'slug')}}
                                <x-required/>
                                {{html()->text('slug')->placeholder('Ex. saree')->class('form-control')}}
                                @error('slug')
                                <p class="validation-message">{{$message}}</p>
                                @enderror
                            </div>
                            <div class="col-lg-12 mb-4 position-relative">
                                {{html()->label('Product short description', 'short_description')}}
                                <x-required/>
                                {{html()->textarea('short_description')->placeholder('Ex. Beautiful saree')->class('form-control')}}
                                @error('short_description')
                                <p class="validation-message">{{$message}}</p>
                                @enderror
                            </div>
                            <div class="col-lg-12 mb-4 position-relative">
                                {{html()->label('Product Description', 'description')}}
                                {{html()->textarea('description')->placeholder('Detailed product description')->class('form-control')->rows(6)->id('description')}}
                                @error('description')
                                <p class="validation-message">{{$message}}</p>
                                @enderror
                            </div>

                            <!-- Key Benefits Section -->
                            <div class="col-lg-12 mb-4">
                                <div class="card bg-transparent border-secondary">
                                    <div class="card-header bg-transparent border-secondary">
                                        <h6 class="mb-0">Key Benefits</h6>
                                    </div>
                                    <div class="card-body bg-transparent" id="benefitsContainer">
                                        <div class="benefit-row mb-2 d-flex gap-2 align-items-center">
                                            <input type="text" name="key_benefits[]" placeholder="E.g. 100% Handmade" class="form-control">
                                            <button type="button" class="btn btn-sm btn-danger remove-benefit">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="card-footer bg-transparent border-secondary">
                                        <button type="button" class="btn btn-sm btn-primary" id="addBenefit">
                                            <i class="fa fa-plus"></i> Add Benefit
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Product Specifications Section -->
                            <div class="col-lg-12 mb-4">
                                <div class="card bg-transparent border-secondary">
                                    <div class="card-header bg-transparent border-secondary">
                                        <h6 class="mb-0">Product Specifications</h6>
                                    </div>
                                    <div class="card-body bg-transparent" id="specificationsContainer">
                                        <div class="row specification-row mb-3" data-spec-index="0">
                                            <div class="col-lg-5">
                                                <label class="form-label">Specification Name</label>
                                                {{html()->select('specifications[0][specification_id]', $specifications)->placeholder('Select Specification')->class('form-select specification-select')->attribute('data-index', '0')}}
                                            </div>
                                            <div class="col-lg-5">
                                                <label class="form-label">Specification Value</label>
                                                {{html()->text('specifications[0][value]')->placeholder('Specification Value')->class('form-control')}}
                                            </div>
                                            <div class="col-lg-2 d-flex align-items-end">
                                                <button type="button" class="btn btn-sm btn-danger remove-specification">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-footer bg-transparent border-secondary">
                                        <button type="button" class="btn btn-sm btn-primary" id="addSpecification">
                                            <i class="fa fa-plus"></i> Add Specification
                                        </button>
                                    </div>
                                </div>
                            </div>

                            @include('backend.modules.product.partials._qa_section')

                            <!-- Pricing Section (shown for simple products) -->
                            <div id="simplePricingSection">
                                <div class="row">
                                    <div class="col-lg-4 mb-4 position-relative">
                                        {{html()->label('Cost', 'cost')}}
                                        {{html()->number('cost')->placeholder('0.00')->attribute('step', '0.01')->class('form-control')}}
                                        @error('cost')
                                        <p class="validation-message">{{$message}}</p>
                                        @enderror
                                    </div>
                                    <div class="col-lg-4 mb-4 position-relative">
                                        {{html()->label('Price', 'price')}}
                                        <x-required/>
                                        {{html()->number('price')->placeholder('0.00')->attribute('step', '0.01')->class('form-control')}}
                                        @error('price')
                                        <p class="validation-message">{{$message}}</p>
                                        @enderror
                                    </div>
                                    <div class="col-lg-4 mb-4 position-relative">
                                        {{html()->label('Sale Price', 'sale_price')}}
                                        {{html()->number('sale_price')->placeholder('0.00')->attribute('step', '0.01')->class('form-control')}}
                                        @error('sale_price')
                                        <p class="validation-message">{{$message}}</p>
                                        @enderror
                                    </div>
                                    <div class="col-lg-6 mb-4 position-relative">
                                        {{html()->label('Stock Quantity', 'stock')}}
                                        <x-required/>
                                        {{html()->number('stock')->placeholder('0')->class('form-control')}}
                                        @error('stock')
                                        <p class="validation-message">{{$message}}</p>
                                        @enderror
                                    </div>
                                    <div class="col-lg-6 mb-4 position-relative">
                                        {{html()->label('Stock Text', 'stock_text')}}
                                        {{html()->text('stock_text')->placeholder('Ex. In Stock')->class('form-control')}}
                                        <small class="text-muted">Optional text to display for stock status</small>
                                        @error('stock_text')
                                        <p class="validation-message">{{$message}}</p>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- SEO Section -->
                            <div class="col-lg-12 mb-4">
                                <div class="card bg-transparent border-secondary">
                                    <div class="card-header bg-transparent border-secondary">
                                        <h6 class="mb-0">SEO Settings</h6>
                                    </div>
                                    <div class="card-body bg-transparent">
                                        <div class="row">
                                            <div class="col-lg-12 mb-3">
                                                {{html()->label('Meta Title', 'meta_title')}}
                                                {{html()->text('meta_title')->placeholder('SEO friendly title')->class('form-control')}}
                                                @error('meta_title')
                                                <p class="validation-message">{{$message}}</p>
                                                @enderror
                                            </div>
                                            <div class="col-lg-12 mb-3">
                                                {{html()->label('Meta Description', 'meta_description')}}
                                                {{html()->textarea('meta_description')->placeholder('SEO friendly description')->class('form-control')->rows(3)}}
                                                @error('meta_description')
                                                <p class="validation-message">{{$message}}</p>
                                                @enderror
                                            </div>
                                            <div class="col-lg-12 mb-3">
                                                {{html()->label('OG Image (1200px X 630px)', 'og_image')}}
                                                {{html()->file('og_image')->class('form-control')}}
                                                <div class="og-image-display-area mt-4" style="display: none">
                                                    <img src="" alt="" class="img-fluid">
                                                </div>
                                                @error('og_image')
                                                <p class="validation-message">{{$message}}</p>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-6 mb-4 position-relative">
                                {{html()->label('Product status', 'status')}}
                                <x-required/>
                                {{html()->select('status', ['1' => 'Active', '0' => 'Inactive'])->placeholder('Select status')->class('form-select')}}
                                @error('status')
                                <p class="validation-message">{{$message}}</p>
                                @enderror
                            </div>
                            <div class="col-lg-6 mb-4 position-relative">
                                {{html()->label('Product sort order', 'sort_order')}}
                                <x-required/>
                                {{html()->number('sort_order')->placeholder('Ex. 1')->class('form-control')}}
                                @error('sort_order')
                                <p class="validation-message">{{$message}}</p>
                                @enderror
                            </div>
                            <div class="col-lg-6 mb-4 position-relative">
                                {{html()->label('Product Primary Photo', 'primary_photo')}}
                                {{html()->file('primary_photo')->class('form-control')}}
                                <div class="product-primary-photo-display-area mt-4" style="display: none">
                                    <img src="" alt="" class="img-fluid">
                                </div>
                                @error('primary_photo')
                                <p class="validation-message">{{$message}}</p>
                                @enderror
                            </div>
                            <div class="col-lg-6 mb-4 position-relative">
                                {{html()->label('Product Gallery (Multiple Images)', 'gallery')}}
                                <input type="file" name="gallery[]" class="form-control" multiple accept="image/*">
                                <div class="product-gallery-display-area mt-4" style="display: none">
                                    <div class="d-flex flex-wrap gap-2" id="galleryPreviewContainer">
                                        <!-- Gallery previews will appear here -->
                                    </div>
                                </div>
                                @error('gallery')
                                <p class="validation-message">{{$message}}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Variations Tab -->
                    <div class="tab-pane fade" id="variation" role="tabpanel">
                        <div class="row">
                            <!-- Product Attributes Section -->
                            <div class="col-lg-12 mb-4">
                                <div class="card bg-transparent border-secondary">
                                    <div class="card-header bg-transparent border-secondary d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0">Product Attributes</h6>
                                        <button type="button" class="btn btn-sm btn-primary" id="addAttribute">
                                            <i class="fa fa-plus"></i> Add Attribute
                                        </button>
                                    </div>
                                    <div class="card-body bg-transparent" id="attributesContainer">
                                        <div class="attribute-group mb-4 border border-secondary rounded p-3" data-attribute-index="0">
                                            <div class="row mb-3">
                                                <div class="col-lg-5">
                                                    {{html()->label('Attribute Name', 'attributes[0][attribute_name_id]')}}
                                                    {{html()->select('attributes[0][attribute_name_id]', $attributeNames)->placeholder('Select Attribute Name')->class('form-select attribute-name-select')->attribute('data-index', '0')}}
                                                </div>
                                                <div class="col-lg-5">
                                                    {{html()->label('Attribute Values (Multiple)', 'attributes[0][attribute_value_ids][]')}}
                                                    <select name="attributes[0][attribute_value_ids][]" class="form-select attribute-value-select" data-index="0" multiple disabled>
                                                        <option value="">Select Attribute Values</option>
                                                    </select>
                                                </div>
                                                <div class="col-lg-2 d-flex align-items-end">
                                                    <button type="button" class="btn btn-sm btn-danger remove-attribute-group">
                                                        <i class="fa fa-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Product Variants Section -->
                            <div class="col-lg-12 mb-4">
                                <div class="card bg-transparent border-secondary">
                                    <div class="card-header bg-transparent border-secondary d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0">Product Variants</h6>
                                        <button type="button" class="btn btn-sm btn-success" id="generateVariants">
                                            <i class="fa fa-cogs"></i> Generate Variants
                                        </button>
                                    </div>
                                    <div class="card-body bg-transparent">
                                        <div class="alert alert-info">
                                            <i class="fa fa-info-circle"></i> First add your product attributes above, then click "Generate Variants" to create all possible combinations.
                                        </div>
                                        <div id="variantsContainer">
                                            <!-- Variants will be generated here -->
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-4 justify-content-center">
                    <div class="col-lg-4">
                        <div class="d-grid">
                            <button type="submit" class="btn theme-button-outline"><i class="fa fa-plus"></i> Add Product</button>
                        </div>
                    </div>
                </div>
            </fieldset>
            {{html()->form()->close()}}
        </div>
    </div>
@endsection

@push('scripts')
    <!-- TinyMCE -->
    <script src="https://cdn.tiny.cloud/1/{{ config('services.tinymce.api_key') }}/tinymce/7/tinymce.min.js" referrerpolicy="origin"></script>
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            let attributeIndex = 1;
            let specificationIndex = 1;
            let variantIndex = 0;

            // Initialize Select2 for existing attribute value selects
            function initializeSelect2() {
                $('.attribute-value-select').select2({
                    placeholder: 'Select Attribute Values',
                    allowClear: true,
                    closeOnSelect: false,
                    width: '100%'
                });
            }

            // Initialize Select2 on page load
            initializeSelect2();

            // Product type change handler
            $('#productType').on('change', function() {
                let productType = $(this).val();

                if (productType === 'group_with_variant') {
                    $('#variation-tab-li').show();
                    $('#simplePricingSection').hide();
                    // Make price fields not required for variant products
                    $('#simplePricingSection input[required]').removeAttr('required');
                } else {
                    $('#variation-tab-li').hide();
                    $('#simplePricingSection').show();
                    // Make price fields required for simple products
                    $('#simplePricingSection input[name="price"], #simplePricingSection input[name="stock"]').attr('required', 'required');
                    // Clear variations
                    $('#variantsContainer').empty();
                    // Switch to basic info tab
                    $('#basic-info-tab').tab('show');
                }
            });

            // Trigger change event on page load
            $('#productType').trigger('change');

            // Auto-fill functionality
            // Generate slug from title
            $('input[name="name"]').on('input', function() {
                let title = $(this).val();
                $('input[name="meta_title"]').val(title);
                let slug = title.toLowerCase()
                    .replace(/[^a-z0-9\s-]/g, '')
                    .replace(/\s+/g, '-')
                    .replace(/-+/g, '-')
                    .trim();
                $('input[name="slug"]').val(slug);
            });

            // Auto-fill meta description from short description
            $('textarea[name="short_description"]').on('input', function() {
                let shortDesc = $(this).val();
                $('textarea[name="meta_description"]').val(shortDesc);
            });

            // Add new attribute group with Select2 initialization
            $('#addAttribute').on('click', function() {
                let attributeOptions = '';
                let attributeNames = @json($attributeNames);

                $.each(attributeNames, function(id, name) {
                    attributeOptions += `<option value="${id}">${name}</option>`;
                });

                let newGroup = $(`
                    <div class="attribute-group mb-4 border border-secondary rounded p-3" data-attribute-index="${attributeIndex}">
                        <div class="row mb-3">
                            <div class="col-lg-5">
                                <label class="form-label">Attribute Name</label>
                                <select name="attributes[${attributeIndex}][attribute_name_id]" class="form-select attribute-name-select" data-index="${attributeIndex}">
                                    <option value="">Select Attribute Name</option>
                                    ${attributeOptions}
                                </select>
                            </div>
                            <div class="col-lg-5">
                                <label class="form-label">Attribute Values (Multiple)</label>
                                <select name="attributes[${attributeIndex}][attribute_value_ids][]" class="form-select attribute-value-select" data-index="${attributeIndex}" multiple disabled>
                                </select>
                            </div>
                            <div class="col-lg-2 d-flex align-items-end">
                                <button type="button" class="btn btn-sm btn-danger remove-attribute-group">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                `);
                $('#attributesContainer').append(newGroup);

                // Initialize Select2 for the new attribute value select
                newGroup.find('.attribute-value-select').select2({
                    placeholder: 'Select Attribute Values',
                    allowClear: true,
                    closeOnSelect: false,
                    width: '100%'
                });

                attributeIndex++;
            });

            // Remove entire attribute group and destroy Select2
            $(document).on('click', '.remove-attribute-group', function() {
                if ($('.attribute-group').length > 1) {
                    let $group = $(this).closest('.attribute-group');
                    // Destroy Select2 before removing
                    $group.find('.attribute-value-select').select2('destroy');
                    $group.remove();
                }
            });

            // Attribute name change handler - Updated for Select2
            $(document).on('change', '.attribute-name-select', function() {
                let $select = $(this);
                let attributeNameId = $select.val();
                let index = $select.data('index');
                let $valueSelect = $(`select[name="attributes[${index}][attribute_value_ids][]"]`);

                // Destroy existing Select2 and clear options
                $valueSelect.select2('destroy');
                $valueSelect.empty().prop('disabled', true);

                if (attributeNameId) {
                    // Get attribute values for this attribute name
                    let attributeValues = @json($attributeValues);
                    let values = attributeValues[attributeNameId] || {};

                    $valueSelect.prop('disabled', false);

                    $.each(values, function(id, name) {
                        $valueSelect.append(new Option(name, id));
                    });
                } else {
                    $valueSelect.prop('disabled', true);
                }

                // Reinitialize Select2
                $valueSelect.select2({
                    placeholder: 'Select Attribute Values',
                    allowClear: true,
                    closeOnSelect: false,
                    width: '100%'
                });
            });

            // Function to update specification dropdowns
            function updateSpecificationDropdowns() {
                let selectedSpecs = [];

                // Collect all selected specification IDs
                $('.specification-select').each(function() {
                    let selectedValue = $(this).val();
                    if (selectedValue) {
                        selectedSpecs.push(selectedValue);
                    }
                });

                // Update each dropdown to hide selected options
                $('.specification-select').each(function() {
                    let $currentSelect = $(this);
                    let currentValue = $currentSelect.val();
                    let specifications = @json($specifications);

                    // Clear and rebuild options
                    $currentSelect.empty();
                    $currentSelect.append('<option value="">Select Specification</option>');

                    $.each(specifications, function(id, name) {
                        // Show option if it's not selected elsewhere OR if it's the current selection
                        if (!selectedSpecs.includes(id) || id === currentValue) {
                            $currentSelect.append(new Option(name, id));
                        }
                    });

                    // Restore current value
                    if (currentValue) {
                        $currentSelect.val(currentValue);
                    }
                });
            }

            // Specification change handler
            $(document).on('change', '.specification-select', function() {
                updateSpecificationDropdowns();
            });

            // Add new specification field with proper dropdown functionality
            $('#addSpecification').on('click', function() {
                let specifications = @json($specifications);
                let specOptions = '<option value="">Select Specification</option>';

                $.each(specifications, function(id, name) {
                    specOptions += `<option value="${id}">${name}</option>`;
                });

                let newRow = $(`
                    <div class="row specification-row mb-3" data-spec-index="${specificationIndex}">
                        <div class="col-lg-5">
                            <label class="form-label">Specification Name</label>
                            <select name="specifications[${specificationIndex}][specification_id]" class="form-select specification-select" data-index="${specificationIndex}">
                                ${specOptions}
                            </select>
                        </div>
                        <div class="col-lg-5">
                            <label class="form-label">Specification Value</label>
                            <input type="text" name="specifications[${specificationIndex}][value]" placeholder="Specification Value" class="form-control">
                        </div>
                        <div class="col-lg-2 d-flex align-items-end">
                            <button type="button" class="btn btn-sm btn-danger remove-specification">
                                <i class="fa fa-trash"></i>
                            </button>
                        </div>
                    </div>
                `);
                $('#specificationsContainer').append(newRow);
                specificationIndex++;

                // Update all dropdowns to reflect the new state
                updateSpecificationDropdowns();
            });

            // Remove specification row and update dropdowns
            $(document).on('click', '.remove-specification', function() {
                if ($('.specification-row').length > 1) {
                    $(this).closest('.specification-row').remove();
                    // Update dropdowns after removal
                    updateSpecificationDropdowns();
                }
            });

            // Initialize specification dropdowns on page load
            updateSpecificationDropdowns();

            // Image preview functionality
            function setupImagePreview(inputSelector, displaySelector) {
                $(inputSelector).on('change', function(e) {
                    let file = e.target.files[0];
                    let $displayArea = $(displaySelector);
                    let $img = $displayArea.find('img');

                    if (file) {
                        let reader = new FileReader();
                        reader.onload = function(e) {
                            $img.attr('src', e.target.result);
                            $displayArea.show();
                        };
                        reader.readAsDataURL(file);
                    } else {
                        $displayArea.hide();
                    }
                });
            }

            // Setup image previews
            setupImagePreview('input[name="primary_photo"]', '.product-primary-photo-display-area');
            setupImagePreview('input[name="og_image"]', '.og-image-display-area');

            // Live preview for dynamically generated variant image inputs
            $(document).on('change', '.variant-image-input', function () {
                var file  = this.files[0];
                var $pre  = $('#' + $(this).data('preview'));
                if (file) {
                    var reader = new FileReader();
                    reader.onload = function (e) {
                        $pre.find('img').attr('src', e.target.result);
                        $pre.show();
                    };
                    reader.readAsDataURL(file);
                } else {
                    $pre.hide();
                }
            });

            // Setup gallery preview functionality
            $('input[name="gallery[]"]').on('change', function(e) {
                let files = e.target.files;
                let $displayArea = $('.product-gallery-display-area');
                let $container = $('#galleryPreviewContainer');

                $container.empty();

                if (files.length > 0) {
                    Array.from(files).forEach(function(file) {
                        if (file) {
                            let reader = new FileReader();
                            reader.onload = function(e) {
                                let $imgContainer = $('<div class="position-relative">');
                                let $img = $('<img>').attr({
                                    'src': e.target.result,
                                    'alt': 'Gallery Preview',
                                    'class': 'img-thumbnail'
                                }).css({
                                    'width': '120px',
                                    'height': '120px',
                                    'object-fit': 'cover'
                                });
                                $imgContainer.append($img);
                                $container.append($imgContainer);
                            };
                            reader.readAsDataURL(file);
                        }
                    });
                    $displayArea.show();
                } else {
                    $displayArea.hide();
                }
            });

            // Generate variants functionality
            $('#generateVariants').on('click', function() {
                let attributes = [];
                let hasErrors = false;

                // Collect all attribute data
                $('.attribute-group').each(function() {
                    let $group = $(this);
                    let attributeNameId = $group.find('.attribute-name-select').val();
                    let attributeValueIds = $group.find('.attribute-value-select').val();

                    if (attributeNameId && attributeValueIds && attributeValueIds.length > 0) {
                        // Get attribute name text
                        let attributeNameText = $group.find('.attribute-name-select option:selected').text();

                        // Get attribute value texts
                        let attributeValues = [];
                        attributeValueIds.forEach(function(valueId) {
                            let valueText = $group.find('.attribute-value-select option[value="' + valueId + '"]').text();
                            attributeValues.push({
                                id: valueId,
                                text: valueText
                            });
                        });

                        attributes.push({
                            nameId: attributeNameId,
                            nameText: attributeNameText,
                            values: attributeValues
                        });
                    } else if (attributeNameId || (attributeValueIds && attributeValueIds.length > 0)) {
                        hasErrors = true;
                        alert('Please select both attribute name and values for all attributes');
                        return false;
                    }
                });

                if (hasErrors) {
                    return;
                }

                if (attributes.length === 0) {
                    alert('Please add at least one attribute to generate variants');
                    return;
                }

                // Generate all possible combinations
                let combinations = generateCombinations(attributes);

                // Clear existing variants
                $('#variantsContainer').empty();

                // Create variant cards for each combination
                combinations.forEach(function(combination, index) {
                    let variantHtml = createVariantCard(combination, index);
                    $('#variantsContainer').append(variantHtml);
                });

                variantIndex = combinations.length;
            });

            // Function to generate all possible combinations
            function generateCombinations(attributes) {
                if (attributes.length === 0) return [[]];

                let result = [[]];

                attributes.forEach(function(attribute) {
                    let newResult = [];
                    result.forEach(function(combination) {
                        attribute.values.forEach(function(value) {
                            let newCombination = [...combination];
                            newCombination.push({
                                nameId: attribute.nameId,
                                nameText: attribute.nameText,
                                valueId: value.id,
                                valueText: value.text
                            });
                            newResult.push(newCombination);
                        });
                    });
                    result = newResult;
                });

                return result;
            }

            // Function to create variant card HTML
            function createVariantCard(combination, index) {
                let variantName = combination.map(attr => attr.valueText).join(' / ');

                let attrInputs = combination.map((attr, attrIndex) => `
                    <input type="hidden" name="variants[${index}][attribute_name_ids][]" value="${attr.nameId}">
                    <input type="hidden" name="variants[${index}][attribute_value_ids][]" value="${attr.valueId}">
                `).join('');

                return `
                    <div class="variant-card border border-secondary rounded p-3 mb-3" data-variant-index="${index}">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="text-primary mb-0"><i class="fa fa-tag me-2"></i>${variantName}</h6>
                        </div>
                        ${attrInputs}
                        <div class="row">
                            <div class="col-lg-3 mb-3">
                                <label class="form-label">Cost</label>
                                <input type="number" name="variants[${index}][cost]" placeholder="0.00" step="0.01" class="form-control">
                            </div>
                            <div class="col-lg-3 mb-3">
                                <label class="form-label">Price <span class="text-danger">*</span></label>
                                <input type="number" name="variants[${index}][price]" placeholder="0.00" step="0.01" class="form-control" required>
                            </div>
                            <div class="col-lg-3 mb-3">
                                <label class="form-label">Sale Price</label>
                                <input type="number" name="variants[${index}][sale_price]" placeholder="0.00" step="0.01" class="form-control">
                            </div>
                            <div class="col-lg-3 mb-3">
                                <label class="form-label">Stock <span class="text-danger">*</span></label>
                                <input type="number" name="variants[${index}][stock]" placeholder="0" class="form-control" required>
                            </div>
                            <div class="col-lg-12 mb-2">
                                <label class="form-label">Variation Image <small class="text-muted">(shown when this option is selected)</small></label>
                                <input type="file" name="variants[${index}][image]" accept="image/*" class="form-control variant-image-input" data-preview="variant-preview-${index}">
                                <div id="variant-preview-${index}" class="mt-2" style="display:none;">
                                    <img src="" alt="Preview" style="max-height:80px; border-radius:6px; border:1px solid #dee2e6;">
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            }

            // Form validation before submit
            $('form').on('submit', function(e) {
                let productType = $('#productType').val();

                if (productType === 'group_with_variant') {
                    let hasVariants = $('#variantsContainer .variant-card').length > 0;

                    if (!hasVariants) {
                        e.preventDefault();
                        alert('Please generate variants for this product type');
                        $('#variation-tab').tab('show');
                        return false;
                    }

                    // Validate variant fields
                    let hasValidationErrors = false;
                    $('#variantsContainer .variant-card').each(function() {
                        let $card = $(this);
                        let price = $card.find('input[name*="[price]"]').val();
                        let stock = $card.find('input[name*="[stock]"]').val();

                        if (!price || !stock) {
                            hasValidationErrors = true;
                            $card.find('input[name*="[price]"], input[name*="[stock]"]').addClass('is-invalid');
                        } else {
                            $card.find('input[name*="[price]"], input[name*="[stock]"]').removeClass('is-invalid');
                        }
                    });

                    if (hasValidationErrors) {
                        e.preventDefault();
                        alert('Please fill in all required variant fields (Price and Stock)');
                        $('#variation-tab').tab('show');
                        return false;
                    }
                }
            });

            // Key Benefits - Add/Remove rows
            $('#addBenefit').on('click', function() {
                $('#benefitsContainer').append(`
                    <div class="benefit-row mb-2 d-flex gap-2 align-items-center">
                        <input type="text" name="key_benefits[]" placeholder="E.g. 100% Handmade" class="form-control">
                        <button type="button" class="btn btn-sm btn-danger remove-benefit"><i class="fa fa-trash"></i></button>
                    </div>
                `);
            });
            $(document).on('click', '.remove-benefit', function() {
                if ($('.benefit-row').length > 1) {
                    $(this).closest('.benefit-row').remove();
                }
            });

            // Initialize TinyMCE for description
            tinymce.init({
                selector: '#description',
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
                setup: function(editor) {
                    editor.on('change', function() {
                        editor.save();
                    });
                }
            });

            // Save TinyMCE content before form submit
            $('form').on('submit', function() {
                tinymce.triggerSave();
            });
        });
    </script>
@endpush
