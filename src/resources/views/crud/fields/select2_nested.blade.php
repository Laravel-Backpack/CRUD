<!-- select2_nested -->

{{-- Thanks to Erwan Pianezza - https://github.com/breizhwave--}}

{{-- This field assumes you have a nested set Eloquent model, using: --}}
{{-- 1. children() as a properly defined relationship --}}
{{-- 2. depth, lft attributes --}}

@php
    $current_value = oldValueDefaultOrFallback($field, '');

    if (!function_exists('echoSelect2NestedEntry')) {
        function echoSelect2NestedEntry($entry, $field, $current_value) {
            if ($current_value == $entry->getKey()) {
                $selected = ' selected ';
            } else {
                $selected = '';
            }
            echo '<option value="'.$entry->getKey().'"'.$selected.'>';
            echo str_repeat("-", (int)$entry->depth > 1 ? (int)$entry->depth - 1 : 0).' '.$entry->{$field['attribute']};
            echo "</option>";
        }
    }

    if (!function_exists('echoSelect2NestedChildren')) {
        function echoSelect2NestedChildren($entity, $field, $current_value)
        {
         foreach ($entity->children()->get() as $entry)
            {
                echoSelect2NestedEntry($entry, $field, $current_value);
                echoSelect2NestedChildren($entry, $field, $current_value);
            }
        }
    }

    $entity_model = $crud->getRelationModel($field['entity'], -1);

    $field['allows_null'] = $field['allows_null'] ?? $entity_model::isColumnNullable($field['name']);
@endphp

@include('crud::fields.inc.wrapper_start')
    <label>{!! $field['label'] !!}</label>
    @include('crud::fields.inc.translatable_icon')

    <select
        name="{{ $field['name'] }}"
        style="width: 100%"
        data-init-function="bpFieldInitSelect2NestedElement"
        data-language="{{ str_replace('_', '-', app()->getLocale()) }}"
        @include('crud::fields.inc.attributes', ['default_class' =>  'form-control select2_field'])
        >

        @if ($field['allows_null'])
            <option value="">-</option>
        @endif

        @if (isset($field['model']))
            @php
                $obj = new $field['model'];
                $first_level_items = $obj->where('depth', '1')->orWhere('depth', null)->orderBy('lft', 'ASC')->get();
            @endphp

            @foreach ($first_level_items as $connected_entity_entry)
                @php
                    echoSelect2NestedEntry($connected_entity_entry, $field, $current_value);
                    echoSelect2NestedChildren($connected_entity_entry, $field, $current_value);
                @endphp
            @endforeach
        @endif
    </select>

    {{-- HINT --}}
    @if (isset($field['hint']))
        <p class="help-block">{!! $field['hint'] !!}</p>
    @endif
@include('crud::fields.inc.wrapper_end')

{{-- ########################################## --}}
{{-- Extra CSS and JS for this particular field --}}
{{-- If a field type is shown multiple times on a form, the CSS and JS will only be loaded once --}}
@if ($crud->fieldTypeNotLoaded($field))
    @php
        $crud->markFieldTypeAsLoaded($field);
    @endphp

    {{-- FIELD CSS - will be loaded in the after_styles section --}}
    @push('crud_fields_styles')
        <!-- include select2 css-->
        <link href="{{ asset('packages/select2/dist/css/select2.min.css') }}" rel="stylesheet" type="text/css" />
        <link href="{{ asset('packages/select2-bootstrap-theme/dist/select2-bootstrap.min.css') }}" rel="stylesheet" type="text/css" />
    @endpush

    {{-- FIELD JS - will be loaded in the after_scripts section --}}
    @push('crud_fields_scripts')
        <!-- include select2 js-->
        <script src="{{ asset('packages/select2/dist/js/select2.full.min.js') }}"></script>
        @if (app()->getLocale() !== 'en')
        <script src="{{ asset('packages/select2/dist/js/i18n/' . str_replace('_', '-', app()->getLocale()) . '.js') }}"></script>
        @endif
        <script>
            function bpFieldInitSelect2NestedElement(element) {
                if (!element.hasClass("select2-hidden-accessible"))
                {
                    element.select2({
                        theme: "bootstrap"
                    });
                }
            }
        </script>
    @endpush

@endif
{{-- End of Extra CSS and JS --}}
{{-- ########################################## --}}
