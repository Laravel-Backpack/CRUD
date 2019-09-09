<!-- field_type_name -->
<div @include('crud::inc.field_wrapper_attributes') >
    <label>{!! $field['label'] !!}</label>
    <input
        type="hidden"
        name="{{ $field['name'] }}"
        data-init-function="bpFieldInitMultiplyElement"
        value="{{ old($field['name']) ? old($field['name']) : (isset($field['value']) ? $field['value'] : (isset($field['default']) ? $field['default'] : '' )) }}"
        @include('crud::inc.field_attributes')
    >

    <div class="container-multiply-elements">
      <div class="col-md-12 well multiply-element row m-1 p-2 bg-light">
        @if (isset($field['fields']) && is_array($field['fields']) && count($field['fields']))
          <button type="button" class="close delete-element"><span aria-hidden="true">×</span></button>
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
    <button type="button" class="btn btn-primary float-right multiply-elements">+</button>

    {{-- HINT --}}
    @if (isset($field['hint']))
        <p class="help-block">{!! $field['hint'] !!}</p>
    @endif
</div>

@if ($crud->fieldTypeNotLoaded($field))
    @php
        $crud->markFieldTypeAsLoaded($field);
    @endphp
  {{-- FIELD EXTRA CSS  --}}
  {{-- push things in the after_styles section --}}

      @push('crud_fields_styles')
          <!-- no styles -->
          <style type="text/css">
            .multiply-element {
              border: 1px solid rgba(0,0,0,.1);
              border-radius: 5px;
            }
            .container-multiply-elements .delete-element {
              z-index: 99;
              position: absolute!important;
              margin-left: -24px;
              margin-top: 0px;
              height: 30px;
              width: 30px;
              border-radius: 15px;
              text-align: center;
              background-color: #c0d0ed!important;
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
                    function multiplyInputToObj(container) {
                        var arr = [];
                        var obj = {};

                        container.find('.well').each(function () {
                            $(this).find('input, select, textarea').each(function () {
                                if ($(this).data('secondary-name')) {
                                    obj[$(this).data('secondary-name')] = $(this).val();
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
                    function bpFieldInitMultiplyElement(element) {
                        // element will be a jQuery wrapped DOM node
                        var container = element.siblings('.container-multiply-elements');

                        container.find('input, select, textarea')
                                .each(function(){
                                    if ($(this).data('name')) {
                                        var name_attr = $(this).data('name');
                                        $(this).removeAttr("data-name");

                                    } else if ($(this).attr('name')) {
                                        var name_attr = $(this).attr('name');
                                        $(this).removeAttr("name");
                                    }
                                    $(this).attr('data-secondary-name', name_attr)
                                           .val('');
                                });

                        var field_group = container.find('.multiply-element:first').clone();
                        container.find('.multiply-element').remove();

                        element.parent().find('.multiply-elements').click(function(){
                            newMultiplyElement(container,field_group);
                        });

                        if (element.val()) {
                            var multiply_fields_values = JSON.parse(element.val());

                            for (var i = 0; i < multiply_fields_values.length; ++i) {
                                newMultiplyElement(container,field_group,multiply_fields_values[i]);
                            }
                        } else {
                            element.parent().find('.multiply-elements').trigger('click');
                        }

                        if (element.closest('.modal-content').length) {

                            element.closest('.modal-content').find('.save-block').click(function(){
                                element.val(JSON.stringify(multiplyInputToObj(container)));
                            })
                        } else if (element.closest('form').length) {

                            element.closest('form').submit(function(){
                                element.val(JSON.stringify(multiplyInputToObj(container)));
                                return true;
                            })
                        }
                    }

                    /**
                     * Adds a new field group to the multiply input.
                     */
                    function newMultiplyElement(container, field_group, values) {

                        var new_field_group = field_group.clone();
                        new_field_group.find('.delete-element')
                                        .click(function(){
                                            $(this).parent().remove();
                                        })

                        if (values != null) {
                            new_field_group.find('input, select, textarea').each(function () {
                                if ($(this).data('secondary-name')) {
                                    $(this).val(values[$(this).data('secondary-name')]);
                                }
                            });
                        }
                        container.append(new_field_group);
                        initializeFieldsWithJavascript(container);
                    }


        </script>
      @endpush
@endif