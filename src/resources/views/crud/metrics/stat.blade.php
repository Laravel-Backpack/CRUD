<div class="card" data-metric="{{ $metric->name }}" data-metric-type="stat">
    <div class="card-body p-3">
        <div class="text-secondary text-uppercase fw-semibold small mb-2">{{ $metric->label }}</div>
        <div class="d-flex align-items-baseline">
            <div class="h1 mb-0 me-2" data-metric-value>
                <span class="spinner-border spinner-border-sm text-secondary" role="status"></span>
            </div>
            @if ($metric->compare)
                <div class="me-auto" data-metric-change>
                    {{-- Populated by JS --}}
                </div>
            @endif
        </div>
    </div>
</div>
