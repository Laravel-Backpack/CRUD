<!-- address_algolia input -->

<?php
    $field['store_as_json'] = $field['store_as_json'] ?? false;

    $field['wrapper'] = $field['wrapper'] ?? $field['wrapperAttributes'] ?? [];
    $field['wrapper']['algolia-wrapper'] = $field['wrapper']['algolia-wrapper'] ?? 'true';
    $field['config'] = [
        'field' => $field['name'],
        'full' => $field['store_as_json'],
    ];
    $field['value'] = oldValueDefaultOrFallback($field, '');

    // the field should work whether or not Laravel attribute casting is used
    if (isset($field['value']) && (is_array($field['value']) || is_object($field['value']))) {
        $field['value'] = json_encode($field['value']);
    }
?>

@include('crud::fields.inc.wrapper_start')
    <label>{!! $field['label'] !!}</label>

    @include('crud::fields.inc.translatable_icon')

    @if($field['store_as_json'])
    <input type="hidden"
        value='{{ $field['value'] }}'
        name="{{ $field['name'] }}"
        data-algolia-hidden-input="{{ $field['name'] }}">
    @endif

    @if(isset($field['prefix']) || isset($field['suffix'])) <div class="input-group"> @endif
    @if(isset($field['prefix'])) <div class="input-group-addon">{!! $field['prefix'] !!}</div> @endif

    <input
        type="text"
        data-config='@json((object)$field['config'])'
        data-init-function="bpFieldInitAddressAlgoliaElement"
        @if(!$field['store_as_json'])
        name="{{ $field['name'] }}"
        value="{{ $field['value'] }}"
        @endif
        @include('crud::fields.inc.attributes')
    >

    @if(isset($field['suffix'])) <div class="input-group-addon">{!! $field['suffix'] !!}</div> @endif
    @if(isset($field['prefix']) || isset($field['suffix'])) </div> @endif

    {{-- HINT --}}
    @if (isset($field['hint']))
        <p class="help-block">{!! $field['hint'] !!}</p>
    @endif
@include('crud::fields.inc.wrapper_end')

{{-- Note: you can use  to only load some CSS/JS once, even though there are multiple instances of it --}}

{{-- ########################################## --}}
{{-- Extra CSS and JS for this particular field --}}
{{-- If a field type is shown multiple times on a form, the CSS and JS will only be loaded once --}}
@if ($crud->fieldTypeNotLoaded($field))
    @php
        $crud->markFieldTypeAsLoaded($field);
    @endphp

    {{-- FIELD CSS - will be loaded in the after_styles section --}}
    @push('crud_fields_styles')
        <style>
            .ap-input-icon.ap-icon-pin {
                right: 5px !important; }
            .ap-input-icon.ap-icon-clear {
                right: 10px !important; }
        </style>
    @endpush

    {{-- FIELD JS - will be loaded in the after_scripts section --}}
    @push('crud_fields_scripts')
    <script src="{{ asset('packages/places.js/dist/cdn/places.min.js') }}"></script>
    <script>
            window.AlgoliaPlaces = window.AlgoliaPlaces || {};

            function bpFieldInitAddressAlgoliaElement(element) {
                $addressConfig = element.data('config');
                $hiddenInput = element.parent("[algolia-wrapper]").find('input[type=hidden]');
                $place = places({ container: element[0] });

                // set id to something unique
                $randomNumber = Math.round(Math.random() * 1000000000);
                element.attr('id', 'algolia_input_'+$randomNumber);

                function clearInput() {
                    if( !element.val().length ){
                        $hiddenInput.val('');
                    }
                }

                if( $addressConfig.full ){

                    $place.on('change', function(e){
                        var result = JSON.parse(JSON.stringify(e.suggestion));
                        delete(result.highlight); delete(result.hit); delete(result.hitIndex);
                        delete(result.rawAnswer); delete(result.query);
                        $hiddenInput.val( JSON.stringify(result) );
                    });

                    element.on('change blur', clearInput);
                    $place.on('clear', clearInput);

                    if( $hiddenInput.val().length ){
                        var existingData = JSON.parse($hiddenInput.val());
                        element.val(existingData.value);
                    }
                }

                window.AlgoliaPlaces[ element.attr('id') ] = $place;
            }
    </script>
    @endpush

@endif
{{-- End of Extra CSS and JS --}}
{{-- ########################################## --}}
