<!-- select2 -->
@php
    $current_value = oldValueDefaultOrFallback($field, '');
    $field['allows_null'] = $field['allows_null'] ?? $crud->model::isColumnNullable($field['name']);
@endphp

@include('crud::fields.inc.wrapper_start')
    <label>{!! $field['label'] !!}</label>
    @include('crud::fields.inc.translatable_icon')
    @php
        $entity_model = $crud->model;
        $related_model = $crud->getRelationModel($field['entity']);
        $group_by_model = (new $related_model)->{$field['group_by']}()->getRelated();
        $categories = $group_by_model::with($field['group_by_relationship_back'])->get();

        if (isset($field['model'])) {
            $categorylessEntries = $related_model::doesnthave($field['group_by'])->get();
        }
    @endphp
    <select
        name="{{ $field['name'] }}"
        style="width: 100%"
        @include('crud::fields.inc.attributes', ['default_class' =>  'form-control'])
        >

            @if ($field['allows_null'])
                <option value="">-</option>
            @endif

            @if (isset($field['model']) && isset($field['group_by']))
                @foreach ($categories as $category)
                    <optgroup label="{{ $category->{$field['group_by_attribute']} }}">
                        @foreach ($category->{$field['group_by_relationship_back']} as $subEntry)
                            <option value="{{ $subEntry->getKey() }}"
                                @if ( ( old($field['name']) && old($field['name']) == $subEntry->getKey() ) || (isset($field['value']) && $subEntry->getKey()==$field['value']))
                                     selected
                                @endif
                            >{{ $subEntry->{$field['attribute']} }}</option>
                        @endforeach
                    </optgroup>
                @endforeach

                @if ($categorylessEntries->count())
                    <optgroup label="-">
                        @foreach ($categorylessEntries as $subEntry)

                            @if($current_value == $subEntry->getKey())
                                <option value="{{ $subEntry->getKey() }}" selected>{{ $subEntry->{$field['attribute']} }}</option>
                            @else
                                <option value="{{ $subEntry->getKey() }}">{{ $subEntry->{$field['attribute']} }}</option>
                            @endif
                        @endforeach
                    </optgroup>
                @endif
            @endif
    </select>

    {{-- HINT --}}
    @if (isset($field['hint']))
        <p class="help-block">{!! $field['hint'] !!}</p>
    @endif
@include('crud::fields.inc.wrapper_end')
