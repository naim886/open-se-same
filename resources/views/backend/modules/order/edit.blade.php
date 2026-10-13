@extends('backend.layouts.app')

@section('title', 'Edit Order')

@section('content')

{{-- Action bar --}}
<div class="d-flex justify-content-between align-items-center mb-3 px-1">
    <h5 class="mb-0">Edit Order #M-{{ $order->order_number }}</h5>
    <div>
        @if(!$order->consignment_id)
        <button type="button" class="btn btn-sm btn-warning me-2"
            onclick="openCourierModal(
                {{ $order->id }},
                '{{ addslashes($order->order_number) }}',
                '{{ addslashes($order->customer_name) }}',
                '{{ addslashes($order->customer_phone) }}',
                {{ (float)$order->total_amount }}
            )">
            <i class="fas fa-truck me-1"></i> Send to Courier
        </button>
        @else
        <span class="btn btn-sm btn-outline-success me-2" style="cursor:default;" title="Consignment: {{ $order->consignment_id }}">
            <i class="fas fa-check-circle me-1"></i> Courier Sent
        </span>
        @endif
        <a href="{{ route('orders.show', $order->id) }}" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-eye me-1"></i> View
        </a>
    </div>
</div>

{{html()->modelForm($order,'PUT', route('orders.update', $order->id))->open()}}
@include('backend.modules.order.partials._form')
{{html()->form()->close()}}

{{-- Order Notes Section (separate from the main form) --}}
<div class="row justify-content-center mt-4 mb-5 order-notes-card">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="ri-sticky-note-line me-1"></i> Order Notes</h5>
                <span class="badge bg-secondary">{{ $order->orderNotes->count() }}</span>
            </div>
            <div class="card-body">
                {{-- Existing notes --}}
                @if($order->orderNotes->isEmpty())
                    <p class="text-muted">No notes yet.</p>
                @else
                    <div class="notes-list mb-4" style="max-height:300px; overflow-y:auto;">
                        @foreach($order->orderNotes as $note)
                            <div class="d-flex gap-2 mb-3 pb-2 border-bottom">
                                <div class="flex-shrink-0">
                                    <span class="avatar-title bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center" style="width:34px;height:34px;font-size:13px;">
                                        {{ strtoupper(substr($note->user->name ?? 'S', 0, 1)) }}
                                    </span>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between">
                                        <strong class="text-sm">{{ $note->user->name ?? 'System' }}</strong>
                                        <small class="text-muted" title="{{ $note->created_at->format('M d, Y h:i A') }}">
                                            {{ $note->created_at->diffForHumans() }}
                                            <span class="ms-1 opacity-75">({{ $note->created_at->format('M d, h:i A') }})</span>
                                        </small>
                                    </div>
                                    <p class="mb-0 mt-1" style="white-space:pre-wrap;">{{ $note->note }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                {{-- Add new note --}}
                <form action="{{ route('orders.addNote', $order->id) }}" method="POST" class="d-flex gap-2 align-items-end">
                    @csrf
                    <div class="flex-grow-1">
                        <label class="form-label mb-1">Add New Note</label>
                        <textarea name="note" class="form-control" rows="2" placeholder="Write a note and press Add Note..." required></textarea>
                    </div>
                    <div>
                        <button type="submit" class="btn btn-primary">
                            <i class="ri-add-line"></i> Add Note
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- ===== Steadfast Courier Modal ===== --}}
<div class="modal fade" id="courierModal" tabindex="-1" aria-labelledby="courierModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background:#1e2130;color:#fff;border:1px solid rgba(255,255,255,0.2);">
            <div class="modal-header" style="border-bottom:1px solid rgba(255,255,255,0.15);">
                <h5 class="modal-title" id="courierModalLabel"><i class="fas fa-truck me-2"></i>Send to Steadfast Courier</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="courierForm" method="POST">
                @csrf
                <div class="modal-body">
                    <table class="table table-sm mb-3" style="color:inherit;--bs-table-bg:transparent;--bs-table-color:inherit;">
                        <tbody>
                            <tr><td style="opacity:.6;width:40%">Order #</td><td><strong id="cm-order-num"></strong></td></tr>
                            <tr><td style="opacity:.6;">Customer</td><td id="cm-name"></td></tr>
                            <tr><td style="opacity:.6;">Phone</td><td id="cm-phone"></td></tr>
                            <tr><td style="opacity:.6;">COD Amount</td><td id="cm-amount"></td></tr>
                        </tbody>
                    </table>
                    <div id="cm-bengali-warning" class="alert alert-warning d-none" style="background:rgba(255,193,7,.15);border-color:#ffc107;color:#ffc107;">
                        <i class="fas fa-exclamation-triangle me-1"></i>
                        <strong>Bengali digits detected in phone number!</strong><br>
                        They will be auto-converted to English before submission.
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" id="cm-convert-check" value="1" checked>
                            <label class="form-check-label" for="cm-convert-check">
                                Convert to English <span id="cm-converted" class="badge bg-success ms-1"></span>
                            </label>
                        </div>
                    </div>
                    <input type="hidden" id="cm-convert-hidden" name="convert_phone" value="0">
                </div>
                <div class="modal-footer" style="border-top:1px solid rgba(255,255,255,0.15);">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning"><i class="fas fa-paper-plane me-1"></i>Send to Courier</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
