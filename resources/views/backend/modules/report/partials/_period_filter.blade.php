{{-- Shared reporting period filter.
     Expects: $action (form target URL), $period, $request
     Optional: set $showGranularity to render the day/week/month selector. --}}
@php
    $periodOptions = [
        'today'      => 'Today',
        'last_7'     => 'Last 7 Days',
        'last_30'    => 'Last 30 Days',
        'this_month' => 'This Month',
        'last_month' => 'Last Month',
        'this_year'  => 'This Year',
        'custom'     => 'Custom',
    ];
@endphp

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-3">
        <form method="GET" action="{{ $action }}" id="reportForm" class="d-flex flex-wrap gap-2 align-items-end">
            <div class="btn-group btn-group-sm flex-wrap me-1" role="group">
                @foreach($periodOptions as $val => $lbl)
                    <button type="button"
                            class="btn {{ $period === $val ? 'btn-primary' : 'btn-outline-secondary' }} period-btn"
                            data-period="{{ $val }}">{{ $lbl }}</button>
                @endforeach
            </div>

            <input type="hidden" name="period" id="periodInput" value="{{ $period }}">

            @isset($showGranularity)
                @include('backend.modules.report.partials._granularity', [
                    'granularity'       => $granularity,
                    'granularityLabels' => $granularityLabels,
                ])
            @endisset

            <div id="customRange" class="{{ $period === 'custom' ? 'd-flex' : 'd-none' }} gap-2 align-items-end flex-wrap">
                <div>
                    <label class="form-label mb-1 small">From</label>
                    <input type="date" name="date_from" class="form-control form-control-sm"
                           value="{{ $period === 'custom' ? $request->date_from : '' }}">
                </div>
                <div>
                    <label class="form-label mb-1 small">To</label>
                    <input type="date" name="date_to" class="form-control form-control-sm"
                           value="{{ $period === 'custom' ? $request->date_to : '' }}">
                </div>
            </div>

            <button type="submit" class="btn btn-sm btn-primary ms-auto">
                <i class="fas fa-search me-1"></i>Apply
            </button>
        </form>
    </div>
</div>

@push('scripts')
<script>
(function () {
    const form        = document.getElementById('reportForm');
    const periodInput = document.getElementById('periodInput');
    const customRange = document.getElementById('customRange');

    if (!form) return;

    document.querySelectorAll('.period-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.period-btn').forEach(b =>
                b.classList.replace('btn-primary', 'btn-outline-secondary'));
            this.classList.replace('btn-outline-secondary', 'btn-primary');

            periodInput.value = this.dataset.period;
            customRange.classList.toggle('d-flex', this.dataset.period === 'custom');
            customRange.classList.toggle('d-none', this.dataset.period !== 'custom');

            if (this.dataset.period !== 'custom') form.submit();
        });
    });

    const granInput = document.getElementById('granularityInput');
    document.querySelectorAll('.granularity-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.granularity-btn').forEach(b =>
                b.classList.replace('btn-primary', 'btn-outline-secondary'));
            this.classList.replace('btn-outline-secondary', 'btn-primary');

            if (granInput) granInput.value = this.dataset.granularity;
            form.submit();
        });
    });
})();
</script>
@endpush
