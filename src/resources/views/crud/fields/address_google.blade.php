{{-- address google

    This field allows you to present your user with google places auto-complete address.

    Options:
        - store_as_json - true/false - If true stores the places object, if false stores the selected address string

--}}

<?php

    // the field should work whether or not Laravel attribute casting is used
    if (isset($field['value']) && (is_array($field['value']) || is_object($field['value']))) {
        $field['value'] = json_encode($field['value']);
    }

    $field['store_as_json'] = $field['store_as_json'] ?? false;

?>

@include('crud::fields.inc.wrapper_start')
    <label>{!! $field['label'] !!}</label>
    @include('crud::fields.inc.translatable_icon')
    <input type="hidden"
           value="{{ oldValueDefaultOrFallback($field, '') }}"
           name="{{ $field['name'] }}">

    @if(isset($field['prefix']) || isset($field['suffix']))
        <div class="input-group"> @endif
            @if(isset($field['prefix']))
                <div class="input-group-addon">{!! $field['prefix'] !!}</div> @endif
                <input
                        type="text"
                        data-google-address="{&quot;field&quot;: &quot;{{$field['name']}}&quot;, &quot;full&quot;: {{isset($field['store_as_json']) && $field['store_as_json'] ? 'true' : 'false'}} }"
                        data-init-function="bpFieldInitAddressGoogleElement"
                        data-store-as-json="{{ isset($field['store_as_json']) && $field['store_as_json'] ? 'true' : 'false' }}"
                        @include('crud::fields.inc.attributes')
                >
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
@if ($crud->fieldTypeNotLoaded($field))
    @php
        $crud->markFieldTypeAsLoaded($field);
    @endphp

    {{-- FIELD CSS - will be loaded in the after_styles section --}}
    @push('crud_fields_styles')
        <style>
            .ap-input-icon.ap-icon-pin {
                right: 5px !important;
            }

            .ap-input-icon.ap-icon-clear {
                right: 10px !important;
            }

            .pac-container {
                z-index: 1051;
            }
        </style>
    @endpush

    {{-- FIELD JS - will be loaded in the after_scripts section --}}
    @push('crud_fields_scripts')
        <script>

            function bpFieldInitAddressGoogleElement(element) {

                //this script is async loaded so it does not prevent other scripts in page to load while this is fetched from outside url.
                //at somepoint our initialization script might run before the script is on page throwing undesired errors.
                //this makes sure that when this script is run, it has google available either on our field initialization or when the callback function is called.
                if(typeof google === "undefined") { return; }

                var $addressConfig = element.data('google-address');
                var $field = $('[name="' + $addressConfig.field + '"]');
                var $storeAsJson = element.data('store-as-json');

                if ($field.val().length) {
                    try {
                        var existingData = JSON.parse($field.val());
                        element.val(existingData.value);
                    } catch(error) {
                        element.val($field.val());
                    }
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

                    if($storeAsJson) {
                        $field.val(JSON.stringify(data));
                    } else {
                        $field.val(value);
                    }

                });

                element.change(function(){
                    if(!$storeAsJson) {
                        $field.val(element.val());
                    } else {
                        if (!element.val().length) {
                            $field.val("");
                        }
                    }
                });

                // Make sure pac container is closed on modals (inline create)
                let modal = document.querySelector('.modal-dialog');
                if(modal) modal.addEventListener('click', e => document.querySelector('.pac-container').style.display = "none");
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
        <script src="https://maps.googleapis.com/maps/api/js?v=3&key={{ $field['api_key'] ?? config('services.google_places.key') }}&libraries=places&callback=initGoogleAddressAutocomplete" async defer></script>

    @endpush

@endif
{{-- End of Extra CSS and JS --}}
{{-- ########################################## --}}
