{{-- REPEATABLE FIELD TYPE --}}

@php
  $field['value'] = old($field['name']) ? old($field['name']) : (isset($field['value']) ? $field['value'] : (isset($field['default']) ? $field['default'] : '' ));
  // make sure the value is a JSON string (not array, if it's cast in the model)
  $field['value'] = is_array($field['value']) ? json_encode($field['value']) : $field['value'];
@endphp

@include('crud::fields.inc.wrapper_start')
  <label>{!! $field['label'] !!}</label>
  <input
      type="hidden"
      name="{{ $field['name'] }}"
      data-init-function="bpFieldInitRepeatableElement"
      value="{{ $field['value'] }}"
      @include('crud::fields.inc.attributes')
  >

  {{-- HINT --}}
  @if (isset($field['hint']))
      <p class="help-block text-muted text-sm">{!! $field['hint'] !!}</p>
  @endif

  <div class="container-repeatable-elements">
    <div class="col-md-12 well repeatable-element row m-1 p-2">
      @if (isset($field['fields']) && is_array($field['fields']) && count($field['fields']))
      	<div class="move move-element action-element"><i class="fa fa-arrows-alt"></i></div>
        <button type="button" class="close delete-element action-element"><span aria-hidden="true">×</span></button>
        @foreach($field['fields'] as $subfield)
          @php
              $fieldViewNamespace = $subfield['view_namespace'] ?? 'crud::fields';
              $fieldViewPath = $fieldViewNamespace.'.'.$subfield['type'];
              $subfield['showAsterisk'] = false;
          @endphp

          @include($fieldViewPath, ['field' => $subfield])
        @endforeach

      @endif
    </div>

  </div>
  <button type="button" class="btn btn-outline-primary btn-sm ml-1 add-repeatable-element-button">+ New Item</button>

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
          margin-bottom: 20px !important;
        }
        .container-repeatable-elements .action-element {
          z-index: 99;
          position: absolute!important;
          height: 30px;
          width: 30px;
          border-radius: 15px;
          text-align: center;
          background-color: #e8ebf0!important;;
        }

        .container-repeatable-elements .delete-element {
					top: -15px;
					left: -15px;
        }

        .container-repeatable-elements .move-element {
					top: 25px;
					left: -15px;
					outline: none;
					cursor: move;
					transform: rotate(45deg);
					line-height: 30px;
        }
      </style>
  @endpush

  {{-- FIELD EXTRA JS --}}
  {{-- push things in the after_scripts section --}}

  @push('crud_fields_scripts')
  		<script src="{{ asset('packages/jquery-ui-dist/jquery-ui.min.js') }}"></script>
      <script>
				$( function() {
			    $( ".container-repeatable-elements" ).sortable({
			    	handle: ".move-element"
			    });
			    $( ".container-repeatable-elements" ).disableSelection();
			  });

        /**
         * Takes all inputs and makes them an object.
         */
        function repeatableInputToObj(container) {
            var arr = [];
            var obj = {};

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
            // element will be a jQuery wrapped DOM node
            var container = element.siblings('.container-repeatable-elements');

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
                            //    .val('');
                    });

            // make a copy of the group of inputs in their default state
            // this way we have a clean element we can clone when the user
            // wants to add a new group of inputs
            var field_group_clone = container.find('.repeatable-element:first').clone();
            container.find('.repeatable-element').remove();

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
                    element.val(JSON.stringify(repeatableInputToObj(container)));
                })
            } else if (element.closest('form').length) {
                element.closest('form').submit(function(){
                    element.val(JSON.stringify(repeatableInputToObj(container)));
                    return true;
                })
            }
        }

        /**
         * Adds a new field group to the repeatable input.
         */
        function newRepeatableElement(container, field_group, values) {
            var new_field_group = field_group.clone();

            new_field_group.find('.delete-element').click(function(){
                $(this).parent().remove();
            });

            if (values != null) {
                new_field_group.find('input, select, textarea').each(function () {
                    if ($(this).data('repeatable-input-name')) {
                        $(this).val(values[$(this).data('repeatable-input-name')]);
                    }
                });
            }

            container.append(new_field_group);
            initializeFieldsWithJavascript(container);
        }
    </script>
  @endpush
@endif
