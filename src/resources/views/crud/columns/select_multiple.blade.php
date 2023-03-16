{{-- relationships with pivot table (n-n) --}}
@php
    $column['value'] = $column['value'] ?? data_get($entry, $column['name'], []);
    $column['escaped'] = $column['escaped'] ?? true;
    $column['prefix'] = $column['prefix'] ?? '';
    $column['suffix'] = $column['suffix'] ?? '';
    $column['limit'] = $column['limit'] ?? 32;
    $column['attribute'] = $column['attribute'] ?? (new $column['model'])->identifiableAttribute();

    if($column['value'] instanceof \Closure) {
        $column['value'] = $column['value']($entry);
    }

    if($column['value'] !== null && !$column['value']->isEmpty()) {
        $column['value'] = $column['value']->lazy()->mapWithKeys(function($relatedModel) use ($column, $crud) {
            if (method_exists($relatedModel, 'translationEnabled') && $relatedModel->translationEnabled()) {
                $locale = $crud->getRequest()->input('_locale', app()->getLocale());
                if (in_array($locale, array_keys($relatedModel->getAvailableLocales()))) {
                    $relatedModel->setLocale($locale);
                }
            }
            return [$relatedModel->getKey() => Str::limit($relatedModel->{$column['attribute']}, $column['limit'], '…')];
        });
    }

    $column['value'] = $column['value']->toArray();
@endphp

<span>
    @if(!empty($column['value']))
        {{ $column['prefix'] }}
        @foreach($column['value'] as $key => $text)
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
        {{ $column['default'] ?? '-' }}
    @endif
</span>
