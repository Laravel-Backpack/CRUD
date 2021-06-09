<!-- Backpack Table Field Type -->

<?php
    $max = isset($field['max']) && (int) $field['max'] > 0 ? $field['max'] : -1;
    $min = isset($field['min']) && (int) $field['min'] > 0 ? $field['min'] : -1;
    $item_name = strtolower(isset($field['entity_singular']) && ! empty($field['entity_singular']) ? $field['entity_singular'] : $field['label']);

    $items = oldValueDefaultOrFallback($field, '');

    // make sure no matter the attribute casting
    // the $items variable contains a properly defined JSON string
    if (is_array($items)) {
        if (count($items)) {
            $items = json_encode($items);
        } else {
            $items = '[]';
        }
    } elseif (is_string($items) && ! is_array(json_decode($items))) {
        $items = '[]';
    }

    // make sure columns are defined
    if (! isset($field['columns'])) {
        $field['columns'] = ['value' => 'Value'];
    }

    $field['wrapper'] = $field['wrapper'] ?? $field['wrapperAttributes'] ?? [];
    $field['wrapper']['data-field-type'] = 'table';
    $field['wrapper']['data-field-name'] = $field['name'];
?>
@include('crud::fields.inc.wrapper_start')

    <label>{!! $field['label'] !!}</label>
    @include('crud::fields.inc.translatable_icon')

    <input class="array-json"
            type="hidden"
            data-init-function="bpFieldInitTableElement"
            name="{{ $field['name'] }}"
            value="{{ $items }}"
            data-max="{{$max}}"
            data-min="{{$min}}"
            data-maxErrorTitle="{{trans('backpack::crud.table_cant_add', ['entity' => $item_name])}}"
            data-maxErrorMessage="{{trans('backpack::crud.table_max_reached', ['max' => $max])}}">

    <div class="array-container form-group">

        <table class="table table-sm table-striped m-b-0">

            <thead>
                <tr>
                    @foreach( $field['columns'] as $column )
                    <th style="font-weight: 600!important;">
                        {{ $column }}
                    </th>
                    @endforeach
                    <th class="text-center"> {{-- <i class="la la-sort"></i> --}} </th>
                    <th class="text-center"> {{-- <i class="la la-trash"></i> --}} </th>
                </tr>
            </thead>

            <tbody class="table-striped items sortableOptions">

                <tr class="array-row clonable" style="display: none;">
                    @foreach( $field['columns'] as $column => $label)
                    <td>
                        <input class="form-control form-control-sm" type="text" data-cell-name="item.{{ $column }}">
                    </td>
                    @endforeach
                    <td>
                        <span class="btn btn-sm btn-light sort-handle pull-right"><span class="sr-only">sort item</span><i class="la la-sort" role="presentation" aria-hidden="true"></i></span>
                    </td>
                    <td>
                        <button class="btn btn-sm btn-light removeItem" type="button"><span class="sr-only">delete item</span><i class="la la-trash" role="presentation" aria-hidden="true"></i></button>
                    </td>
                </tr>

            </tbody>

        </table>

        <div class="array-controls btn-group m-t-10">
            <button class="btn btn-sm btn-light" type="button" data-button-type="addItem"><i class="la la-plus"></i> {{trans('backpack::crud.add')}} {{ $item_name }}</button>
        </div>

    </div>

    {{-- HINT --}}
    @if (isset($field['hint']))
        <p class="help-block">{!! $field['hint'] !!}</p>
    @endif
@include('crud::fields.inc.wrapper_end')

{{-- ########################################## --}}
{{-- Extra CSS and JS for this particular field --}}
{{-- If a field type is shown multiple times on a form, the CSS and JS will only be loaded once --}}
@if ($crud->fieldTypeNotLoaded($field))
    @php
        $crud->markFieldTypeAsLoaded($field);
    @endphp

    {{-- FIELD JS - will be loaded in the after_scripts section --}}
    @push('crud_fields_scripts')
        {{-- YOUR JS HERE --}}
        <script type="text/javascript" src="{{ asset('packages/jquery-ui-dist/jquery-ui.min.js') }}"></script>

        <script>
            function bpFieldInitTableElement(element) {
                var $tableWrapper = element.parent('[data-field-type=table]');
                var $rows = (element.attr('value') != '') ? $.parseJSON(element.attr('value')) : '';
                var $max = element.attr('data-max');
                var $min = element.attr('data-min');
                var $maxErrorTitle = element.attr('data-maxErrorTitle');
                var $maxErrorMessage = element.attr('data-maxErrorMessage');


                // add rows with the information from the database
                if($rows != '[]') {
                    $.each($rows, function(key) {

                        addItem();

                        $.each(this, function(column , value) {
                            $tableWrapper.find('tbody tr:last').find('input[data-cell-name="item.' + column + '"]').val(value);
                        });

                        // if it's the last row, update the JSON
                        if ($rows.length == key+1) {
                            updateTableFieldJson();
                        }
                    });
                }

                // add minimum rows if needed
                var itemCount = $tableWrapper.find('tbody tr').not('.clonable').length;
                if($min > 0 && itemCount < $min) {
                    $rowsToAdd = Number($min) - Number(itemCount);

                    for(var i = 0; i < $rowsToAdd; i++){
                        addItem();
                    }
                }

                $tableWrapper.find('.sortableOptions').sortable({
                    handle: '.sort-handle',
                    axis: 'y',
                    helper: function(e, ui) {
                        ui.children().each(function() {
                            $(this).width($(this).width());
                        });
                        return ui;
                    },
                    update: function( event, ui ) {
                        updateTableFieldJson();
                    }
                });


                $tableWrapper.find('[data-button-type=addItem]').click(function() {
                    if($max > -1) {
                        var totalRows = $tableWrapper.find('tbody tr').not('.clonable').length;

                        if(totalRows < $max) {
                            addItem();
                            updateTableFieldJson();
                        } else {
                            new Noty({
                              type: "warning",
                              text: "<strong>"+$maxErrorTitle+"</strong><br>"+$maxErrorMessage
                            }).show();
                        }
                    } else {
                        addItem();
                        updateTableFieldJson();
                    }
                });

                function addItem() {
                    $tableWrapper.find('tbody').append($tableWrapper.find('tbody .clonable').clone().show().removeClass('clonable'));
                }

                $tableWrapper.on('click', '.removeItem', function() {
                    var totalRows = $tableWrapper.find('tbody tr').not('.clonable').length;
                    if (totalRows > $min) {
                        $(this).closest('tr').remove();
                        updateTableFieldJson();
                        return false;
                    }
                });

                $tableWrapper.find('tbody').on('keyup', function() {
                    updateTableFieldJson();
                });


                function updateTableFieldJson() {
                    var $rows = $tableWrapper.find('tbody tr').not('.clonable');
                    var $hiddenField = $tableWrapper.find('input.array-json');

                    var json = '[';
                    var otArr = [];
                    var tbl2 = $rows.each(function(i) {
                        x = $(this).children().closest('td').find('input');
                        var itArr = [];
                        x.each(function() {
                            if(this.value.length > 0) {
                                var key = $(this).attr('data-cell-name').replace('item.','');
                                itArr.push('"' + key + '":' + JSON.stringify(this.value));
                            }
                        });
                        otArr.push('{' + itArr.join(',') + '}');
                    })
                    json += otArr.join(",") + ']';

                    var totalRows = $rows.length;

                    $hiddenField.val( totalRows ? json : null );
                }

                // on page load, make sure the input has the old values
                updateTableFieldJson();
            }
        </script>
    @endpush
@endif
{{-- End of Extra CSS and JS --}}
{{-- ########################################## --}}
