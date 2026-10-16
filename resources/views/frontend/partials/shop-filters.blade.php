@php
    // Shared filter markup used by the desktop sidebar and the mobile drawer.
    // Included twice per page, so it deliberately uses classes (never ids).
    $category         = $category ?? null;
    $brand            = $brand ?? null;
    $categoryTree     = $categoryTree ?? collect();
    $variationFilters = $variationFilters ?? collect();
    $filterAction     = $filterAction ?? route('shop.products');

    $currentSlug        = $category->slug ?? null;
    $selectedVariations = (array) request('variation', []);
    $hasActiveFilters   = request()->hasAny(['variation', 'min_price', 'max_price']);
    $clearUrl           = request('q') ? $filterAction . '?q=' . urlencode(request('q')) : $filterAction;
@endphp

<h6 class="filter-title border-bottom pb-2 mb-3">📂 Categories</h6>
<div class="filter-accordion">
    @foreach($categoryTree as $topCategory)
        @php
            $hasChildren = $topCategory->children->isNotEmpty();
            $isOpen      = $currentSlug === $topCategory->slug
                || ($hasChildren && $topCategory->children->contains('slug', $currentSlug));
        @endphp
        <div class="filter-category">
            <div class="filter-category-row">
                <a href="{{ route('shop.category', $topCategory->slug) }}"
                   class="filter-category-link {{ $currentSlug === $topCategory->slug ? 'active' : '' }}">
                    <span>{{ $topCategory->name }}</span>
                    <small>({{ $topCategory->products_count }})</small>
                </a>
                @if($hasChildren)
                    <button type="button" class="filter-category-toggle {{ $isOpen ? 'open' : '' }}"
                            aria-expanded="{{ $isOpen ? 'true' : 'false' }}"
                            aria-label="Show {{ $topCategory->name }} sub categories">
                        <i class="fas fa-chevron-down" aria-hidden="true"></i>
                    </button>
                @endif
            </div>
            @if($hasChildren)
                <div class="filter-subcategory-list" style="{{ $isOpen ? '' : 'display:none;' }}">
                    @foreach($topCategory->children as $child)
                        <a href="{{ route('shop.category', $child->slug) }}"
                           class="filter-subcategory-link {{ $currentSlug === $child->slug ? 'active' : '' }}">
                            <span>{{ $child->name }}</span>
                            <small>({{ $child->products_count }})</small>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    @endforeach
</div>

<form action="{{ $filterAction }}" method="GET" class="filter-form">
    @if(request('q'))
        <input type="hidden" name="q" value="{{ request('q') }}">
    @endif
    @if(request('sort'))
        <input type="hidden" name="sort" value="{{ request('sort') }}">
    @endif

    @if($variationFilters->isNotEmpty())
        <hr>
        <h6 class="filter-title pt-2 mb-3">🎨 Filter by Variation</h6>
        @foreach($variationFilters as $group)
            @php
                $selected = array_map('strval', (array) ($selectedVariations[$group['id']] ?? []));
            @endphp
            <div class="filter-variation-group">
                <button type="button" class="filter-variation-toggle {{ $selected ? 'open' : '' }}"
                        aria-expanded="{{ $selected ? 'true' : 'false' }}">
                    <span>{{ $group['name'] }}</span>
                    <i class="fas fa-chevron-down" aria-hidden="true"></i>
                </button>
                <div class="filter-variation-values" style="{{ $selected ? '' : 'display:none;' }}">
                    @foreach($group['values'] as $value)
                        <label class="filter-check">
                            <input type="checkbox" class="filter-variation-input"
                                   name="variation[{{ $group['id'] }}][]" value="{{ $value['id'] }}"
                                   {{ in_array((string) $value['id'], $selected, true) ? 'checked' : '' }}>
                            <span>{{ $value['name'] }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
        @endforeach
    @endif

    <hr>
    <h6 class="filter-title pt-2">💰 Filter by Price</h6>
    <div class="row g-2 mt-1">
        <div class="col-6">
            <input type="number" name="min_price" class="form-control form-control-sm" placeholder="Min ৳"
                   value="{{ request('min_price') }}">
        </div>
        <div class="col-6">
            <input type="number" name="max_price" class="form-control form-control-sm" placeholder="Max ৳"
                   value="{{ request('max_price') }}">
        </div>
        <div class="col-12 d-flex gap-2">
            <button type="submit" class="btn btn-sm w-100" style="background:var(--primary-gradient);color:#fff;">
                Apply Filter
            </button>
            @if($hasActiveFilters)
                <a href="{{ $clearUrl }}" class="btn btn-sm btn-outline-secondary">Clear</a>
            @endif
        </div>
    </div>
</form>
