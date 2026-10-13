$(document).ready(function() {
    let attributeIndex = 1;
    let specificationIndex = {{ isset($product) ? $product->specifications->count() : 1 }};
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
        if (!$('input[name="meta_title"]').val()) {
            $('input[name="meta_title"]').val(title);
        }
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
        if (!$('textarea[name="meta_description"]').val()) {
            $('textarea[name="meta_description"]').val(shortDesc);
        }
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
            let specifications = @json($specifications ?? []);

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
        let specifications = @json($specifications ?? []);
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

    // Setup gallery preview functionality
    $('input[name="gallery"]').on('change', function(e) {
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