const bengaliChars = '০১২৩৪৫৬৭৮৯';
const englishChars = '0123456789';
function hasBengali(str) { return /[০-৯]/.test(str); }
function toBengaliEnglish(str) {
    return str.split('').map(c => { const i = bengaliChars.indexOf(c); return i >= 0 ? englishChars[i] : c; }).join('');
}
function openCourierModal(orderId, orderNum, name, phone, total) {
    document.getElementById('cm-order-num').textContent = 'M-' + orderNum;
    document.getElementById('cm-name').textContent      = name;
    document.getElementById('cm-phone').textContent     = phone;
    document.getElementById('cm-amount').textContent    = parseFloat(total).toFixed(2) + ' tk';
    const warning       = document.getElementById('cm-bengali-warning');
    const convertCheck  = document.getElementById('cm-convert-check');
    const convertHidden = document.getElementById('cm-convert-hidden');
    const converted     = document.getElementById('cm-converted');
    if (hasBengali(phone)) {
        warning.classList.remove('d-none');
        converted.textContent  = '→ ' + toBengaliEnglish(phone);
        convertCheck.checked   = true;
        convertHidden.value    = '1';
        convertCheck.onchange = () => { convertHidden.value = convertCheck.checked ? '1' : '0'; };
    } else {
        warning.classList.add('d-none');
        convertHidden.value = '0';
    }
    document.getElementById('courierForm').action = '{{ url("admin/orders") }}/' + orderId + '/send-to-courier';
    new bootstrap.Modal(document.getElementById('courierModal')).show();
}
</script>

