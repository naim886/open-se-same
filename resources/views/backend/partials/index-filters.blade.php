@php
    $action        = $action ?? url()->current();
    $search        = $search ?? '';
    $searchName    = $searchName ?? 'search';
    $placeholder   = $placeholder ?? 'Search...';
    $selects       = $selects ?? [];
    $hasFilters    = $search !== '';

    foreach ($selects as $select) {
        if (($select['value'] ?? '') !== '') {
            $hasFilters = true;
            break;
        }
    }
@endphp
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-3">
        <form method="GET" action="{{ $action }}" class="d-flex flex-wrap gap-2 align-items-end">
            <div>
                <label class="form-label mb-1 small">Search</label>
                <input type="text" name="{{ $searchName }}" class="form-control form-control-sm"
                       value="{{ $search }}" placeholder="{{ $placeholder }}" style="min-width:220px;">
            </div>

            @foreach($selects as $select)
                <div>
                    <label class="form-label mb-1 small">{{ $select['label'] }}</label>
                    <select name="{{ $select['name'] }}" class="form-select form-select-sm" style="min-width:{{ $select['width'] ?? '150px' }};">
                        <option value="">All</option>
                        @foreach(($select['options'] ?? []) as $optionValue => $optionLabel)
                            <option value="{{ $optionValue }}" @selected((string) ($select['value'] ?? '') === (string) $optionValue)>
                                {{ $optionLabel }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endforeach

            <button type="submit" class="btn btn-sm btn-primary">
                <i class="fas fa-search me-1"></i>Filter
            </button>

            @if($hasFilters)
                <a href="{{ $action }}" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-times me-1"></i>Clear
                </a>
            @endif
        </form>
    </div>
</div>
