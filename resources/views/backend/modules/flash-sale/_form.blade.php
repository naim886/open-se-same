{{-- Flash Sale Form Partial — used by create & edit --}}

<div class="row">
    {{-- Name --}}
    <div class="col-lg-12 mb-4 position-relative">
        {{ html()->label('Flash Sale Name', 'name') }}
        <x-required />
        {{ html()->text('name')->placeholder('e.g., Eid Mega Flash Sale')->class('form-control')->value(old('name', $flashSale->name ?? '')) }}
        @error('name')<p class="validation-message">{{ $message }}</p>@enderror
    </div>

    {{-- Dates --}}
    <div class="col-lg-4 mb-4 position-relative">
        {{ html()->label('Start Date & Time', 'starts_at') }}
        <x-required />
        <input type="datetime-local" name="starts_at" id="starts_at"
               class="form-control @error('starts_at') is-invalid @enderror"
               value="{{ old('starts_at', isset($flashSale) ? $flashSale->starts_at->format('Y-m-d\TH:i') : '') }}">
        @error('starts_at')<p class="validation-message">{{ $message }}</p>@enderror
    </div>

    <div class="col-lg-4 mb-4 position-relative">
        {{ html()->label('End Date & Time', 'ends_at') }}
        <x-required />
        <input type="datetime-local" name="ends_at" id="ends_at"
               class="form-control @error('ends_at') is-invalid @enderror"
               value="{{ old('ends_at', isset($flashSale) ? $flashSale->ends_at->format('Y-m-d\TH:i') : '') }}">
        @error('ends_at')<p class="validation-message">{{ $message }}</p>@enderror
    </div>

    {{-- Status --}}
    <div class="col-lg-4 mb-4 position-relative">
        {{ html()->label('Status', 'status') }}
        <x-required />
        {{ html()->select('status', \App\Models\FlashSale::STATUS_LIST, old('status', $flashSale->status ?? 1))->class('form-select') }}
        @error('status')<p class="validation-message">{{ $message }}</p>@enderror
    </div>
</div>

{{-- Product Picker ────────────────────────────────────────────── --}}
<div class="card mt-2 mb-4">
    <div class="card-header d-flex align-items-center justify-content-between">
        <strong><i class="fas fa-bolt text-warning me-1"></i>Flash Sale Products</strong>
        <span class="badge bg-primary" id="productCount">0</span>
    </div>
    <div class="card-body">

        {{-- Add by Category --}}
        <div class="row g-2 mb-2 align-items-end">
            <div class="col">
                <label class="form-label mb-1 text-muted" style="font-size:12px;"><i class="fas fa-layer-group me-1"></i>Add all products from a category</label>
                <select id="categorySelect" class="form-select">
                    <option value="">— Select category —</option>
                    @foreach(\App\Models\Category::where('status', \App\Models\Category::STATUS_ACTIVE)->orderBy('name')->get() as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <button type="button" class="btn btn-outline-success" onclick="addByCategory()" id="addCategoryBtn">
                    <i class="fas fa-plus-circle"></i> Add All
                </button>
            </div>
        </div>
        <hr class="my-3">
        {{-- Search box --}}
        <div class="row g-2 mb-3">
            <div class="col">
                <input type="text" id="productSearchInput" class="form-control"
                       placeholder="Search product by name…">
            </div>
            <div class="col-auto">
                <button type="button" class="btn btn-outline-primary" onclick="searchProducts()">
                    <i class="fas fa-search"></i> Search
                </button>
            </div>
        </div>

        {{-- Search results --}}
        <div id="searchResults" class="mb-3" style="display:none;">
            <div class="list-group" id="searchResultsList"></div>
        </div>

        {{-- Selected products table --}}
        <div id="noProducts" class="text-center text-muted py-3" style="display:none;">
            <i class="fas fa-box-open fa-2x mb-2"></i><br>No products added yet.
        </div>
        <div class="table-responsive" id="selectedProductsWrap">
            <table class="table table-sm table-bordered align-middle mb-0" id="selectedProductsTable">
                <thead class="table-light">
                    <tr>
                        <th style="width:54px;"></th>
                        <th>Product</th>
                        <th>Original Price</th>
                        <th style="width:160px;">Flash Sale Price</th>
                        <th style="width:50px;"></th>
                    </tr>
                </thead>
                <tbody id="selectedProductsTbody">
                    {{-- JS renders rows here --}}
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Submit --}}
<div class="text-center">
    {{ html()->submit(isset($flashSale) ? 'Update Flash Sale' : 'Create Flash Sale')->class('btn btn-primary px-4') }}
    <a href="{{ route('flash-sale.index') }}" class="btn btn-secondary px-4 ms-2">Cancel</a>
</div>

@push('scripts')
@php
$preloadedProducts = isset($flashSale)
    ? $flashSale->products->map(function($p) {
        return [
            'id'         => $p->id,
            'name'       => $p->name,
            'price'      => $p->price,
            'sale_price' => $p->pivot->sale_price ?? $p->sale_price,
            'thumb'      => $p->primary_photo_thumb ? asset('storage/'.$p->primary_photo_thumb) : null,
        ];
    })->values()
    : collect();
