@extends('backend.layouts.app')
@section('title', 'Create Order')
@section('content')
    {{html()->form('POST', route('orders.store'))->open()}}
        @include('backend.modules.order.partials._form')
    {{html()->form()->close()}}
@endsection
@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        /* Colour theming comes from backend/css/styles.css; only
           the sizing tweak for the taller order fields remains. */
        .select2-container--default .select2-selection--single { height: 44px; }
        .select2-container--default .select2-selection--single .select2-selection__rendered { line-height: 42px; }
        .select2-container--default .select2-selection--single .select2-selection__arrow { height: 42px; }
    </style>
@endpush
@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        @php
            $products_for_js = [];
            foreach ($products ?? collect([]) as $p) {
                $variations_for_p = [];
                foreach ($p->activeVariations as $v) {
                    $variations_for_p[] = [
                        'id'    => $v->id,
                        'label' => $v->label,
                        'price' => $v->sale_price ? (float)$v->sale_price : (float)$v->price,
                    ];
                }
                $products_for_js[] = [
                    'id'            => $p->id,
                    'name'          => $p->name,
                    'price'         => (isset($p->sale_price) && $p->sale_price > 0) ? (float)$p->sale_price : (float)$p->price,
                    'primary_photo' => $p->primary_photo ? \Illuminate\Support\Facades\Storage::url($p->primary_photo) : null,
                    'is_variant'    => $p->isVariantProduct(),
                    'variations'    => $variations_for_p,
                ];
            }
        @endphp

        // Products data (id, name, price, primary_photo, is_variant, variations)
        const products = @json($products_for_js);

        // Small HTML-escape helper
        function escapeHtml(str) {
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
        }

        // Build options HTML from products array (safe-escaped, with data-photo for Select2)
        var productOptions = (products || []).map(function(p){
            return '<option value="' + p.id + '" data-photo="' + (p.primary_photo || '') + '">' + escapeHtml(p.name) + '</option>';
        }).join('');

        // Initialize Select2 with product image thumbnails
        function initProductSelect($select) {
            $select.select2({
                width: '100%',
                placeholder: 'Select Product',
                templateResult: function(option) {
                    if (!option.id) return option.text;
                    var photo = $(option.element).attr('data-photo');
                    var $wrap = $('<span style="display:flex;align-items:center;gap:8px;"></span>');
                    if (photo) {
                        $wrap.append('<img src="' + photo + '" style="width:48px;height:48px;object-fit:cover;border-radius:3px;flex-shrink:0;" />');
                    }
                    $wrap.append($('<span></span>').text(option.text));
                    return $wrap;
                },
                templateSelection: function(option) {
                    if (!option.id) return option.text;
                    var photo = $(option.element).attr('data-photo');
                    if (!photo) return option.text;
                    return $('<span style="display:flex;align-items:center;gap:6px;"><img src="' + photo + '" style="width:26px;height:26px;object-fit:cover;border-radius:2px;" /><span>' + escapeHtml(option.text) + '</span></span>');
                }
            });
        }

        // Delivery costs mapping for calculation
        const DELIVERY_COST = {
            @foreach(\App\Models\Order::deliveryCostList() as $k => $v)
            "{{ $k }}": {{ $v }},
            @endforeach
        };

        let rowIndex = 0;
        let deliveryChargeManual = false;
        let orderTotalManual = false;
        let codManual = false;

        function makeRowHtml(index) {
            return `
                <div class="product-row mb-2 border p-2" data-index="${index}">
                    <div class="row align-items-center">
                        <div class="col-lg-6">
                            <select name="product_ids[]" class="form-select product-select" required>
                                <option value="">Select Product</option>
                                ${productOptions}
                            </select>
                        </div>
                        <div class="col-lg-2">
                            <input type="number" name="quantities[]" class="form-control quantity-input" value="1" min="1" required />
                        </div>
                        <div class="col-lg-2">
                            <input type="number" name="unit_prices[]" class="form-control unit-price-input" value="0" step="0.01" readonly />
                        </div>
                        <div class="col-lg-2 text-end">
                            <div class="row-subtotal">0</div>
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-lg-10">
                            <div class="row-image"></div>
                        </div>
                        <div class="col-lg-2 text-end">
                            <button type="button" class="btn btn-sm btn-outline-danger remove-row-btn">Remove</button>
                        </div>
                    </div>
                    <div class="row mt-2 variation-row" style="display:none">
                        <div class="col-lg-8">
                            <select class="form-select variation-select">
                                <option value="">-- Select Variation --</option>
                            </select>
                        </div>
                    </div>
                    <input type="hidden" name="variation_ids[]" class="variation-id-input" value="">
                    <input type="hidden" name="variation_labels[]" class="variation-label-input" value="">
                </div>
            `;
        }

        function addProductRow() {
            const html = makeRowHtml(rowIndex++);
            const $row = $(html);
            $('#product-rows').append($row);
            initProductSelect($row.find('.product-select'));
        }

        function recalcRowSubtotal($row) {
            const qty = parseFloat($row.find('.quantity-input').val()) || 0;
            const unit = parseFloat($row.find('.unit-price-input').val()) || 0;
            const subtotal = (qty * unit);
            $row.find('.row-subtotal').text(subtotal.toFixed(2));
            recalcTotals();
        }

        function recalcTotals(manual = false) {
            let productsTotal = 0;

            if (manual) {
                productsTotal = parseFloat($('#product_total_price').val()) || 0;
            } else {
                $('#product-rows .product-row').each(function () {
                    const subtotal = parseFloat($(this).find('.row-subtotal').text()) || 0;
                    productsTotal += subtotal;
                });
                $('#product_total_price').val(productsTotal.toFixed(2));
            }

            if (!deliveryChargeManual) {
                let deliveryLocation = $('#delivery_location').val();
                let deliveryCharge = 0;
                if (deliveryLocation && DELIVERY_COST.hasOwnProperty(deliveryLocation)) {
                    deliveryCharge = parseFloat(DELIVERY_COST[deliveryLocation]) || 0;
                } else {
                    const firstKey = Object.keys(DELIVERY_COST)[0];
                    deliveryCharge = parseFloat(DELIVERY_COST[firstKey]) || 0;
                }
                $('#delivery_charge').val(deliveryCharge.toFixed(2));
            }

            const deliveryCharge = parseFloat($('#delivery_charge').val()) || 0;
            const paidAmount = parseFloat($('#paid_amount').val()) || 0;

            if (!orderTotalManual) {
                const orderTotal = productsTotal + deliveryCharge;
                $('#order_total').val(orderTotal.toFixed(2));
            }

            const orderTotal = parseFloat($('#order_total').val()) || 0;

            if (!codManual) {
                const cod = orderTotal - paidAmount;
                $('#cod_amount').val(cod.toFixed(2));
            }
        }

        function populateVariationSelect($row, variations, selectedId) {
            const vSelect = $row.find('.variation-select');
            let opts = '<option value="">-- Select Variation --</option>';
            variations.forEach(function(v) {
                opts += '<option value="' + v.id + '" data-price="' + v.price + '">' + escapeHtml(v.label) + '</option>';
            });
            vSelect.html(opts);
            $row.find('.variation-row').show();
            if (selectedId) {
                vSelect.val(selectedId);
                const selOpt = vSelect.find('option:selected');
                if (selOpt.val()) {
                    $row.find('.variation-id-input').val(selOpt.val());
                    $row.find('.variation-label-input').val(selOpt.text());
                    const vPrice = parseFloat(selOpt.data('price')) || 0;
                    if (vPrice > 0) $row.find('.unit-price-input').val(vPrice.toFixed(2));
                }
            }
        }

        // Initial row
        $(document).ready(function () {
            addProductRow();
        });

        // Add row button
        $('#add-product-btn').on('click', function () {
            addProductRow();
        });

        // Delegate change for product select
        $(document).on('change', '.product-select', function () {
            const $row = $(this).closest('.product-row');
            const productId = $(this).val();
            // Reset variation state
            $row.find('.variation-row').hide();
            $row.find('.variation-id-input').val('');
            $row.find('.variation-label-input').val('');

            if (!productId) {
                $row.find('.unit-price-input').val('0');
                $row.find('.row-image').html('');
                recalcRowSubtotal($row);
                return;
            }

            const prod = products.find(p => p.id === Number(productId));
            if (prod) {
                $row.find('.unit-price-input').val(parseFloat(prod.price).toFixed(2));
                if (prod.primary_photo) {
                    $row.find('.row-image').html('<img src="' + prod.primary_photo + '" alt="' + escapeHtml(prod.name) + '" class="img-fluid img-thumbnail" style="max-width:120px" />');
                } else {
                    $row.find('.row-image').html('');
                }
                if (prod.is_variant && prod.variations && prod.variations.length > 0) {
                    populateVariationSelect($row, prod.variations, null);
                }
                recalcRowSubtotal($row);
                return;
            }

            // AJAX fallback
            axios.post("{{route('order_dependency')}}", {product_id: productId})
                .then(response => {
                    const price = parseFloat(response?.data?.product?.price) || 0;
                    const photo = response?.data?.product?.primary_photo || '';
                    const isVariant = response?.data?.product?.is_variant || false;
                    const variations = response?.data?.product?.variations || [];
                    $row.find('.unit-price-input').val(price.toFixed(2));
                    if (photo) {
                        $row.find('.row-image').html('<img src="' + photo + '" class="img-fluid img-thumbnail" style="max-width:120px" />');
                    } else {
                        $row.find('.row-image').html('');
                    }
                    if (isVariant && variations.length > 0) {
                        populateVariationSelect($row, variations, null);
                    }
                    recalcRowSubtotal($row);
                })
                .catch(() => {
                    $row.find('.unit-price-input').val('0');
                    $row.find('.row-image').html('');
                    recalcRowSubtotal($row);
                });
        });

        // Variation select change — update price + hidden inputs
        $(document).on('change', '.variation-select', function () {
            const $row = $(this).closest('.product-row');
            const selectedOpt = $(this).find('option:selected');
            const variationId = $(this).val();
            if (variationId) {
                const vPrice = parseFloat(selectedOpt.data('price')) || 0;
                $row.find('.variation-id-input').val(variationId);
                $row.find('.variation-label-input').val(selectedOpt.text());
                if (vPrice > 0) $row.find('.unit-price-input').val(vPrice.toFixed(2));
            } else {
                $row.find('.variation-id-input').val('');
                $row.find('.variation-label-input').val('');
            }
            recalcRowSubtotal($row);
        });

        // Quantity / unit price change
        $(document).on('input change', '.quantity-input, .unit-price-input', function () {
            recalcRowSubtotal($(this).closest('.product-row'));
        });

        // Remove row
        $(document).on('click', '.remove-row-btn', function () {
            $(this).closest('.product-row').remove();
            recalcTotals();
        });

        // Delivery location change → reset lock
        $('#delivery_location').on('change', function () {
            deliveryChargeManual = false;
            recalcTotals();
        });

        // Manual delivery charge → lock
        $('#delivery_charge').on('input', function () {
            deliveryChargeManual = true;
            recalcTotals();
        });

        // Manual order total → lock
        $('#order_total').on('input', function () {
            orderTotalManual = true;
            codManual = false;
            const orderTotal = parseFloat($(this).val()) || 0;
            const paidAmount = parseFloat($('#paid_amount').val()) || 0;
            $('#cod_amount').val((orderTotal - paidAmount).toFixed(2));
        });

        // Manual cod → lock
        $('#cod_amount').on('input', function () {
            codManual = true;
        });

        // Paid amount → recalc cod
        $('#paid_amount').on('change input', function () {
            if (!codManual) {
                const orderTotal = parseFloat($('#order_total').val()) || 0;
                const paidAmount = parseFloat($(this).val()) || 0;
                $('#cod_amount').val((orderTotal - paidAmount).toFixed(2));
            }
        });

        // Form submit: variation required for variant products
        var $form = $('#product-rows').closest('form');
        $form.on('submit', function (e) {
            var $productRows = $('#product-rows');
            const rows = $productRows.find('.product-row');
            if (rows.length === 0) {
                e.preventDefault();
                alert('Please add at least one product to the order.');
                return false;
            }
            let valid = true;
            let errMsg = '';
            rows.each(function () {
                const prod = $(this).find('.product-select').val();
                const qty = parseInt($(this).find('.quantity-input').val()) || 0;
                if (!prod || qty <= 0) {
                    valid = false;
                    errMsg = 'Please ensure every product row has a selected product and quantity >= 1.';
                    return false;
                }
                // If the variation row is visible, a variation must be chosen
                if ($(this).find('.variation-row').is(':visible') && !$(this).find('.variation-select').val()) {
                    valid = false;
                    errMsg = 'Please select a variation for every variant product.';
                    return false;
                }
            });
            if (!valid) {
                e.preventDefault();
                alert(errMsg);
                return false;
            }
        });

    </script>
@endpush
