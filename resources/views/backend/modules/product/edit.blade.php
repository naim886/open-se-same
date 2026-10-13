@extends('backend.layouts.app')

@push('styles')
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        .select2-container--default .select2-selection--multiple {
            border: 1px solid #ced4da;
            border-radius: 0.375rem;
            min-height: 38px;
        }
        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            background-color: #0d6efd;
            border: 1px solid #0d6efd;
            color: white;
            border-radius: 3px;
        }
        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
            color: white;
            margin-right: 5px;
        }
        .select2-container {
            width: 100% !important;
        }
        /* ── Select2 dropdown dark theme ── */
        .select2-dropdown {
            background-color: #0a1c3e;
            border: 1px solid rgba(132, 163, 188, 0.4);
            color: #d4ecff;
        }
        .select2-container--default .select2-results__option {
            color: #d4ecff;
            background-color: transparent;
        }
        .select2-container--default .select2-results__option--highlighted.select2-results__option--selectable {
            background-color: #0d6efd;
            color: #fff;
        }
        .select2-container--default .select2-results__option--selected {
            background-color: rgba(13, 110, 253, 0.25);
            color: #d4ecff;
        }
        .select2-search--dropdown .select2-search__field {
            background-color: #001235;
            border: 1px solid rgba(132, 163, 188, 0.4);
            color: #d4ecff;
        }
        .select2-search--dropdown .select2-search__field::placeholder {
            color: rgba(132, 163, 188, 0.5);
        }
        .select2-container--default .select2-selection--multiple {
            background-color: transparent !important;
        }
    </style>
@endpush

