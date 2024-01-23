{{-- checklist --}}
@php
  $key_attribute = (new $field['model'])->getKeyName();
  $field['attribute'] = $field['attribute'] ?? (new $field['model'])->identifiableAttribute();
  $field['number_of_columns'] = $field['number_of_columns'] ?? 3;

  // calculate the checklist options
  if (!isset($field['options'])) {
      $field['options'] = $field['model']::all()->pluck($field['attribute'], $key_attribute)->toArray();
  } else {
      $field['options'] = call_user_func($field['options'], $field['model']::query());
  }

  // calculate the value of the hidden input
  $field['value'] = old_empty_or_null($field['name'], []) ??  $field['value'] ?? $field['default'] ?? [];
  if(!empty($field['value'])) {
      if (is_a($field['value'], \Illuminate\Support\Collection::class)) {
          $field['value'] = ($field['value'])->pluck($key_attribute)->toArray();
      } elseif (is_string($field['value'])){
        $field['value'] = json_decode($field['value']);
      }
  }

  // define the init-function on the wrapper
  $field['wrapper']['data-init-function'] =  $field['wrapper']['data-init-function'] ?? 'bpFieldInitChecklist';
@endphp

@include('crud::fields.inc.wrapper_start')
    <label>{!! $field['label'] !!}</label>
    <div class="row">
        <div class="col-sm-4">
            <label class="font-weight-normal">
                <input type="checkbox"  id="select-all" {{ count($field['options']) == count($field['value']) ? 'checked' : '' }}> 
                <strong> Select all </strong>
            </label>
        </div>
    </div>
    @include('crud::fields.inc.translatable_icon')

    <input type="hidden" value='@json($field['value'])' name="{{ $field['name'] }}">

    <div class="row" id="checkbox-wrapper">
        @foreach ($field['options'] as $key => $option)
            <div class="col-sm-{{ intval(12/$field['number_of_columns']) }}">
                <div class="checkbox">
                  <label class="font-weight-normal">
                    <input type="checkbox" value="{{ $key }}"> {{ $option }}
                  </label>
                </div>
            </div>
        @endforeach
    </div>

    {{-- HINT --}}
    @if (isset($field['hint']))
        <p class="help-block">{!! $field['hint'] !!}</p>
    @endif
@include('crud::fields.inc.wrapper_end')


{{-- ########################################## --}}
{{-- Extra CSS and JS for this particular field --}}
{{-- If a field type is shown multiple times on a form, the CSS and JS will only be loaded once --}}
    {{-- FIELD JS - will be loaded in the after_scripts section --}}
    @push('crud_fields_scripts')
        @bassetBlock('backpack/crud/fields/checklist-field.js')
        <script>
            function bpFieldInitChecklist(element) {
                var hidden_input = element.find('input[type=hidden]');
                var selected_options = JSON.parse(hidden_input.val() || '[]');
                var checkboxes = element.find('#checkbox-wrapper :input[type=checkbox]');
                var container = element.find('.row');

                // set the default checked/unchecked states on checklist options
                checkboxes.each(function(key, option) {
                  var id = $(this).val();

                  if (selected_options.map(String).includes(id)) {
                    $(this).prop('checked', 'checked');
                  } else {
                    $(this).prop('checked', false);
                  }
                });

                // when a checkbox is clicked
                // set the correct value on the hidden input
                checkboxes.click(function() {
                  var newValue = [];

                  checkboxes.each(function() {
                    if ($(this).is(':checked')) {
                      var id = $(this).val();
                      newValue.push(id);
                    }
                  });

                  hidden_input.val(JSON.stringify(newValue)).trigger('change');

                  toggleAllSelectCheckbox();
                });

                hidden_input.on('CrudField:disable', function(e) {
                      checkboxes.attr('disabled', 'disabled');
                  });

                hidden_input.on('CrudField:enable', function(e) {
                    checkboxes.removeAttr('disabled');
                });

            }

            document.getElementById("select-all").addEventListener("click", selectAll);

            function selectAll() {
                var allCheckbox = $('#checkbox-wrapper :input[type=checkbox]');

                if ($("#select-all").is(':checked')) {
                  allCheckbox.each(function(){
                    if ($(this).is(':checked') == false) {
                      $(this).click();
                    }
                  });
                } else {
                  allCheckbox.each(function(){
                    $(this).click();
                  });
                }
            };

            function toggleAllSelectCheckbox() {
              var selectedItems = JSON.parse(document.getElementsByName("{{ $field['name'] }}")['0'].value);
              var allItems = "{{ count($field['options']) }}";

              if(selectedItems.length == allItems) {
                $('#select-all').prop('checked', 'checked');
              } else {
                $('#select-all').prop('checked', false);
              }
            }
        </script>
        @endBassetBlock
    @endpush
{{-- End of Extra CSS and JS --}}
{{-- ########################################## --}}
