<!-- Phone verification and formatting -->
<div @include('crud::inc.field_wrapper_attributes') >
    <label>{!! $field['label'] !!}</label>
    @include('crud::inc.field_translatable_icon')

    <input
        type="tel"
        id="{{ $field['name'] }}_picker"
        value="{{ old($field['name']) ? old($field['name']) : (isset($field['value']) ? $field['value'] : (isset($field['default']) ? $field['default'] : '' )) }}"
        @include('crud::inc.field_attributes')
    >
    <input type="hidden" name="{{ $field['name'] }}" value="{{ old($field['name']) ? old($field['name']) : (isset($field['value']) ? $field['value'] : (isset($field['default']) ? $field['default'] : '' )) }}">

    {{-- HINT --}}
    @if (isset($field['hint']))
        <p class="help-block">{!! $field['hint'] !!}</p>
    @endif
</div>

{{-- ########################################## --}}
{{-- Extra CSS and JS for this particular field --}}
{{-- If a field type is shown multiple times on a form, the CSS and JS will only be loaded once --}}
@if ($crud->checkIfFieldIsFirstOfItsType($field, $fields))

    {{-- FIELD CSS - will be loaded in the after_styles section --}}
    @push('crud_fields_styles')
        <link rel="stylesheet" href="{{ asset('vendor/backpack/intl-tel-input/css/intlTelInput.css') }}">
        <style>
          .intl-tel-input {
            display: flex;
            width: 100%;
          }
        </style>
    @endpush

    {{-- FIELD JS - will be loaded in the after_scripts section --}}
    @push('crud_fields_scripts')
      <script src="{{ asset('vendor/backpack/intl-tel-input/js/intlTelInput.min.js') }}"></script>
    @endpush

@endif

@push('crud_fields_scripts')
<script type="text/javascript">
    jQuery('document').ready(function($){
      // https://github.com/jackocnr/intl-tel-input
      var input = $('[id="{{ $field['name'] }}_picker"]');
      var output = $('[name="{{ $field['name'] }}"]');

      input.intlTelInput({
          initialCountry: "auto",
          separateDialCode: true,
          nationalMode: true,
          autoHideDialCode: false,
          hiddenInput: "{{ $field['name'] }}",
          geoIpLookup: function(callback) {
            $.get('https://ipinfo.io', function() {}, "jsonp").always(function(resp) {
              var countryCode = (resp && resp.country) ? resp.country : "";
              callback(countryCode);
            });
          },
          utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/12.1.15/js/utils.js"
        });

      input.on("keyup change", function() {
          var intlNumber = input.intlTelInput("getNumber");

          if (intlNumber) {
            output.val(intlNumber);
          }
        });
    });
</script>
@endpush

{{-- End of Extra CSS and JS --}}
{{-- ########################################## --}}
