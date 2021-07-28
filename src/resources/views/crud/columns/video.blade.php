{{-- regular object attribute --}}
@php
    $value = data_get($entry, $column['name']);

    if( !empty($value) ) {

        // if attribute casting is used, convert to object
        if (is_array($value)) {
            $video = (object)$value;
        } elseif (is_string($value)) {
            $video = json_decode($value);
        } else {
            $video = $value;
        }
        $bgColor = $video->provider == 'vimeo' ? '#00ADEF' : '#DA2724';
    }
@endphp

<span>
    @if( isset($video) )
    <a target="_blank" href="{{$video->url}}" title="{{$video->title}}" style="background: {{$bgColor}}; color: #fff; display: inline-block; width: 30px; height: 25px; text-align: center; border-top-left-radius: 3px; border-bottom-left-radius: 3px; transform: translateY(1px);">
        <i class="la la-{{$video->provider}}" style="transform: translateY(2px);"></i>
    </a><img src="{{$video->image}}" alt="{{$video->title}}" style="height: 25px; border-top-right-radius: 3px; border-bottom-right-radius: 3px;" />
    @else
    -
    @endif
</span>
