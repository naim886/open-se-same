{{-- Day/week/month grouping selector. Expects: $granularity, $granularityLabels --}}
<div class="me-1">
    <label class="form-label mb-1 small d-block">Group by</label>
    <input type="hidden" name="granularity" id="granularityInput" value="{{ $granularity }}">
    <div class="btn-group btn-group-sm" role="group">
        @foreach($granularityLabels as $val => $lbl)
            <button type="button"
                    class="btn {{ $granularity === $val ? 'btn-primary' : 'btn-outline-secondary' }} granularity-btn"
                    data-granularity="{{ $val }}">{{ $lbl }}</button>
        @endforeach
    </div>
</div>
