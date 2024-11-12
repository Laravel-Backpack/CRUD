@if ($crud->exportButtons())
  @basset('bp-datatables-buttons-bs5-css')
  @basset('bp-datatables-dt-buttons-bs5-js')
  @basset('bp-datatables-buttons-bs5-js')
  @basset('bp-datatables-jszip')
  @basset('bp-datatables-pdfmake')
  @basset('bp-datatables-pdfmake-fonts')
  @basset('bp-datatables-html5-button')
  @basset('bp-datatables-print-button')
  @basset('bp-datatables-colvis-button')
  <script>
    let dataTablesExportStrip = text => {
        if ( typeof text !== 'string' ) {
            return text;
        }

        return text
            .replace(/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/gi, '')
            .replace(/<!\-\-.*?\-\->/g, '')
            .replace(/<[^>]*>/g, '')
            .replace(/^\s+|\s+$/g, '')
            .replace(/\s+([,.;:!\?])/g, '$1')
            .replace(/\s+/g, ' ')
            .replace(/[\n|\r]/g, ' ');
    };

    let dataTablesExportFormat = {
        body: (data, row, column, node) => 
            node.querySelector('input[type*="text"]')?.value ??
            node.querySelector('input[type*="checkbox"]:not(.crud_bulk_actions_line_checkbox)')?.checked ??
            node.querySelector('select')?.selectedOptions[0]?.value ??
            dataTablesExportStrip(data),
    };

    window.crud.dataTableConfiguration.buttons = [
        @if($crud->get('list.showExportButton'))
        {
            extend: 'collection',
            text: '<i class="la la-download"></i> {{ trans('backpack::crud.export.export') }}',
            dropup: true,
            buttons: [
                {
                    name: 'copyHtml5',
                    extend: 'copyHtml5',
                    exportOptions: {
                        columns: function ( idx, data, node ) {
                            var $column = crud.table.column( idx );
                                return  ($column.visible() && $(node).attr('data-visible-in-export') != 'false') || $(node).attr('data-force-export') == 'true';
                        },
                        format: dataTablesExportFormat,
                    },
                    action: function(e, dt, button, config) {
                        crud.responsiveToggle(dt);
                        $.fn.DataTable.ext.buttons.copyHtml5.action.call(this, e, dt, button, config);
                        crud.responsiveToggle(dt);
                    }
                },
                {
                    name: 'excelHtml5',
                    extend: 'excelHtml5',
                    exportOptions: {
                        columns: function ( idx, data, node ) {
                            var $column = crud.table.column( idx );
                                return  ($column.visible() && $(node).attr('data-visible-in-export') != 'false') || $(node).attr('data-force-export') == 'true';
                        },
                        format: dataTablesExportFormat,
                    },
                    action: function(e, dt, button, config) {
                        crud.responsiveToggle(dt);
                        $.fn.DataTable.ext.buttons.excelHtml5.action.call(this, e, dt, button, config);
                        crud.responsiveToggle(dt);
                    }
                },
                {
                    name: 'csvHtml5',
                    extend: 'csvHtml5',
                    exportOptions: {
                        columns: function ( idx, data, node ) {
                            var $column = crud.table.column( idx );
                                return  ($column.visible() && $(node).attr('data-visible-in-export') != 'false') || $(node).attr('data-force-export') == 'true';
                        },
                        format: dataTablesExportFormat,
                    },
                    action: function(e, dt, button, config) {
                        crud.responsiveToggle(dt);
                        $.fn.DataTable.ext.buttons.csvHtml5.action.call(this, e, dt, button, config);
                        crud.responsiveToggle(dt);
                    }
                },
                {
                    name: 'pdfHtml5',
                    extend: 'pdfHtml5',
                    exportOptions: {
                        columns: function ( idx, data, node ) {
                            var $column = crud.table.column( idx );
                                return  ($column.visible() && $(node).attr('data-visible-in-export') != 'false') || $(node).attr('data-force-export') == 'true';
                        },
                        format: dataTablesExportFormat,
                    },
                    orientation: 'landscape',
                    action: function(e, dt, button, config) {
                        crud.responsiveToggle(dt);
                        $.fn.DataTable.ext.buttons.pdfHtml5.action.call(this, e, dt, button, config);
                        crud.responsiveToggle(dt);
                    }
                },
                {
                    name: 'print',
                    extend: 'print',
                    exportOptions: {
                        columns: function ( idx, data, node ) {
                            var $column = crud.table.column( idx );
                                return  ($column.visible() && $(node).attr('data-visible-in-export') != 'false') || $(node).attr('data-force-export') == 'true';
                        },
                        format: dataTablesExportFormat,
                    },
                    action: function(e, dt, button, config) {
                        crud.responsiveToggle(dt);
                        $.fn.DataTable.ext.buttons.print.action.call(this, e, dt, button, config);
                        crud.responsiveToggle(dt);
                    }
                }
            ]
        }
        @endif
        @if($crud->get('list.showTableColumnPicker'))
        ,{
            extend: 'colvis',
            text: '<i class="la la-eye-slash"></i> {{ trans('backpack::crud.export.column_visibility') }}',
            columns: function ( idx, data, node ) {
                return $(node).attr('data-can-be-visible-in-table') == 'true';
            },
            dropup: true
        }
        @endif
    ];

    // move the datatable buttons in the top-right corner and make them smaller
    function moveExportButtonsToTopRight() {
      crud.table.buttons().each(function(button) {
        if (button.node.className.indexOf('buttons-columnVisibility') == -1 && button.node.nodeName=='BUTTON')
        {
          button.node.className = button.node.className + " btn-sm";
        }
      })
      $(".dt-buttons").appendTo($('#datatable_button_stack' ));
      $('.dt-buttons').addClass('d-xs-block')
                      .addClass('d-sm-inline-block')
                      .addClass('d-md-inline-block')
                      .addClass('d-lg-inline-block');
    }

    crud.addFunctionToDataTablesDrawEventQueue('moveExportButtonsToTopRight');
  </script>
@endif
