{{-- REPEATABLE FIELD TYPE --}}

@php
  $field['value'] = old($field['name']) ? old($field['name']) : (isset($field['value']) ? $field['value'] : (isset($field['default']) ? $field['default'] : '' ));
  // make sure the value is a JSON string (not array, if it's cast in the model)
  $field['value'] = is_array($field['value']) ? json_encode($field['value']) : $field['value'];
@endphp

@include('crud::fields.inc.wrapper_start')
  <label>{!! $field['label'] !!}</label>
  @include('crud::fields.inc.translatable_icon')
  <input
      type="hidden"
      name="{{ $field['name'] }}"
      data-init-function="bpFieldInitRepeatableElement"
      value="{{ $field['value'] }}"
      @include('crud::fields.inc.attributes')
  >

  {{-- HINT --}}
  @if (isset($field['hint']))
    <small class="text-muted d-block">{!! $field['hint'] !!}</small>
  @endif



<div class="container-repeatable-elements">
    <div data-repeatable-holder="{{ $field['name'] }}"></div>

    @push('before_scripts')
    <div class="col-md-12 well repeatable-element row m-1 p-2" data-repeatable-identifier="{{ $field['name'] }}">
      @if (isset($field['fields']) && is_array($field['fields']) && count($field['fields']))
        <button type="button" class="close delete-element"><span aria-hidden="true">×</span></button>
        @foreach($field['fields'] as $subfield)
          @php
              $subfield = $crud->makeSureFieldHasNecessaryAttributes($subfield);
              $fieldViewNamespace = $subfield['view_namespace'] ?? 'crud::fields';
              $fieldViewPath = $fieldViewNamespace.'.'.$subfield['type'];
              $subfield['showAsterisk'] = false;
          @endphp

          @include($fieldViewPath, ['field' => $subfield])
        @endforeach

      @endif
    </div>
    @endpush

  </div>


  <button type="button" class="btn btn-outline-primary btn-sm ml-1 add-repeatable-element-button">+ {{ $field['new_item_label'] ?? trans('backpack::crud.new_item') }}</button>

@include('crud::fields.inc.wrapper_end')

@if ($crud->fieldTypeNotLoaded($field))
  @php
      $crud->markFieldTypeAsLoaded($field);
  @endphp
  {{-- FIELD EXTRA CSS  --}}
  {{-- push things in the after_styles section --}}

  @push('crud_fields_styles')
      <!-- no styles -->
      <style type="text/css">
        .repeatable-element {
          border: 1px solid rgba(0,40,100,.12);
          border-radius: 5px;
          background-color: #f0f3f94f;
        }
        .container-repeatable-elements .delete-element {
          z-index: 2;
          position: absolute!important;
          margin-left: -24px;
          margin-top: 0px;
          height: 30px;
          width: 30px;
          border-radius: 15px;
          text-align: center;
          background-color: #e8ebf0!important;;
        }
      </style>
  @endpush

  {{-- FIELD EXTRA JS --}}
  {{-- push things in the after_scripts section --}}

  @push('crud_fields_scripts')
      <script>
        /**
         * Takes all inputs and makes them an object.
         */
        function repeatableInputToObj(container_name) {
            var arr = [];
            var obj = {};

            var container = $('[data-repeatable-holder='+container_name+']');

            container.find('.well').each(function () {
                $(this).find('input, select, textarea').each(function () {
                    if ($(this).data('repeatable-input-name')) {
                        obj[$(this).data('repeatable-input-name')] = $(this).val();
                    }
                });
                arr.push(obj);
                obj = {};
            });

            return arr;
        }

        /**
         * The method that initializes the javascript on this field type.
         */
        function bpFieldInitRepeatableElement(element) {

            var field_name = element.attr('name');

            // element will be a jQuery wrapped DOM node
            var container = $('[data-repeatable-identifier='+field_name+']');

            // make sure the inputs no longer have a "name" attribute,
            // so that the form will not send the inputs as request variables;
            // use a "data-repeatable-input-name" attribute to store the same information;
            container.find('input, select, textarea')
                    .each(function(){
                        if ($(this).data('name')) {
                            var name_attr = $(this).data('name');
                            $(this).removeAttr("data-name");
                        } else if ($(this).attr('name')) {
                            var name_attr = $(this).attr('name');
                            $(this).removeAttr("name");
                        }
                        $(this).attr('data-repeatable-input-name', name_attr)
                    });

            // make a copy of the group of inputs in their default state
            // this way we have a clean element we can clone when the user
            // wants to add a new group of inputs
            var field_group_clone = container.clone();
            container.remove();

            element.parent().find('.add-repeatable-element-button').click(function(){
                newRepeatableElement(container, field_group_clone);
            });

            if (element.val()) {
                var repeatable_fields_values = JSON.parse(element.val());

                for (var i = 0; i < repeatable_fields_values.length; ++i) {
                    newRepeatableElement(container, field_group_clone, repeatable_fields_values[i]);
                }
            } else {
                element.parent().find('.add-repeatable-element-button').trigger('click');
            }

            if (element.closest('.modal-content').length) {
                element.closest('.modal-content').find('.save-block').click(function(){
                    element.val(JSON.stringify(repeatableInputToObj(field_name)));
                })
            } else if (element.closest('form').length) {
                element.closest('form').submit(function(){
                    element.val(JSON.stringify(repeatableInputToObj(field_name)));
                    return true;
                })
            }
        }

        /**
         * Adds a new field group to the repeatable input.
         */
        function newRepeatableElement(container, field_group, values) {

            var field_name = container.data('repeatable-identifier');
            var new_field_group = field_group.clone();

            //this is the container for this repeatable group that holds it inside the main form.
            var container_holder = $('[data-repeatable-holder='+field_name+']');

            new_field_group.find('.delete-element').click(function(){
                new_field_group.find('input, select, textarea').each(function(i, el) {
                    //we trigger this event so fields can intercept when they are beeing deleted from the page
                    //implemented because of ckeditor instances that stayed around when deleted from page
                    //introducing unwanted js errors and high memory usage.
                    $(el).trigger('backpack_field.deleted');
                });
                $(this).parent().remove();
            });

            if (values != null) {
                // set the value on field inputs, based on the JSON in the hidden input
                new_field_group.find('input, select, textarea').each(function () {
                    if ($(this).data('repeatable-input-name')) {
                        $(this).val(values[$(this).data('repeatable-input-name')]);

                        // if it's a Select input with no options, also attach the values as a data attribute;
                        // this is done because the above val() call will do nothing if the options aren't there
                        // so the fields themselves have to treat this use case, and look at data-selected-options
                        // and create the options based on those values
                        if ($(this).is('select') && $(this).children('option').length == 0) {
                          $(this).attr('data-selected-options', JSON.stringify(values[$(this).data('repeatable-input-name')]));
                        }
                    }
                });
            }
            //we push the fields to the correct container in page.
            container_holder.append(new_field_group);
            initializeFieldsWithJavascript(container_holder);
        }
    </script>
  @endpush
@endif
