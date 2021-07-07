{{-- enumerate the values in an array  --}}
@php
    $array = data_get($entry, $column['name']);

    $column['escaped'] = $column['escaped'] ?? false;
    $column['prefix'] = $column['prefix'] ?? '';
    $column['suffix'] = $column['suffix'] ?? ' items';
    $column['text'] = '-';

    // the value should be an array wether or not attribute casting is used
    if (! is_array($array)) {
        $array = json_decode($array, true);
    }

    if($array && count($array)) {
        $column['text'] = $column['prefix'].count($array).$column['suffix'];
    }
@endphp

<span>
    @includeWhen(!empty($column['wrapper']), 'crud::columns.inc.wrapper_start')
        @if($column['escaped'])
            {{ $column['text'] }}
        @else
            {!! $column['text'] !!}
        @endif
    @includeWhen(!empty($column['wrapper']), 'crud::columns.inc.wrapper_end')
</span>
