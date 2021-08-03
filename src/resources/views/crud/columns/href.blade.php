@php
    $value = data_get($entry, $column['key']);
    $column['text'] = $column['text'] ?? $value;
@endphp
<span>
    @includeWhen(!empty($column['wrapper']), 'crud::columns.inc.wrapper_start')
    <a href=" {!! $value !!}">{{$column['text']}}</a>
    @includeWhen(!empty($column['wrapper']), 'crud::columns.inc.wrapper_end')
</span>
