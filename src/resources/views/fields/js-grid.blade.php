<!-- js-grid input -->

<!--
USAGE:

            [   // js-grid
                'name' => 'children',
                'type' => 'js-grid',
                'label' => 'Children',
                'fields'=>[
                    [ 'name'=> "Name", 'type'=> "text", 'width'=> 150, 'validate'=> "required", 'db_field'=> "name" ],
                    [ 'name'=> "Age", 'type'=> "number", 'width'=> 60, 'validate'=> ["validator"=>"min", "param"=>"1"], 'db_field'=> "qty" ],
                    [ 'name'=> "Parent", 'type'=> "select", 'items'=> Parent::class, 'valueField'=> "id", 'textField'=> "name", 'validate'=> "required", 'width'=> 60 ],
                    [ 'name': "Married", 'type': "checkbox", 'title': "Is Married?" ],
                    [ 'type'=> "control" ]
                ],
                'wrapperAttributes' => [
                    'class' => 'form-group col-md-12'
                ],
            ],


END USAGE
-->
<?php
    foreach($field['fields'] as &$jsGridField){
        if($jsGridField['type'] === 'select' && isset($jsGridField['items'])){
            $items = null;
            foreach($jsGridField['items']::all() as $connected_entity){
                $items[] = [
                    $jsGridField['valueField'] => $connected_entity->{$jsGridField['valueField']},
                    $jsGridField['textField'] => $connected_entity->{$jsGridField['textField']},
                ];
            }
            unset($jsGridField['items']);
            $jsGridField['items'] = $items;
        }
    }
    $jsGridFields = json_encode($field['fields']);

    $current_value = old($field['name']) ? old($field['name']) : (isset($field['value']) ? $field['value'] : (isset($field['default']) ? json_encode($field['default']) : '' ));
?>
<div @include('crud::inc.field_wrapper_attributes') >

    <label>{!! $field['label'] !!}</label>
    @include('crud::inc.field_translatable_icon')

    <input class="array-json" type="hidden" id="{{ $field['name'] }}" name="{{ $field['name'] }}" value = '{{ $current_value }}'>
    <div class="jsGrid_{{ $field['name'] }}"></div>


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

        {{-- YOUR CSS HERE --}}
        <link type="text/css" rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jsgrid/1.5.3/jsgrid.min.css" />
        <link type="text/css" rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jsgrid/1.5.3/jsgrid-theme.min.css" />
    @endpush

    {{-- FIELD JS - will be loaded in the after_scripts section --}}
    @push('crud_fields_scripts')
        {{-- YOUR JS HERE --}}
        <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jsgrid/1.5.3/jsgrid.min.js"></script>
        <script>
            jQuery(document).ready(function($) {
                var TextField = jsGrid.TextField;

                function NumberField(config) {
                    TextField.call(this, config);
                }

                NumberField.prototype = new TextField({

                    sorter: "number",
                    align: "right",
                    readOnly: false,
                    step : "any",

                    itemTemplate: function(value) {
                        return  value ? parseFloat(value).toFixed(2) : "";
                    },

                    filterValue: function() {
                        return this.filterControl.val()
                            ? parseFloat(this.filterControl.val() || 0, 10).toFixed(2)
                            : undefined;
                    },

                    insertValue: function() {
                        return this.insertControl.val()
                            ? parseFloat(this.insertControl.val() || 0, 10).toFixed(2)
                            : undefined;
                    },

                    editValue: function() {
                        return this.editControl.val()
                            ? parseFloat(this.editControl.val() || 0, 10).toFixed(2)
                            : undefined;
                    },

                    _createTextBox: function() {
                        return $("<input>").attr("type", "number")
                            .prop("readonly", !!this.readOnly)
                            .prop("step", this.step);
                    }
                });

                jsGrid.fields.number = jsGrid.NumberField = NumberField;

                var dateField = function(config) {
                    jsGrid.Field.call(this, config);
                };

                dateField.prototype = new jsGrid.Field({

                    css: "date-field",            // redefine general property 'css'
                    align: "center",              // redefine general property 'align'

                    sorter: function(date1, date2) {
                        return new Date(date1) - new Date(date2);
                    },

                    itemTemplate: function(value) {
                        return new Date(value).toDateString();
                    },

                    insertTemplate: function(value) {
                        return this._insertPicker = $("<input>").datepicker({ defaultDate: new Date() });
                    },

                    editTemplate: function(value) {
                        return this._editPicker = $("<input>").datepicker().datepicker("setDate", new Date(value));
                    },

                    insertValue: function() {
                        return this._insertPicker.datepicker("getDate").toISOString();
                    },

                    editValue: function() {
                        return this._editPicker.datepicker("getDate").toISOString();
                    }
                });

                jsGrid.fields.date = dateField;

                var data = $('input[name="{{ $field['name'] }}"]').val() ? JSON.parse($('input[name="{{ $field['name'] }}"]').val()) : [];
                var fields = JSON.parse('{!! $jsGridFields !!}');

                $(".jsGrid_{{ $field['name'] }}").each(function(){
                    let $obj = $(this);
                    $obj.jsGrid({
                        autoload: false,
                        controller: {
                            loadData: $.noop,
                            insertItem: $.noop,
                            updateItem: $.noop,
                            deleteItem: $.noop
                        },

                        onItemInserted: function(args) {
                            $obj.jsGrid("refresh");
                        },

                        onItemUpdated: function(args) {
                            $obj.jsGrid("refresh");
                        },

                        onRefreshed: function(args) {
                            let $data = args.grid.data;

                            if(Object.keys($data).length){
                                $('input[name="{{ $field['name'] }}"]').val(JSON.stringify($data));
                            }
                        },

                        width: "100%",
                        height: "auto",

                        heading: true,
                        filtering: false,
                        inserting: true,
                        editing: true,
                        selecting: true,
                        sorting: false,
                        paging: false,
                        pageLoading: false,

                        data: data,

                        fields: fields
                    });
                });

            });
        </script>
    @endpush
@endif
{{-- End of Extra CSS and JS --}}
{{-- ########################################## --}}
