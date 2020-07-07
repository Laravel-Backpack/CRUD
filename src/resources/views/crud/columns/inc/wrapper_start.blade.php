@php
    // this is made available by columns like select and select_multiple
    $related_key = $related_key ?? null;

    // each wrapper attribute can be a callback or a string
    // for those that are callbacks, run the callbacks to get the final string to use
    foreach($column['wrapper'] as $attribute => $value) {
        $column['wrapper'][$attribute] = !is_string($value) && is_callable($value) ? $value($crud, $column, $entry, $related_key) : $value ?? '';
    }
@endphp

<{{ $column['wrapper']['element'] ?? 'a' }}
@foreach(Arr::where($column['wrapper'],function($value, $key) { return $key != 'element'; }) as $element => $value)
    {{$element}}="{{$value}}"
@endforeach
>