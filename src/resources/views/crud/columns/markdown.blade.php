@php
    $value = Illuminate\Mail\Markdown::parse($entry->{$column['name']} ?? '');

    $column['escaped'] = $column['escaped'] ?? false;
    $column['prefix'] = $column['prefix'] ?? '';
    $column['suffix'] = $column['suffix'] ?? '';
    $column['text'] = '-';

    if(!empty($value)) {
        $column['text'] = $column['prefix'].$value.$column['suffix'];
    }
@endphp

@includeWhen(!empty($column['wrapper']), 'crud::columns.inc.wrapper_start')
    @if($column['escaped'])
        {{ $column['text'] }}
    @else
        {!! $column['text'] !!}
    @endif
@includeWhen(!empty($column['wrapper']), 'crud::columns.inc.wrapper_end')