@endphp
<script>
(function () {
    // Pre-loaded products (edit mode)
    var preloaded = @json($preloadedProducts);

    var selectedProducts = {};  // keyed by product id

    // ── Init ──────────────────────────────────────────────────────
    preloaded.forEach(function(p) { addProduct(p); });
    renderTable();

    // ── Search ────────────────────────────────────────────────────
    window.searchProducts = function() {
        var q = document.getElementById('productSearchInput').value.trim();
        var excludeIds = Object.keys(selectedProducts);

        var url = '{{ route('flash-sale.search-products') }}?q=' + encodeURIComponent(q)
            + excludeIds.map(function(id){ return '&exclude[]=' + id; }).join('');

        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                var list = document.getElementById('searchResultsList');
                list.innerHTML = '';
                if (!data.length) {
                    list.innerHTML = '<div class="list-group-item text-muted">No products found.</div>';
                    document.getElementById('searchResults').style.display = '';
                    return;
                }
                data.forEach(function(p) {
                    var item = document.createElement('button');
                    item.type = 'button';
                    item.className = 'list-group-item list-group-item-action d-flex align-items-center gap-2';
                    item.innerHTML = (p.thumb ? '<img src="'+p.thumb+'" style="width:38px;height:38px;object-fit:cover;border-radius:6px;">' : '<div style="width:38px;height:38px;background:#eee;border-radius:6px;"></div>')
                        + '<div class="flex-grow-1"><strong>' + escHtml(p.name) + '</strong><br>'
                        + '<small class="text-muted">Price: ৳' + p.price + (p.sale_price ? ' | Sale: ৳'+p.sale_price : '') + '</small></div>'
                        + '<span class="badge bg-success ms-auto"><i class="fas fa-plus"></i> Add</span>';
                    item.onclick = function() { addProduct(p); renderTable(); document.getElementById('searchResults').style.display = 'none'; };
                    list.appendChild(item);
                });
                document.getElementById('searchResults').style.display = '';
            });
    };

    // Enter key triggers search
    document.getElementById('productSearchInput').addEventListener('keydown', function(e){
        if (e.key === 'Enter') { e.preventDefault(); searchProducts(); }
    });

    // ── Add by Category ───────────────────────────────────────────
    window.addByCategory = function() {
        var categoryId = document.getElementById('categorySelect').value;
        if (!categoryId) { alert('Please select a category first.'); return; }
        var btn = document.getElementById('addCategoryBtn');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading…';

        var excludeIds = Object.keys(selectedProducts);
        var url = '{{ route('flash-sale.products-by-category') }}?category_id=' + categoryId
            + excludeIds.map(function(id){ return '&exclude[]=' + id; }).join('');

        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-plus-circle"></i> Add All';
                if (!data.length) { alert('No new products found in this category.'); return; }
                data.forEach(function(p) { addProduct(p); });
                renderTable();
            })
            .catch(function() {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-plus-circle"></i> Add All';
                alert('Failed to load products. Please try again.');
            });
    };

    // ── Add product ───────────────────────────────────────────────
    function addProduct(p) {
        if (!selectedProducts[p.id]) {
            selectedProducts[p.id] = {
                id:         p.id,
                name:       p.name,
                price:      p.price,
                sale_price: p.sale_price || '',
                thumb:      p.thumb || null,
            };
            renderTable();
        }
    }

    // ── Remove product ────────────────────────────────────────────
    window.removeProduct = function(id) {
        delete selectedProducts[id];
        renderTable();
    };

    // ── Update sale price from input ──────────────────────────────
    window.updateSalePrice = function(id, val) {
        if (selectedProducts[id]) selectedProducts[id].sale_price = val;
    };

    // ── Render ────────────────────────────────────────────────────
    function renderTable() {
        var ids    = Object.keys(selectedProducts);
        var tbody  = document.getElementById('selectedProductsTbody');
        var wrap   = document.getElementById('selectedProductsWrap');
        var noMsg  = document.getElementById('noProducts');
        var count  = document.getElementById('productCount');

        tbody.innerHTML = '';
        count.textContent = ids.length;

        if (!ids.length) {
            wrap.style.display  = 'none';
            noMsg.style.display = '';
            return;
        }
        wrap.style.display  = '';
        noMsg.style.display = 'none';

        ids.forEach(function(id, idx) {
            var p  = selectedProducts[id];
            var tr = document.createElement('tr');
            tr.innerHTML =
                '<td>' + (p.thumb ? '<img src="'+p.thumb+'" style="width:44px;height:44px;object-fit:cover;border-radius:6px;">' : '<div style="width:44px;height:44px;background:#eee;border-radius:6px;"></div>') + '</td>'
                + '<td><strong>' + escHtml(p.name) + '</strong>'
                + ' <input type="hidden" name="product_ids['+idx+']" value="'+p.id+'"></td>'
                + '<td class="text-muted">৳' + p.price + '</td>'
                + '<td><input type="number" name="sale_prices['+idx+']" class="form-control form-control-sm" placeholder="Leave blank to use product\'s sale price" step="0.01" min="0" value="' + (p.sale_price || '') + '" onchange="updateSalePrice('+p.id+', this.value)"></td>'
                + '<td class="text-center"><button type="button" class="btn btn-sm btn-danger" onclick="removeProduct('+p.id+')"><i class="fas fa-times"></i></button></td>';
            tbody.appendChild(tr);
        });
    }

    // ── Utility ───────────────────────────────────────────────────
    function escHtml(s) {
        return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }
})();
</script>
@endpush
