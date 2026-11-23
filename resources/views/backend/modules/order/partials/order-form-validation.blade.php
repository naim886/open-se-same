{{-- Shared client-side validation for admin order create/edit forms. --}}
<style>
    .client-validation-message {
        color: #ff6b7a;
        font-size: 12.5px;
        line-height: 1.35;
        margin: 6px 0 0;
    }
    #orderForm .form-control.is-invalid,
    #orderForm .form-select.is-invalid {
        border-color: #ff6b7a !important;
        box-shadow: 0 0 0 1px rgba(255, 107, 122, .35) !important;
    }
    .select2-container.select2-invalid .select2-selection {
        border-color: #ff6b7a !important;
        box-shadow: 0 0 0 1px rgba(255, 107, 122, .35) !important;
    }
    #product-rows .client-validation-message {
        margin-top: 4px;
    }
</style>

<script>
$(function () {
    var $form = $('#orderForm');

    if (!$form.length) {
        return;
    }

    function fieldKey($field) {
        return $field.attr('name') || $field.attr('id') || 'field';
    }

    function errorContainer($field) {
        var $container = $field.closest('.mb-4, .mb-3, .position-relative');

        return $container.length ? $container : $field.parent();
    }

    function clearAllErrors() {
        $form.find('.client-validation-message').remove();
        $form.find('.is-invalid').removeClass('is-invalid');
        $form.find('.select2-invalid').removeClass('select2-invalid');
    }

    function clearFieldError($field) {
        var $row = $field.closest('.product-row');

        $field.removeClass('is-invalid');
        $field.next('.select2-container').removeClass('select2-invalid');

        if ($row.length) {
            $row.find('.client-validation-message').remove();
            $row.find('.is-invalid').removeClass('is-invalid');
            $row.find('.select2-invalid').removeClass('select2-invalid');

            return;
        }

        var key = fieldKey($field);

        if (key) {
            $form.find('.client-validation-message[data-error-for="' + key + '"]').remove();
        }
    }

    function showFieldError($field, message) {
        var key = fieldKey($field);
        var $row = $field.closest('.product-row');
        var $container = $row.length ? $row : errorContainer($field);

        $field.addClass('is-invalid');
        $field.next('.select2-container').addClass('select2-invalid');

        $container
            .find('.client-validation-message[data-error-for="' + key + '"]')
            .remove();

        $('<p>', {
            'class': 'client-validation-message',
            'data-error-for': key
        }).text(message).appendTo($container);
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
        var $target = ($field && $field.length)
            ? ($field.closest('.product-row').length ? $field.closest('.product-row') : errorContainer($field))
            : $container;

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

    function validateOrderForm() {
        clearAllErrors();

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

        var $rows = $form.find('#product-rows .product-row');

        if (!$rows.length) {
            addToContainer($('#product-rows'), 'product_rows', 'Please add at least one product to the order.');
        } else {
            $rows.each(function (index) {
                var $row = $(this);
                var $product = $row.find('.product-select');
                var $quantity = $row.find('.quantity-input');
                var $unitPrice = $row.find('.unit-price-input');
                var quantity = $.trim($quantity.val());
                var unitPrice = $.trim($unitPrice.val());

                if (!$product.val()) {
                    add($product, 'Please select a product for row ' + (index + 1) + '.');
                }

                if (!/^\d+$/.test(quantity) || parseInt(quantity, 10) < 1) {
                    add($quantity, 'Quantity must be a whole number of at least 1.');
                }

                if (unitPrice === '' || isNaN(Number(unitPrice)) || Number(unitPrice) < 0) {
                    add($unitPrice, 'Unit price must be a non-negative number.');
                }

                if ($row.find('.variation-row').is(':visible') && !$row.find('.variation-select').val()) {
                    add($row.find('.variation-select'), 'Please select a variation for this product.');
                }
            });
        }

        var $customerName = $form.find('input[name="customer_name"]');
        if (!$.trim($customerName.val())) {
            add($customerName, 'Please enter the customer name.');
        }

        var $customerPhone = $form.find('input[name="customer_phone"]');
        var phoneValue = $.trim($customerPhone.val());
        var phoneDigits = phoneValue.replace(/[^0-9০-৯]/g, '');

        if (phoneValue === '') {
            add($customerPhone, 'Please enter the customer phone number.');
        } else if (phoneDigits.length < 10 || phoneDigits.length > 15) {
            add($customerPhone, 'Please enter a valid phone number.');
        }

        var $customerAddress = $form.find('textarea[name="customer_address"]');
        if (!$.trim($customerAddress.val())) {
            add($customerAddress, 'Please enter the customer address.');
        }

        var $status = $form.find('select[name="status"]');
        if (!$status.val()) {
            add($status, 'Please select an order status.');
        }

        var $deliveryLocation = $form.find('select[name="delivery_location"]');
        if (!$deliveryLocation.val()) {
            add($deliveryLocation, 'Please select a delivery location.');
        }

        ['product_price', 'delivery_charge', 'order_total'].forEach(function (name) {
            var $field = $form.find('input[name="' + name + '"]');
            var value = $.trim($field.val());

            if (value === '') {
                add($field, 'This amount is required.');
            } else if (isNaN(Number(value)) || Number(value) < 0) {
                add($field, 'Please enter a valid non-negative amount.');
            }
        });

        ['paid_amount', 'cod_amount'].forEach(function (name) {
            var $field = $form.find('input[name="' + name + '"]');
            var value = $.trim($field.val());

            if (value !== '' && (isNaN(Number(value)) || Number(value) < 0)) {
                add($field, 'Please enter a valid non-negative amount.');
            }
        });

        if (messages.length) {
            focusFirstError(firstField, firstContainer);

            return false;
        }

        return true;
    }

    $form.on('submit', function (event) {
        if (!validateOrderForm()) {
            event.preventDefault();

            return false;
        }
    });

    $form.on('input change', 'input, select, textarea', function () {
        clearFieldError($(this));
    });
});
</script>