@section('content')
    <div class="row justify-content-center my-5">
        <div class="col-lg-8">
            {{html()->modelForm($product, 'PATCH', route('product.update', $product->id))->acceptsFiles()->open()}}
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
                                        @php $existingBenefits = $product->key_benefits ?? ['']; if(empty($existingBenefits)) $existingBenefits = ['']; @endphp
                                        @foreach($existingBenefits as $benefit)
                                        <div class="benefit-row mb-2 d-flex gap-2 align-items-center">
                                            <input type="text" name="key_benefits[]" value="{{ $benefit }}" placeholder="E.g. 100% Handmade" class="form-control">
                                            <button type="button" class="btn btn-sm btn-danger remove-benefit">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </div>
                                        @endforeach
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
                                        @if($product->specifications->count() > 0)
                                            @foreach($product->specifications as $specIdx => $existingSpec)
                                            <div class="row specification-row mb-3" data-spec-index="{{ $specIdx }}">
                                                <div class="col-lg-5">
                                                    <label class="form-label">Specification Name</label>
                                                    <select name="specifications[{{ $specIdx }}][specification_id]" class="form-select specification-select" data-index="{{ $specIdx }}">
                                                        <option value="">Select Specification</option>
                                                        @foreach($specifications as $specId => $specName)
                                                            <option value="{{ $specId }}" {{ $existingSpec->specification_id == $specId ? 'selected' : '' }}>{{ $specName }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-lg-5">
                                                    <label class="form-label">Specification Value</label>
                                                    <input type="text" name="specifications[{{ $specIdx }}][value]" value="{{ $existingSpec->value }}" placeholder="Specification Value" class="form-control">
                                                </div>
                                                <div class="col-lg-2 d-flex align-items-end">
                                                    <button type="button" class="btn btn-sm btn-danger remove-specification">
                                                        <i class="fa fa-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                            @endforeach
                                        @else
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
                                        @endif
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
                                                @if($product->og_image)
                                                    <div class="og-image-display-area mt-4">
                                                        <img src="{{asset('storage/' . $product->og_image)}}" alt="Current OG Image" class="img-fluid" style="max-height: 200px;">
                                                    </div>
                                                @else
                                                    <div class="og-image-display-area mt-4" style="display: none">
                                                        <img src="" alt="" class="img-fluid">
                                                    </div>
                                                @endif
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
                                @if($product->primary_photo)
                                    <div class="product-primary-photo-display-area mt-4">
                                        <img src="{{asset('storage/' . $product->primary_photo)}}" alt="Current Primary Photo" class="img-fluid" style="max-height: 200px;">
                                    </div>
                                @else
                                    <div class="product-primary-photo-display-area mt-4" style="display: none">
                                        <img src="" alt="" class="img-fluid">
                                    </div>
                                @endif
                                @error('primary_photo')
                                <p class="validation-message">{{$message}}</p>
                                @enderror
                            </div>
                            <div class="col-lg-6 mb-4 position-relative">
                                {{html()->label('Product Gallery (Multiple Images)', 'gallery')}}
                                <input type="file" name="gallery[]" class="form-control" multiple accept="image/*">
                                @if($product->photos->count() > 0)
                                    <div class="product-gallery-display-area mt-4">
                                        <div class="d-flex flex-wrap gap-2" id="galleryPreviewContainer">
                                            @foreach($product->photos as $photo)
                                                <div class="position-relative">
                                                    <img src="{{asset('storage/' . $photo->photo_path)}}" alt="{{$photo->alt_text ?? 'Gallery'}}" class="img-thumbnail" style="width: 120px; height: 120px; object-fit: cover;">
                                                    <small class="text-muted d-block text-center">Order: {{$photo->sort_order}}</small>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @else
                                    <div class="product-gallery-display-area mt-4" style="display: none">
                                        <div class="d-flex flex-wrap gap-2" id="galleryPreviewContainer">
                                            <!-- Gallery previews will appear here -->
                                        </div>
                                    </div>
                                @endif
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
                                            <i class="fa fa-cogs"></i> Generate / Refresh Variants
                                        </button>
                                    </div>
                                    <div class="card-body bg-transparent">
                                        <div class="alert alert-info mb-3">
                                            <i class="fa fa-info-circle"></i>
                                            Existing variants are shown below. Use "Generate / Refresh Variants" to add new combinations from the Attributes panel above (this rebuilds the list — existing data will be re-saved on submit).
                                        </div>
                                        <div id="variantsContainer">
                                            {{-- Pre-populate existing variations --}}
                                            @foreach($product->variations as $vIdx => $existingVar)
                                            @php
                                                $vLabel = $existingVar->variationAttributes->map(function ($va) {
                                                    return ($va->attributeName?->name ?? '') . ': ' . ($va->attributeValue?->name ?? '');
                                                })->filter()->join(' / ');
                                            @endphp
                                            <div class="variant-card border border-secondary rounded p-3 mb-3" data-variant-index="{{ $vIdx }}">
                                                <div class="d-flex justify-content-between align-items-center mb-3">
                                                    <h6 class="text-primary mb-0"><i class="fa fa-tag me-2"></i>{{ $vLabel ?: 'Variant '.$loop->iteration }}</h6>
                                                    <small class="text-muted">ID: {{ $existingVar->id }}</small>
                                                </div>
                                                <input type="hidden" name="variants[{{ $vIdx }}][id]" value="{{ $existingVar->id }}">
                                                @foreach($existingVar->variationAttributes as $va)
                                                    <input type="hidden" name="variants[{{ $vIdx }}][attribute_name_ids][]" value="{{ $va->attribute_name_id }}">
                                                    <input type="hidden" name="variants[{{ $vIdx }}][attribute_value_ids][]" value="{{ $va->attribute_value_id }}">
                                                @endforeach
                                                <div class="row">
                                                    <div class="col-lg-3 mb-3">
                                                        <label class="form-label">Cost</label>
                                                        <input type="number" name="variants[{{ $vIdx }}][cost]" value="{{ $existingVar->cost }}" placeholder="0.00" step="0.01" class="form-control">
                                                    </div>
                                                    <div class="col-lg-3 mb-3">
                                                        <label class="form-label">Price <span class="text-danger">*</span></label>
                                                        <input type="number" name="variants[{{ $vIdx }}][price]" value="{{ $existingVar->price }}" placeholder="0.00" step="0.01" class="form-control" required>
                                                    </div>
                                                    <div class="col-lg-3 mb-3">
                                                        <label class="form-label">Sale Price</label>
                                                        <input type="number" name="variants[{{ $vIdx }}][sale_price]" value="{{ $existingVar->sale_price }}" placeholder="0.00" step="0.01" class="form-control">
                                                    </div>
                                                    <div class="col-lg-3 mb-3">
                                                        <label class="form-label">Stock <span class="text-danger">*</span></label>
                                                        <input type="number" name="variants[{{ $vIdx }}][stock]" value="{{ $existingVar->stock }}" placeholder="0" class="form-control" required>
                                                    </div>
                                                    <div class="col-lg-12 mb-2">
                                                        <label class="form-label">Variation Image <small class="text-muted">(upload new to replace)</small></label>
                                                        @if($existingVar->image)
                                                            <div class="mb-2">
                                                                <img src="{{ asset('storage/' . $existingVar->image) }}" alt="Current" style="max-height:70px; border-radius:6px;">
                                                            </div>
                                                        @endif
                                                        <input type="file" name="variants[{{ $vIdx }}][image]" accept="image/*" class="form-control variant-image-input" data-preview="variant-preview-{{ $vIdx }}">
                                                        <div id="variant-preview-{{ $vIdx }}" class="mt-2" style="display:none;">
                                                            <img src="" alt="Preview" style="max-height:80px; border-radius:6px; border:1px solid #dee2e6;">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            @endforeach
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
                            <button type="submit" class="btn theme-button-outline"><i class="fa fa-edit"></i> Update Product</button>
                        </div>
                    </div>
                </div>
            </fieldset>
            {{html()->closeModelForm()}}
        </div>
    </div>
@endsection

@push('scripts')
    <!-- TinyMCE -->
    <script src="https://cdn.tiny.cloud/1/{{ config('services.tinymce.api_key') }}/tinymce/7/tinymce.min.js" referrerpolicy="origin"></script>
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        @include('backend.modules.product.partials.product-form-scripts')
    </script>
    <script>
    $(document).ready(function () {
        let attributeIndex = 1;
        let variantIndex   = {{ $product->variations->count() }};

        // ── Attribute builder ─────────────────────────────────────
        $('#addAttribute').on('click', function () {
            let attributeNames = @json($attributeNames);
            let attrOptions = '';
            $.each(attributeNames, function (id, name) {
                attrOptions += `<option value="${id}">${name}</option>`;
            });
            let $group = $(`
                <div class="attribute-group mb-4 border border-secondary rounded p-3" data-attribute-index="${attributeIndex}">
                    <div class="row mb-3">
                        <div class="col-lg-5">
                            <label class="form-label">Attribute Name</label>
                            <select name="attributes[${attributeIndex}][attribute_name_id]" class="form-select attribute-name-select" data-index="${attributeIndex}">
                                <option value="">Select Attribute Name</option>
                                ${attrOptions}
                            </select>
                        </div>
                        <div class="col-lg-5">
                            <label class="form-label">Attribute Values (Multiple)</label>
                            <select name="attributes[${attributeIndex}][attribute_value_ids][]" class="form-select attribute-value-select" data-index="${attributeIndex}" multiple disabled></select>
                        </div>
                        <div class="col-lg-2 d-flex align-items-end">
                            <button type="button" class="btn btn-sm btn-danger remove-attribute-group"><i class="fa fa-trash"></i></button>
                        </div>
                    </div>
                </div>
            `);
            $('#attributesContainer').append($group);
            $group.find('.attribute-value-select').select2({ placeholder: 'Select Values', allowClear: true, closeOnSelect: false, width: '100%' });
            attributeIndex++;
        });

        $(document).on('click', '.remove-attribute-group', function () {
            if ($('.attribute-group').length > 1) {
                $(this).closest('.attribute-group').find('.attribute-value-select').select2('destroy');
                $(this).closest('.attribute-group').remove();
            }
        });

        $(document).on('change', '.attribute-name-select', function () {
            let $sel = $(this);
            let nameId = $sel.val();
            let idx = $sel.data('index');
            let $valSel = $(`select[name="attributes[${idx}][attribute_value_ids][]"]`);
            $valSel.select2('destroy').empty().prop('disabled', !nameId);
            if (nameId) {
                let attributeValues = @json($attributeValues);
                let vals = attributeValues[nameId] || {};
                $.each(vals, function (id, name) { $valSel.append(new Option(name, id)); });
                $valSel.prop('disabled', false);
            }
            $valSel.select2({ placeholder: 'Select Values', allowClear: true, closeOnSelect: false, width: '100%' });
        });

        // ── Generate / Refresh variants ─────────────────────────
        $('#generateVariants').on('click', function () {
            let attributes = [];
            let hasErrors = false;

            $('.attribute-group').each(function () {
                let $g = $(this);
                let nameId = $g.find('.attribute-name-select').val();
                let valueIds = $g.find('.attribute-value-select').val();
                if (nameId && valueIds && valueIds.length) {
                    let nameText = $g.find('.attribute-name-select option:selected').text();
                    let values = valueIds.map(vid => ({
                        id: vid,
                        text: $g.find(`.attribute-value-select option[value="${vid}"]`).text()
                    }));
                    attributes.push({ nameId, nameText, values });
                } else if (nameId || (valueIds && valueIds.length)) {
                    hasErrors = true;
                    alert('Please select both attribute name and values for all attributes.');
                    return false;
                }
            });

            if (hasErrors) return;
            if (!attributes.length) { alert('Please add at least one attribute.'); return; }

            let combinations = [[]];
            attributes.forEach(function (attr) {
                let next = [];
                combinations.forEach(function (combo) {
                    attr.values.forEach(function (val) {
                        next.push([...combo, { nameId: attr.nameId, nameText: attr.nameText, valueId: val.id, valueText: val.text }]);
                    });
                });
                combinations = next;
            });

            // Append newly generated variants (don't wipe existing ones)
            combinations.forEach(function (combo, i) {
                let idx = variantIndex++;
                let label = combo.map(a => a.valueText).join(' / ');
                let attrInputs = combo.map(a => `
                    <input type="hidden" name="variants[${idx}][attribute_name_ids][]" value="${a.nameId}">
                    <input type="hidden" name="variants[${idx}][attribute_value_ids][]" value="${a.valueId}">
                `).join('');
                $('#variantsContainer').append(`
                    <div class="variant-card border border-secondary rounded p-3 mb-3" data-variant-index="${idx}">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="text-primary mb-0"><i class="fa fa-tag me-2"></i>${label} <small class="text-muted">(new)</small></h6>
                        </div>
                        ${attrInputs}
                        <div class="row">
                            <div class="col-lg-3 mb-3">
                                <label class="form-label">Cost</label>
                                <input type="number" name="variants[${idx}][cost]" placeholder="0.00" step="0.01" class="form-control">
                            </div>
                            <div class="col-lg-3 mb-3">
                                <label class="form-label">Price <span class="text-danger">*</span></label>
                                <input type="number" name="variants[${idx}][price]" placeholder="0.00" step="0.01" class="form-control" required>
                            </div>
                            <div class="col-lg-3 mb-3">
                                <label class="form-label">Sale Price</label>
                                <input type="number" name="variants[${idx}][sale_price]" placeholder="0.00" step="0.01" class="form-control">
                            </div>
                            <div class="col-lg-3 mb-3">
                                <label class="form-label">Stock <span class="text-danger">*</span></label>
                                <input type="number" name="variants[${idx}][stock]" placeholder="0" class="form-control" required>
                            </div>
                            <div class="col-lg-12 mb-2">
                                <label class="form-label">Variation Image</label>
                                <input type="file" name="variants[${idx}][image]" accept="image/*" class="form-control variant-image-input" data-preview="variant-preview-${idx}">
                                <div id="variant-preview-${idx}" class="mt-2" style="display:none;">
                                    <img src="" alt="Preview" style="max-height:80px; border-radius:6px; border:1px solid #dee2e6;">
                                </div>
                            </div>
                        </div>
                    </div>
                `);
            });
        });

        // Live preview for variant image inputs
        $(document).on('change', '.variant-image-input', function () {
            var file = this.files[0];
            var $pre = $('#' + $(this).data('preview'));
            if (file) {
                var reader = new FileReader();
                reader.onload = function (e) { $pre.find('img').attr('src', e.target.result); $pre.show(); };
                reader.readAsDataURL(file);
            } else { $pre.hide(); }
        });
    });
    </script>
@endpush
