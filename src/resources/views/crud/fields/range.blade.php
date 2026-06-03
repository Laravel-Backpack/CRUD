{{-- html5 range input --}}
@php
    $rangeMin = $field['attributes']['min'] ?? 0;
    $rangeMax = $field['attributes']['max'] ?? 100;
    $rangeValue = old_empty_or_null($field['name'], '') ?? $field['value'] ?? $field['default'] ?? '';
    if ($rangeValue === '' || $rangeValue === null) {
        $rangeValue = $rangeMin;
    }
    $showMinMax = $field['show_min_max'] ?? true;
@endphp
@include('crud::fields.inc.wrapper_start')
    <label>{!! $field['label'] !!}</label>
    @include('crud::fields.inc.translatable_icon')
    <div class="bp-range-field d-flex align-items-center gap-2">
        @if($showMinMax)
            <span class="bp-range-label small">{{ $rangeMin }}</span>
        @endif
        <div class="bp-range-track position-relative flex-grow-1">
            <input
                type="range"
                name="{{ $field['name'] }}"
                value="{{ $rangeValue }}"
                min="{{ $rangeMin }}"
                max="{{ $rangeMax }}"
                class="form-range w-100"
                data-init-function="bpFieldInitRangeElement"
                @include('crud::fields.inc.attributes')
                >
            <span class="bp-range-bubble">{{ $rangeValue }}</span>
        </div>
        @if($showMinMax)
            <span class="bp-range-label small">{{ $rangeMax }}</span>
        @endif
    </div>

    {{-- HINT --}}
    @if (isset($field['hint']))
        <p class="help-block">{!! $field['hint'] !!}</p>
    @endif
@include('crud::fields.inc.wrapper_end')

@push('crud_fields_styles')
@bassetBlock('backpack/crud/fields/range-field.css')
<style>
    .bp-range-track { padding-top: 1.6rem; }
    .bp-range-label { color: var(--bp-range-label-color, #6c757d); }
    .bp-range-bubble {
        position: absolute;
        top: 0;
        transform: translateX(-50%);
        background: var(--bp-range-bubble-bg, #7c69ef);
        color: var(--bp-range-bubble-color, #fff);
        font-size: 0.75rem;
        line-height: 1;
        padding: 0.2rem 0.4rem;
        border-radius: 0.25rem;
        pointer-events: none;
        white-space: nowrap;
    }
    .bp-range-bubble::after {
        content: '';
        position: absolute;
        left: 50%;
        bottom: -4px;
        transform: translateX(-50%);
        border: 4px solid transparent;
        border-top-color: var(--bp-range-bubble-bg, #7c69ef);
        border-bottom: 0;
    }
</style>
@endBassetBlock
@endpush

@push('crud_fields_scripts')
@bassetBlock('backpack/crud/fields/range-field.js')
<script>
    function bpFieldInitRangeElement($element) {
        const input = $element[0];
        const bubble = input.nextElementSibling;
        if (!bubble || !bubble.classList.contains('bp-range-bubble')) return;

        // native range thumb is ~16px wide; offset the bubble so it tracks the thumb center
        const THUMB_WIDTH = 16;

        function positionBubble() {
            bubble.textContent = input.value;
            const min = parseFloat(input.min) || 0;
            const max = parseFloat(input.max) || 100;
            const val = parseFloat(input.value) || 0;
            const ratio = max === min ? 0 : (val - min) / (max - min);
            const offset = ratio * (input.offsetWidth - THUMB_WIDTH) + THUMB_WIDTH / 2;
            bubble.style.left = offset + 'px';
        }

        input.addEventListener('input', positionBubble);
        window.addEventListener('resize', positionBubble);
        // defer to next frame so layout is settled (e.g. inside tabs/modals)
        requestAnimationFrame(positionBubble);
    }
</script>
@endBassetBlock
@endpush
