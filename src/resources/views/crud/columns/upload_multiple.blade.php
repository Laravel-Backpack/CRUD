@php
    $value = data_get($entry, $column['name']);

    $column['prefix'] = $column['prefix'] ?? '';
    $column['disk'] = $column['disk'] ?? null;
    $column['escaped'] = $column['escaped'] ?? true;
    $column['wrapper']['element'] = $column['wrapper']['element'] ?? 'a';
    $column['wrapper']['target'] = $column['wrapper']['target'] ?? '_blank';
    $column_wrapper_href = $column['wrapper']['href'] ?? function($file_path, $disk, $prefix) { return ( !is_null($disk) ?asset(\Storage::disk($disk)->url($file_path)):asset($prefix.$file_path) ); }
@endphp

<span>
    @if ($value && count($value))
        @foreach ($value as $file_path)
        @php
            $column['wrapper']['href'] = is_callable($column_wrapper_href) ? $column_wrapper_href($file_path, $column['disk'], $column['prefix']) : $column_wrapper_href;
            $text = $column['prefix'].$file_path;
        @endphp
            @includeWhen(!empty($column['wrapper']), 'crud::columns.inc.wrapper_start')
            @if($column['escaped'])
                - {{ $text }} <br/>
            @else
                - {!! $text !!} <br/>
            @endif
        @includeWhen(!empty($column['wrapper']), 'crud::columns.inc.wrapper_end')
        @endforeach
    @else
        -
    @endif
</span>
