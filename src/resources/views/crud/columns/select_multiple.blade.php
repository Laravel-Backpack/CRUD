{{-- relationships with pivot table (n-n) --}}
@php
    $column['escaped'] = $column['escaped'] ?? true;
    $column['prefix'] = $column['prefix'] ?? '';
    $column['suffix'] = $column['suffix'] ?? '';
    $column['limit'] = $column['limit'] ?? 40;
    $column['attribute'] = $column['attribute'] ?? (new $column['model'])->identifiableAttribute();


    $results = data_get($entry, $column['name']);
    $results_array = [];

    if($results !== null && !$results->isEmpty()) {
        $related_key = $results->first()->getKeyName();
        $results_array = $results->pluck($column['attribute'], $related_key)->toArray();
    }

    foreach ($results_array as $key => $text) {
        $results_array[$key] = Str::limit($text, $column['limit'], '[...]');
    }
@endphp

<span>
    @if(!empty($results_array))
        {{ $column['prefix'] }}
        @foreach($results_array as $key => $text)
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
        {{ $column['suffix'] }}
    @else
        -
    @endif
</span>
