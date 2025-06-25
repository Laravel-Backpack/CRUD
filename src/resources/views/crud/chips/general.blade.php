@php
    $defaultHeading = [
        'content' => null, // text to show in the heading
        'element' => 'a',
        'class' => 'mb-1 d-inline-block',
    ];

    $defaultImage = [
        'content' => null, // image url
        'element' => 'a',
        'class' => 'avatar avatar-2 rounded',
    ];

    // merge any passed parameters with the defaults
    $heading = array_merge($defaultHeading, $heading ?? []); // the main heading showing in the chip
    $image = array_merge($defaultImage, $image ?? []); // the image that shows up in the chip (if any)
    $details = $details ?? []; // the details that show up on the second row (if any)

    // ensure the details have the minimum info
    foreach ($details as $key => $detail) {
        $details[$key]['element'] = $detail['element'] ?? 'span';
        $details[$key]['class'] = $detail['class'] ?? 'text-reset';
    }

    // if the heading has a href, target and tile, and the image does not
    // then use those for the image as well
    if ($image['content'] !== null && $heading['content'] !== null) {
        $image['href'] = $image['href'] ?? $heading['href'] ?? null;
        $image['title'] = $image['title'] ?? $heading['title'] ?? null;
        $image['target'] = $image['target'] ?? $heading['target'] ?? null;
    }
@endphp

<div class="row align-items-center bp-chip">
    @if ($image['content'])
        <div class="col-auto">
            <div class="d-block">
                @if ($image['content'])
                    <{{ $image['element'] }}
                        @foreach ($image as $attribute => $value)
                            @if ($attribute !== 'element' && $attribute !== 'content')
                                {{ $attribute }}="{{ $value }}"
                            @endif
                        @endforeach
                    >
                        <span class="avatar avatar-2 rounded" style="background-image: url({{ $image['content'] }})"> </span>
                    </{{ $image['element'] }}>
                @endif
            </div>
        </div>
    @endif
    <div class="col text-truncate">
        <div class="d-block">
            @if ($heading['content'])
                <{{ $heading['element'] }}
                    @foreach ($heading as $attribute => $value)
                        @if ($attribute !== 'element' && $attribute !== 'content')
                            {{ $attribute }}="{{ $value }}"
                        @endif
                    @endforeach
                >
                    {{ $heading['content'] }}
                </{{ $heading['element'] }}>
            @endif
        </div>
        <div class="d-block text-secondary text-truncate mt-n1">
            @foreach ($details as $key => $detail)
                <small class="d-inline-block me-1">
                    @if (isset($detail['icon']))
                    <i class="{{ $detail['icon'] }}" title="{{ $detail['title'] ?? '' }}"></i>
                    @endif
                    <{{ $detail['element'] }}
                        @foreach ($detail as $attribute => $value)
                            @if ($attribute !== 'element' && $attribute !== 'icon' && $attribute !== 'content')
                                {{ $attribute }}="{{ $value }}"
                            @endif
                        @endforeach
                        >
                        {{ $detail['content'] }}
                    </{{ $detail['element'] }}>
                </small>
            @endforeach
        </div>
    </div>
</div>
