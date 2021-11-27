{{-- radio --}}
@php
    $column['key'] = $column['key'] ?? $column['name'];
    $column['value'] = $column['value'] ?? data_get($entry, $column['key']);
    $column['escaped'] = $column['escaped'] ?? true;
    $column['prefix'] = $column['prefix'] ?? '';
    $column['suffix'] = $column['suffix'] ?? '';
    $column['text'] = $column['default'] ?? '-';

    if(is_callable($column['value'])) {
        $column['value'] = $column['value']($entry);
    }

    if(!empty($column['value'])) {
        $column['value'] = $column['options'][$column['value']] ?? '';
        $column['text'] = $column['prefix'].$column['value'].$column['suffix'];
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
