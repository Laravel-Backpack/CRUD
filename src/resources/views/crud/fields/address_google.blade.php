<!-- text input -->

<?php

// the field should work whether or not Laravel attribute casting is used
if (isset($field['value']) && (is_array($field['value']) || is_object($field['value']))) {
    $field['value'] = json_encode($field['value']);
}

?>

@include('crud::fields.inc.wrapper_start')
    <label>{!! $field['label'] !!}</label>
    @include('crud::fields.inc.translatable_icon')
    <input type="hidden"
           value="{{ old($field['name']) ? old($field['name']) : (isset($field['value']) ? $field['value'] : (isset($field['default']) ? $field['default'] : '' )) }}"
           name="{{ $field['name'] }}">

    @if(isset($field['prefix']) || isset($field['suffix']))
        <div class="input-group"> @endif
            @if(isset($field['prefix']))
                <div class="input-group-addon">{!! $field['prefix'] !!}</div> @endif
            @if(isset($field['store_as_json']) && $field['store_as_json'])
                <input
                        type="text"
                        data-google-address="{&quot;field&quot;: &quot;{{$field['name']}}&quot;, &quot;full&quot;: {{isset($field['store_as_json']) && $field['store_as_json'] ? 'true' : 'false'}} }"
                        data-init-function="bpFieldInitAddressGoogleElement"
                        @include('crud::fields.inc.attributes')
                >
            @else
                <input
                        type="text"
                        data-google-address="{&quot;field&quot;: &quot;{{$field['name']}}&quot;, &quot;full&quot;: {{isset($field['store_as_json']) && $field['store_as_json'] ? 'true' : 'false'}} }"
                        data-init-function="bpFieldInitAddressGoogleElement"
                        name="{{ $field['name'] }}"
                        value="{{ old($field['name']) ? old($field['name']) : (isset($field['value']) ? $field['value'] : (isset($field['default']) ? $field['default'] : '' )) }}"
                        @include('crud::fields.inc.attributes')
                >
            @endif
            @if(isset($field['suffix']))
                <div class="input-group-addon">{!! $field['suffix'] !!}</div> @endif
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

    {{-- FIELD CSS - will be loaded in the after_styles section --}}
    @push('crud_fields_styles')
    @loadOnce('AddressGoogleCss')
        <style>
            .ap-input-icon.ap-icon-pin {
                right: 5px !important;
            }

            .ap-input-icon.ap-icon-clear {
                right: 10px !important;
            }
        </style>
    @endLoadOnce
    @endpush

    {{-- FIELD JS - will be loaded in the after_scripts section --}}
    @push('crud_fields_scripts')
        @loadOnce('bpFieldInitAddressGoogleElement')
        <script>

            function bpFieldInitAddressGoogleElement(element) {
                var $addressConfig = element.data('google-address');
                var $field = $('[name="' + $addressConfig.field + '"]');

                if ($field.val().length) {
                    var existingData = JSON.parse($field.val());
                    element.val(existingData.value);
                }

                var $autocomplete = new google.maps.places.Autocomplete(
                    (element[0]),
                    {types: ['geocode']});

                $autocomplete.addListener('place_changed', function fillInAddress() {

                    var place = $autocomplete.getPlace();
                    var value = element.val();
                    var latlng = place.geometry.location;
                    var data = {"value": value, "latlng": latlng};

                    for (var i = 0; i < place.address_components.length; i++) {
                        var addressType = place.address_components[i].types[0];
                        data[addressType] = place.address_components[i]['long_name'];
                    }
                    $field.val(JSON.stringify(data));

                });

                element.change(function(){
                    if (!element.val().length) {
                        $field.val("");
                    }
                });
            }

            //Function that will be called by Google Places Library
            function initGoogleAddressAutocomplete() {
                $('[data-google-address]').each(function () {
                    var element = $(this);
                    var functionName = element.data('init-function');

                    if (typeof window[functionName] === "function") {
                      window[functionName](element);
                    }
                });
            }

        </script>
        <script src="https://maps.googleapis.com/maps/api/js?key={{ $field['api_key'] ?? config('services.google_places.key') }}&libraries=places&callback=initGoogleAddressAutocomplete" async defer></script>

    @endLoadOnce
    @endpush

{{-- End of Extra CSS and JS --}}
{{-- ########################################## --}}
