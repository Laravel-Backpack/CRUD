{{-- select_from_array column --}}
@php
    $values = data_get($entry, $column['name']);
    $list = [];
    if ($values !== null) {
        if (is_array($values)) {
            foreach ($values as $key => $value) {
                if (! is_null($value)) {
                    $list[$key] = $column['options'][$value] ?? $value;
                }
            }
        } else {
            $value = $column['options'][$values] ?? $values;
            $list[$values] = $value;
        }
    }

    $column['escaped'] = $column['escaped'] ?? true;
@endphp

<span>
    @if(!empty($list))
        @foreach($list as $key => $text)
            @php
                $related_key = $key;
            @endphp

            <span class="d-inline-flex">
                @includeWhen(!empty($column['wrapper']), 'crud::columns.inc.wrapper_start')
                    @if($column['escaped'])
                        {{ $text }}
                    @else
                        {!! $text !!}
                    @endif
                @includeWhen(!empty($column['wrapper']), 'crud::columns.inc.wrapper_end')

                @if(!$loop->last), @endif
            </span>
        @endforeach
    @else
        -
    @endif
</span>