@endsection
@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        .select2-container--default .select2-selection--single { background: var(--theme-color, #1e2130); border-color: rgba(255,255,255,.2); color: #fff; height: 44px; }
        .select2-container--default .select2-selection--single .select2-selection__rendered { color: #fff; line-height: 42px; }
        .select2-container--default .select2-selection--single .select2-selection__arrow { height: 42px; }
        .select2-dropdown { background: var(--theme-color, #1e2130); border-color: rgba(255,255,255,.2); }
        .select2-container--default .select2-results__option { color: #fff; }
        .select2-container--default .select2-results__option--highlighted { background: rgba(13,110,253,.35); }
        .select2-search--dropdown .select2-search__field { background: rgba(255,255,255,.08); border-color: rgba(255,255,255,.2); color: #fff; }
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

            // Prepare existing order items for JS
            $existing_items_for_js = [];
            foreach ($order->orderItems ?? collect([]) as $it) {
                $existing_items_for_js[] = [
                    'product_id'      => $it->product_id,
                    'quantity'        => $it->quantity,
                    'unit_price'      => (float)$it->unit_price,
                    'variation_id'    => $it->variation_id,
                    'variation_label' => $it->variation_label,
                ];
            }

            // Prepare delivery cost mapping for JS
            $delivery_cost_js = \App\Models\Order::DELIVERY_COST_LIST;
        @endphp

        // Prepare products data for JS to use (id, name, price, primary_photo)
        const products = @json($products_for_js);

        // Small HTML-escape helper used when inserting product names into option HTML
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

        // Existing order items (for edit) passed from server
        const existingItems = @json($existing_items_for_js);

        // Delivery costs mapping for calculation (use numeric costs from model)
        const DELIVERY_COST = @json($delivery_cost_js);

        let rowIndex = 0;
        // Manual override flags
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
            var $productRows = $('#product-rows');
            $productRows.find('.product-row').each(function () {
                const subtotal = parseFloat($(this).find('.row-subtotal').text()) || 0;
                productsTotal += subtotal;
            });

            var $productTotalPrice = $('#product_total_price');

            if (manual) {
                productsTotal = parseFloat($productTotalPrice.val()) || 0;
            } else {
                $productTotalPrice.val(productsTotal.toFixed(2));
            }

            // delivery charge: auto-fill unless manually overridden
            var $deliveryChargeEl = $('#delivery_charge');
            if (!deliveryChargeManual) {
                let deliveryLocation = $('#delivery_location').val();
                let computedDeliveryCharge = 0;
                if (deliveryLocation && DELIVERY_COST.hasOwnProperty(deliveryLocation)) {
                    computedDeliveryCharge = parseFloat(DELIVERY_COST[deliveryLocation]) || 0;
                } else {
                    const firstKey = Object.keys(DELIVERY_COST)[0];
                    computedDeliveryCharge = parseFloat(DELIVERY_COST[firstKey]) || 0;
                }
                $deliveryChargeEl.val(computedDeliveryCharge.toFixed(2));
            }

            const deliveryCharge = parseFloat($deliveryChargeEl.val()) || 0;
            const paidAmount = parseFloat($('#paid_amount').val()) || 0;

            if (!orderTotalManual) {
                const orderTotal = productsTotal + deliveryCharge;
                $('#order_total').val(orderTotal.toFixed(2));
            }

            const orderTotal = parseFloat($('#order_total').val()) || 0;

            if (!codManual) {
                $('#cod_amount').val((orderTotal - paidAmount).toFixed(2));
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

        // Helper: set product fields for a row after adding the HTML
        function fillRowWithItem($row, item) {
            // Init Select2 first so display updates correctly
            initProductSelect($row.find('.product-select'));
            // Set product select value and update Select2 display (no native change fired)
            $row.find('.product-select').val(item.product_id).trigger('change.select2');
            // try to find local product data
            const prod = products.find(p => p.id === item.product_id);
            if (prod) {
                $row.find('.unit-price-input').val(parseFloat(item.unit_price || prod.price).toFixed(2));
                if (prod.primary_photo) {
                    $row.find('.row-image').html('<img src="' + prod.primary_photo + '" alt="' + escapeHtml(prod.name) + '" class="img-fluid img-thumbnail" style="max-width:120px" />');
                }
                if (prod.is_variant && prod.variations && prod.variations.length > 0) {
                    populateVariationSelect($row, prod.variations, item.variation_id || null);
                    if (item.variation_label) {
                        $row.find('.variation-label-input').val(item.variation_label);
                    }
                }
            } else {
                // fallback to AJAX if not found locally
                axios.post("{{ route('order_dependency') }}", {product_id: item.product_id})
                    .then(response => {
                        const price = parseFloat(response?.data?.product?.price) || 0;
                        const photo = response?.data?.product?.primary_photo || '';
                        const isVariant = response?.data?.product?.is_variant || false;
                        const variations = response?.data?.product?.variations || [];
                        $row.find('.unit-price-input').val(parseFloat(item.unit_price || price).toFixed(2));
                        if (photo) $row.find('.row-image').html('<img src="' + photo + '" alt="Product image" class="img-fluid img-thumbnail" style="max-width:120px" />');
                        if (isVariant && variations.length > 0) {
                            populateVariationSelect($row, variations, item.variation_id || null);
                            if (item.variation_label) $row.find('.variation-label-input').val(item.variation_label);
                        }
                        recalcRowSubtotal($row);
                    })
                    .catch(() => {
                        $row.find('.unit-price-input').val('0');
                        $row.find('.row-image').html('');
                        recalcRowSubtotal($row);
                    });
            }

            $row.find('.quantity-input').val(item.quantity);
            // compute subtotal immediately
            const subtotal = (parseFloat($row.find('.unit-price-input').val()) || 0) * (parseFloat(item.quantity) || 0);
            $row.find('.row-subtotal').text(subtotal.toFixed(2));
        }

        // Initial row population: existing items or one blank row
        $(document).ready(function () {
            // Preserve the existing delivery_charge on load only if it has a value
            const existingDeliveryCharge = $('#delivery_charge').val();
            if (existingDeliveryCharge !== '' && !isNaN(parseFloat(existingDeliveryCharge))) {
                deliveryChargeManual = true;
            }
            // Preserve existing order_total only if DB has a value for it
            const existingOrderTotal = $('#order_total').val();
            if (existingOrderTotal !== '' && !isNaN(parseFloat(existingOrderTotal))) {
                orderTotalManual = true;
            }
            // COD: auto-calc unless DB has explicit value
            const existingCod = $('#cod_amount').val();
            if (existingCod !== '' && !isNaN(parseFloat(existingCod))) {
                codManual = true;
            }

            if (Array.isArray(existingItems) && existingItems.length > 0) {
                existingItems.forEach(function (it) {
                    const idx = rowIndex++;
                    const html = makeRowHtml(idx);
                    $('#product-rows').append(html);
                    const $row = $("#product-rows .product-row[data-index='" + idx + "']");
                    fillRowWithItem($row, it);
                });
                // ensure totals reflect existing data
                recalcTotals();
            } else {
                addProductRow();
            }
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

            // Try to resolve locally first to avoid extra requests
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

            // fallback to AJAX
            axios.post("{{ route('order_dependency') }}", {product_id: productId})
                .then(response => {
                    const price = parseFloat(response?.data?.product?.price) || 0;
                    const photo = response?.data?.product?.primary_photo || '';
                    const isVariant = response?.data?.product?.is_variant || false;
                    const variations = response?.data?.product?.variations || [];
                    $row.find('.unit-price-input').val(price.toFixed(2));
                    if (photo) {
                        $row.find('.row-image').html('<img src="' + photo + '" alt="Product image" class="img-fluid img-thumbnail" style="max-width:120px" />');
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

        // Delegate change for quantity and unit price (unit price is readonly but included if ever changed)
        $(document).on('input change', '.quantity-input, .unit-price-input', function () {
            const $row = $(this).closest('.product-row');
            recalcRowSubtotal($row);
        });

        // Remove row
        $(document).on('click', '.remove-row-btn', function () {
            $(this).closest('.product-row').remove();
            recalcTotals();
        });

        // Recalc on delivery_location change → reset delivery charge lock
        $('#delivery_location').on('change', function () {
            deliveryChargeManual = false;
            recalcTotals();
        });

        // When user manually types delivery_charge → lock it
        $('#delivery_charge').on('input', function () {
            deliveryChargeManual = true;
            recalcTotals();
        });

        // When user manually types order_total → lock it, recalc cod
        $('#order_total').on('input', function () {
            orderTotalManual = true;
            codManual = false;
            const orderTotal = parseFloat($(this).val()) || 0;
            const paidAmount = parseFloat($('#paid_amount').val()) || 0;
            $('#cod_amount').val((orderTotal - paidAmount).toFixed(2));
        });

        // When user manually types cod_amount → lock it
        $('#cod_amount').on('input', function () {
            codManual = true;
        });

        // paid_amount changes → recalc cod (unless cod is locked)
        $('#paid_amount').on('change input', function () {
            if (!codManual) {
                const orderTotal = parseFloat($('#order_total').val()) || 0;
                const paidAmount = parseFloat($(this).val()) || 0;
                $('#cod_amount').val((orderTotal - paidAmount).toFixed(2));
            }
        });

        // Recalc when user types into product_total_price (manual mode)
        $('#product_total_price').on('input', function () {
            recalcTotals(true);
        });

        // Form submit: simple client-side checks before sending to server
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
                    return false; // break
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
            // allow submit
        });

    </script>
@endpush

@push('styles')
    <style>
        /* Fix muted text — Bootstrap default is near-black, invisible on dark bg */
        .text-muted, small.text-muted, .text-muted small, .text-muted span {
            color: rgba(255,255,255,0.5) !important;
        }
        /* Dark theme for Order Notes card */
        .order-notes-card,
        .order-notes-card .card,
        .order-notes-card .card-header,
        .order-notes-card .card-body {
            background: transparent !important;
            color: inherit !important;
        }
        .order-notes-card .card {
            border: 1px solid rgba(255,255,255,0.15) !important;
        }
        .order-notes-card .card-header {
            border-bottom: 1px solid rgba(255,255,255,0.15) !important;
        }
        .order-notes-card .notes-list .border-bottom {
            border-color: rgba(255,255,255,0.12) !important;
        }
        .order-notes-card textarea.form-control {
            background: rgba(255,255,255,0.07) !important;
            color: inherit !important;
            border-color: rgba(255,255,255,0.2) !important;
        }
        .order-notes-card textarea.form-control::placeholder {
            color: rgba(255,255,255,0.35) !important;
        }
        /* Improve product row visuals for the edit order form */
        .product-row {
            border-radius: 6px;
            background: rgba(0,0,0,0.02);
        }
        .product-row .row-subtotal {
            font-weight: 700;
            font-size: 1rem;
        }
        .product-row .row-image img {
            max-height: 80px;
            object-fit: contain;
            border-radius: 4px;
        }
        #product-rows .product-row + .product-row {
            margin-top: .75rem;
        }
    </style>
@endpush
