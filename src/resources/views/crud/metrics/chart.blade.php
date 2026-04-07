<div class="card" data-metric="{{ $metric->name }}" data-metric-type="{{ $metric->type }}">
    <div class="card-header">
        <div class="card-title">{{ $metric->label }}</div>
    </div>
    <div class="card-body">
        <div class="d-flex justify-content-center align-items-center" data-metric-placeholder style="min-height: 200px;">
            <span class="spinner-border spinner-border-sm text-secondary" role="status"></span>
        </div>
        <canvas data-metric-canvas style="display:none;"></canvas>
    </div>
</div>
