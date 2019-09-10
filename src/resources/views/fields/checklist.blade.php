<!-- checklist -->
<div @include('crud::inc.field_wrapper_attributes') >
    <label>{!! $field['label'] !!}</label>
    @if (isset($field['batch']) && $field['batch'] === true)
        <div class="select-all js-select-all">{{ trans('backpack::crud.select_all') }}</div>
    @endif
    @include('crud::inc.field_translatable_icon')
    <?php $entity_model = $crud->getModel(); ?>

    <div class="row">
        @foreach ($field['model']::all() as $connected_entity_entry)
            <div class="col-sm-4">
                <div class="checkbox">
                  <label>
                    <input type="checkbox"
                      name="{{ $field['name'] }}[]"
                      value="{{ $connected_entity_entry->getKey() }}"

                      @if( ( old( $field["name"] ) && in_array($connected_entity_entry->getKey(), old( $field["name"])) ) || (isset($field['value']) && in_array($connected_entity_entry->getKey(), $field['value']->pluck($connected_entity_entry->getKeyName(), $connected_entity_entry->getKeyName())->toArray())))
                             checked = "checked"
                      @endif > {!! $connected_entity_entry->{$field['attribute']} !!}
                  </label>
                </div>
            </div>
        @endforeach
    </div>

    {{-- HINT --}}
    @if (isset($field['hint']))
        <p class="help-block">{!! $field['hint'] !!}</p>
    @endif
</div>

{{-- ########################################## --}}
{{-- Extra CSS and JS for this particular field --}}
{{-- If a field type is shown multiple times on a form, the CSS and JS will only be loaded once --}}
@if ($crud->checkIfFieldIsFirstOfItsType($field, $fields))

    @push('crud_fields_styles')
        <style>
            .select-all {
                cursor: pointer;
                text-decoration: underline;
            }
        </style>
    @endpush

    {{-- FIELD JS - will be loaded in the after_scripts section --}}
    @push('crud_fields_scripts')
        <!-- include checklist js -->
        <script>
            jQuery(document).ready(function($) {

                $('.js-select-all').bind('click', function(event) {
                    event.preventDefault();
                    var checkboxesContainer = $(this).next('.row');
                    var checkboxes = checkboxesContainer.find('input[type="checkbox"]');

                    // Treat everything as unchecked by default (first action will be checking)
                    var checked = false;

                    // But if everything is checked, first action is unchecking
                    if (checkboxesContainer.find('input[type="checkbox"]:checked').length == checkboxes.length) {
                        checked = true;
                    }

                    checkboxes.each(function() {
                        $(this).prop('checked', !checked);
                    })
                });
            });
        </script>
    @endpush

@endif
{{-- End of Extra CSS and JS --}}
{{-- ########################################## --}}
