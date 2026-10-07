{{-- checklist --}}
@php
  $key_attribute = (new $field['model'])->getKeyName();
  $field['attribute'] ??= (new $field['model'])->identifiableAttribute();
  $field['number_of_columns'] ??= 3;
  $field['show_select_all'] ??= false;

  // calculate the checklist options
  if (!isset($field['options'])) {
      $field['options'] = $field['model']::all()->pluck($field['attribute'], $key_attribute)->toArray();
  } else {
      $field['options'] = call_user_func($field['options'], $field['model']::query());

      if(is_a($field['options'], \Illuminate\Contracts\Database\Query\Builder::class, true)) {
          $field['options'] = $field['options']->pluck($field['attribute'], $key_attribute)->toArray();
      }
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
  $field['wrapper']['data-init-function'] ??= 'bpFieldInitChecklist';
@endphp

@include('crud::fields.inc.wrapper_start')

    <label>{!! $field['label'] !!}

    @if($field['show_select_all'] ?? false)
    <span class="fs-6 small checklist-select-all-inputs">
        <a href="javascript:void(0)" href="#" class="select-all-inputs">{{trans('backpack::crud.select_all')}}</a>
        <a href="javascript:void(0)" href="#" class="unselect-all-inputs d-none">{{trans('backpack::crud.unselect_all')}}</a> 
    </span>
    @endif
    </label>
    
    @include('crud::fields.inc.translatable_icon')

    <input type="hidden" data-show-select-all="{{var_export($field['show_select_all'])}}" value='@json($field['value'])' name="{{ $field['name'] }}">

    <div class="row checklist-options-container">
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
                let wrapper = element[0];
                let hidden_input = wrapper.querySelector('input[type=hidden]');
                let selected_options = JSON.parse(hidden_input.value || '[]');
                let container = wrapper.querySelector('.row.checklist-options-container');
                let checkboxes = container.querySelectorAll('input[type=checkbox]');
                let showSelectAll = hidden_input.dataset.showSelectAll === 'true';
                let selectAllAnchor = wrapper.querySelector('.checklist-select-all-inputs a.select-all-inputs');
                let unselectAllAnchor = wrapper.querySelector('.checklist-select-all-inputs a.unselect-all-inputs');

                // set the default checked/unchecked states on checklist options
                checkboxes.forEach(function(option) {
                  var id = option.value;

                  if (selected_options.map(String).includes(id)) {
                    option.checked = true;
                  } else {
                    option.checked = false;
                  }
                });

                // when a checkbox is clicked
                // set the correct value on the hidden input
                checkboxes.forEach(function(checkbox) {
                    checkbox.addEventListener('click', function() {
                        var newValue = [];

                        checkboxes.forEach(function(cb) {
                            if (cb.checked) {
                                newValue.push(cb.value);
                            }
                        });

                        hidden_input.value = JSON.stringify(newValue);
                        hidden_input.dispatchEvent(new Event('change'));

                        toggleAllSelectAnchor();
                    });
                });
                  
                let selectAll = function() {
                  checkboxes.forEach(function(cb) { cb.checked = true; });
                  hidden_input.value = JSON.stringify(Array.from(checkboxes).map(function(cb) { return cb.value; }));
                  hidden_input.dispatchEvent(new Event('change'));
                  selectAllAnchor.classList.toggle('d-none');
                  unselectAllAnchor.classList.toggle('d-none');
                };

                let unselectAll = function() {
                  checkboxes.forEach(function(cb) { cb.checked = false; });
                  hidden_input.value = JSON.stringify([]);
                  hidden_input.dispatchEvent(new Event('change'));
                  selectAllAnchor.classList.toggle('d-none');
                  unselectAllAnchor.classList.toggle('d-none');
                };

                let toggleAllSelectAnchor = function() {
                  if(showSelectAll === false) {
                    return;
                  }

                  if (checkboxes.length === selected_options.length) {
                    selectAllAnchor.classList.toggle('d-none');
                    unselectAllAnchor.classList.toggle('d-none');
                  }
                };

                if(showSelectAll) {
                  selectAllAnchor.addEventListener('click', selectAll);
                  unselectAllAnchor.addEventListener('click', unselectAll);

                  toggleAllSelectAnchor();
                }

                hidden_input.addEventListener('CrudField:disable', function(e) {
                    checkboxes.forEach(function(cb) { cb.setAttribute('disabled', 'disabled'); });
                });

                hidden_input.addEventListener('CrudField:enable', function(e) {
                    checkboxes.forEach(function(cb) { cb.removeAttribute('disabled'); });
                });

            }
        </script>
        @endBassetBlock
    @endpush
{{-- End of Extra CSS and JS --}}
{{-- ########################################## --}}
