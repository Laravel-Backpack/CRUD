@php
    // the main text showing in the chip
    $text = $text ?? null;
    $title = $title ?? null;

    // the URL for the main text and image (if any)
    $url = $url ?? null;
    $target = $target ?? "_self";

    // the image (if any)
    $image = $image ?? null;
    $showImage = isset($showImage) ? $showImage : !empty($image);

    // the details that show up on the second row (if any)
    $details = $details ?? [];
@endphp

<div class="row align-items-center bp-chip">
    @if ($showImage)
        <div class="col-auto">
            <div class="d-block">
                @if ($url)
                    <a href="{{ $url }}" title="{{ $title }}" target="{{ $target }}" class="d-inline-block">
                @endif
                @if ($image)
                <span class="avatar avatar-2 rounded" style="background-image: url({{ $image }})"> </span>
                @else
                <span class="avatar avatar-2 rounded bg-secondary text-white">
                    {{ $title ? mb_substr($title, 0, 1, 'UTF-8') : 'A' }}
                </span>
                @endif
                @if ($url)
                    </a>
                @endif
            </div>
        </div>
    @endif
    <div class="col text-truncate">
        <div class="d-block">
            <a @if ($url) href="{{ $url }}" @endif class="mb-1 d-inline-block @if (!$url) text-dark @endif" title="{{ $title }}" target="{{ $target }}">
                {{ $text }}
            </a>
        </div>
        <div class="d-block text-secondary text-truncate mt-n1">
            @foreach ($details as $key => $detail)
                <small class="d-inline-block me-1">
                    <i class="{{ $detail['icon'] }}" title="{{ $detail['title'] ?? '' }}"></i>
                    <a @if (isset($detail['url']) && $detail['url'] != null) href="{{ $detail['url'] }}" @endif
                        class="text-reset"
                        title="{{ $detail['title'] ?? '' }}">{{ $detail['text'] }}</a>
                </small>
            @endforeach
        </div>
    </div>
</div>
