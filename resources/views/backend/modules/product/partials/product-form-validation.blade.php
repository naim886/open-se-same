{{-- Shared client-side validation for product create/edit forms. --}}
<style>
    .client-validation-message {
        color: #ff6b7a;
        font-size: 12.5px;
        line-height: 1.35;
        margin: 6px 0 0;
    }
    #productForm .form-control.is-invalid,
    #productForm .form-select.is-invalid {
        border-color: #ff6b7a !important;
        box-shadow: 0 0 0 1px rgba(255, 107, 122, .35) !important;
    }
    .select2-container.select2-invalid .select2-selection {
        border-color: #ff6b7a !important;
        box-shadow: 0 0 0 1px rgba(255, 107, 122, .35) !important;
    }
</style>

<script>
$(function () {
    var $form = $('#productForm');

    if (!$form.length) {
        return;
    }

    function fieldKey($field) {
        return $field.attr('name') || $field.attr('id') || '';
    }

    function errorContainer($field) {
        var $container = $field.closest('.mb-4, .mb-3, .position-relative');

        return $container.length ? $container : $field.parent();
    }

    function clearFieldError($field) {
        if (!$field || !$field.length) {
            return;
        }

        var key = fieldKey($field);

        $field.removeClass('is-invalid');
        $field.next('.select2-container').removeClass('select2-invalid');

        if (key) {
            errorContainer($field)
                .find('.client-validation-message[data-error-for="' + key + '"]')
                .remove();
        }
    }

    function clearAllErrors() {
        $form.find('.client-validation-message').remove();
        $form.find('.is-invalid').removeClass('is-invalid');
        $form.find('.select2-invalid').removeClass('select2-invalid');
    }

    function showFieldError($field, message) {
        if (!$field || !$field.length) {
            return;
        }

        var key = fieldKey($field);
        var $container = errorContainer($field);

        $field.addClass('is-invalid');
        $field.next('.select2-container').addClass('select2-invalid');

        if (key) {
            $container
                .find('.client-validation-message[data-error-for="' + key + '"]')
                .remove();

            $('<p>', {
                'class': 'client-validation-message',
                'data-error-for': key
            }).text(message).appendTo($container);
        }
    }

    function showContainerError($container, key, message) {
        if (!$container || !$container.length) {
            return;
        }

        $container
            .find('.client-validation-message[data-error-for="' + key + '"]')
            .remove();

        $('<p>', {
            'class': 'client-validation-message',
            'data-error-for': key
        }).text(message).appendTo($container);
    }

    function focusFirstError($field, $container) {
        var $pane = ($field && $field.length)
            ? $field.closest('.tab-pane')
            : ($container ? $container.closest('.tab-pane') : $());

        if ($pane.length) {
            var paneId = $pane.attr('id');
            var tabTrigger = paneId
                ? document.querySelector('#productTabs [data-bs-target="#' + paneId + '"]')
                : null;

            if (tabTrigger) {
                try {
                    if (window.bootstrap && bootstrap.Tab) {
                        bootstrap.Tab.getOrCreateInstance(tabTrigger).show();
                    } else {
                        tabTrigger.click();
                    }
                } catch (e) {
                    tabTrigger.click();
                }
            }
        }

        var $target = ($field && $field.length) ? errorContainer($field) : $container;

        if ($target && $target.length && $target[0].scrollIntoView) {
            $target[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
        }

        if ($field && $field.length && !$field.hasClass('select2-hidden-accessible')) {
            try {
                $field.trigger('focus');
            } catch (e) {
                // Ignore focus errors on hidden fields.
            }
        }
    }

    function validateProductForm() {
        clearAllErrors();

        if (window.tinymce && typeof tinymce.triggerSave === 'function') {
            try {
                tinymce.triggerSave();
            } catch (e) {
                // TinyMCE may not be initialised on every render.
            }
        }

        var messages = [];
        var firstField = null;
        var firstContainer = null;

        function add($field, message) {
            if (!$field || !$field.length) {
                return;
            }

            showFieldError($field, message);
            messages.push(message);

            if (!firstField) {
                firstField = $field;
            }
        }

        function addToContainer($container, key, message) {
            showContainerError($container, key, message);
            messages.push(message);

            if (!firstField && !firstContainer) {
                firstContainer = $container;
            }
        }

        var productType = $('#productType').val();

        if (!productType) {
            add($('#productType'), 'Please select a product type.');
        }

        var $name = $form.find('input[name="name"]');

        if (!$.trim($name.val())) {
            add($name, 'Please enter the product name.');
        }

        var $category = $form.find('select[name="category_id"]');

        if (!$category.val()) {
            add($category, 'Please select a product category.');
        }

        var $slug = $form.find('input[name="slug"]');

        if (!$.trim($slug.val())) {
            add($slug, 'Please enter the product slug.');
        }

        var $shortDescription = $form.find('textarea[name="short_description"]');

        if (!$.trim($shortDescription.val())) {
            add($shortDescription, 'Please enter the product short description.');
        }

        var $status = $form.find('select[name="status"]');

        if ($status.val() === null || $status.val() === '') {
            add($status, 'Please select the product status.');
        }

        var $sortOrder = $form.find('input[name="sort_order"]');
        var sortOrder = $.trim($sortOrder.val());

        if (sortOrder === '') {
            add($sortOrder, 'Please enter the product sort order.');
        } else if (!/^\d+$/.test(sortOrder) || parseInt(sortOrder, 10) < 1) {
            add($sortOrder, 'Sort order must be a whole number of at least 1.');
        }

        if (productType === 'simple') {
            var $price = $('#simplePricingSection input[name="price"]');
            var $salePrice = $('#simplePricingSection input[name="sale_price"]');
            var $stock = $('#simplePricingSection input[name="stock"]');
            var price = $.trim($price.val());
            var salePrice = $.trim($salePrice.val());
            var stock = $.trim($stock.val());

            if (price === '') {
                add($price, 'Please enter the product price.');
            } else if (!isFinite(Number(price)) || Number(price) < 0) {
                add($price, 'Price must be a valid non-negative amount.');
            }

            if (stock === '') {
                add($stock, 'Please enter the stock quantity.');
            } else if (!/^\d+$/.test(stock)) {
                add($stock, 'Stock must be a whole number.');
            }

            if (salePrice !== '' && price !== ''
                && isFinite(Number(salePrice)) && Number(salePrice) >= Number(price)) {
                add($salePrice, 'Sale price must be less than the regular price.');
            }
        }

        if (productType === 'group_with_variant') {
            var $variantCards = $('#variantsContainer .variant-card');

            if (!$variantCards.length) {
                addToContainer($('#variantsContainer'), 'variants', 'Please generate at least one variant for this product.');
            } else {
                $variantCards.each(function () {
                    var $card = $(this);
                    var $variantPrice = $card.find('input[name*="[price]"]').first();
                    var $variantStock = $card.find('input[name*="[stock]"]').first();
                    var variantPrice = $.trim($variantPrice.val());
                    var variantStock = $.trim($variantStock.val());

                    if (variantPrice === '') {
                        add($variantPrice, 'Price is required for every variant.');
                    } else if (!isFinite(Number(variantPrice)) || Number(variantPrice) < 0) {
                        add($variantPrice, 'Variant price must be a non-negative amount.');
                    }

                    if (variantStock === '') {
                        add($variantStock, 'Stock is required for every variant.');
                    } else if (!/^\d+$/.test(variantStock)) {
                        add($variantStock, 'Variant stock must be a whole number.');
                    }
                });
            }
        }

        // Specifications, Key Benefits and Q&A are optional. A specification
        // row only needs both fields filled when the admin starts filling it.
        $form.find('#specificationsContainer .specification-row').each(function () {
            var $row = $(this);
            var $specification = $row.find('select[name*="[specification_id]"]').first();
            var $value = $row.find('input[name*="[value]"]').first();
            var hasSpecification = $.trim($specification.val() || '') !== '';
            var hasValue = $.trim($value.val() || '') !== '';

            if (hasSpecification && !hasValue) {
                add($value, 'Please enter a value for the selected specification.');
            } else if (!hasSpecification && hasValue) {
                add($specification, 'Please select a specification name.');
            }
        });

        if (messages.length) {
            focusFirstError(firstField, firstContainer);

            return false;
        }

        return true;
    }

    $form.on('submit', function (event) {
        if (!validateProductForm()) {
            event.preventDefault();

            return false;
        }
    });

    $form.on('input change', 'input, select, textarea', function () {
        clearFieldError($(this));
    });
});
</script>
