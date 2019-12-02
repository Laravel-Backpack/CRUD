<!-- select2 -->
@php
    $current_value = old($field['name']) ? old($field['name']) : (isset($field['value']) ? $field['value'] : (isset($field['default']) ? $field['default'] : '' ));
@endphp

<div @include('crud::inc.field_wrapper_attributes') >
    <label>{!! $field['label'] !!}</label>
    @include('crud::inc.field_translatable_icon')
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
        @include('crud::inc.field_attributes', ['default_class' =>  'form-control'])
        >

            @if ($entity_model::isColumnNullable($field['name']))
                <option value="">-</option>
            @endif

            @if (isset($field['model']) && isset($field['group_by']))
                @foreach ($categories as $category)
                    <optgroup label="{{ $category->{$field['group_by_attribute']} }}">
                        @foreach ($category->{$field['group_by_relationship_back']} as $subEntry)
                            <option value="{{ $subEntry->getRouteKey() }}"
                                @if ( ( old($field['name']) && old($field['name']) == $subEntry->getRouteKey() ) || (isset($field['value']) && $subEntry->getRouteKey()==$field['value']))
                                     selected
                                @endif
                            >{{ $subEntry->{$field['attribute']} }}</option>
                        @endforeach
                    </optgroup>
                @endforeach

                @if ($categorylessEntries->count())
                    <optgroup label="-">
                        @foreach ($categorylessEntries as $subEntry)

                            @if($current_value == $subEntry->getRouteKey())
                                <option value="{{ $subEntry->getRouteKey() }}" selected>{{ $subEntry->{$field['attribute']} }}</option>
                            @else
                                <option value="{{ $subEntry->getRouteKey() }}">{{ $subEntry->{$field['attribute']} }}</option>
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
</div>