@if ($crud->exportButtons())
    @basset('https://cdn.datatables.net/buttons/3.2.6/js/dataTables.buttons.min.js')
    @basset('https://cdn.datatables.net/buttons/3.2.6/js/buttons.bootstrap5.min.js')
    @basset('https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js')
    @basset('https://cdn.jsdelivr.net/npm/pdfmake@0.3.11/build/pdfmake.min.js')
    @basset('https://cdn.jsdelivr.net/npm/pdfmake@0.3.11/build/vfs_fonts.js')
    @basset('https://cdn.datatables.net/buttons/3.2.6/js/buttons.html5.min.js')
    @basset('https://cdn.datatables.net/buttons/3.2.6/js/buttons.print.min.js')
    @basset('https://cdn.datatables.net/buttons/3.2.6/js/buttons.colVis.min.js')
    <script>
        let dataTablesExportStrip = text => {
            if ( typeof text !== 'string' ) {
                return text;
            }
    
            return text
                .replace(/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/gi, '')
                .replace(/<!--.*?-->/g, '')
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

        let getColumnVisibility = function(dt, idx, node) {
            try {
                var $tableFromNode = $(node).closest('table');
                
                if ($tableFromNode.length === 0) {
                    return false;
                }
                
                var $header = $tableFromNode.find('thead th').eq(idx);
                
                var isDomVisible = $header.length > 0 && $header.is(':visible') && $header.css('display') !== 'none';
                
                return isDomVisible;
            } catch (e) {
                return true; // Default to visible if there's an error
            }
        };
    
        // Create an export buttons configuration that can be applied to any table
        window.crud.exportButtonsConfig = [
            @if($crud->get('list.showExportButton'))
            {
                extend: 'collection',
                text: '<i class="la la-download"></i> {{ trans('backpack::crud.export.export') }}',
                className: 'buttons-collection dropdown-toggle',
                dropup: true,
                buttons: [
                    {
                        name: 'copyHtml5',
                        extend: 'copyHtml5',
                        exportOptions: {
                            columns: function ( idx, data, node ) {
                                var dt = $(node).closest('table').DataTable();
                                var isVisible = getColumnVisibility(dt, idx, node);
                                var isExportable = $(node).attr('data-visible-in-export') == 'true';
                                var isForceExport = $(node).attr('data-force-export') == 'true';

                                if(isForceExport && isExportable) {
                                   return true;
                                }
                                return isVisible && isExportable;
                            },
                            format: dataTablesExportFormat,
                        },
                    },
                    {
                        name: 'excelHtml5',
                        extend: 'excelHtml5',
                        exportOptions: {
                            columns: function ( idx, data, node ) {
                                var dt = $(node).closest('table').DataTable();
                                var isVisible = getColumnVisibility(dt, idx, node);
                                var isExportable = $(node).attr('data-visible-in-export') == 'true';
                                var isForceExport = $(node).attr('data-force-export') == 'true';
                                
                                if(isForceExport && isExportable) {
                                   return true;
                                }
                                return isVisible && isExportable;
                            },
                            format: dataTablesExportFormat,
                        },
                    },
                    {
                        name: 'csvHtml5',
                        extend: 'csvHtml5',
                        exportOptions: {
                            columns: function ( idx, data, node ) {
                                var dt = $(node).closest('table').DataTable();
                                var isVisible = getColumnVisibility(dt, idx, node);
                                var isExportable = $(node).attr('data-visible-in-export') == 'true';
                                var isForceExport = $(node).attr('data-force-export') == 'true';

                                 if(isForceExport && isExportable) {
                                   return true;
                                }
                                
                                return isVisible && isExportable;
                            },
                            format: dataTablesExportFormat,
                        },
                    },
                    {
                        name: 'pdfHtml5',
                        extend: 'pdfHtml5',
                        exportOptions: {
                            columns: function ( idx, data, node ) {
                                var dt = $(node).closest('table').DataTable();
                                var isVisible = getColumnVisibility(dt, idx, node);
                                var isExportable = $(node).attr('data-visible-in-export') == 'true';
                                var isForceExport = $(node).attr('data-force-export') == 'true';

                                if(isForceExport && isExportable) {
                                   return true;
                                }
                                
                                return isVisible && isExportable;
                            },
                            format: dataTablesExportFormat,
                        },
                        orientation: 'landscape'
                    },
                    {
                        name: 'print',
                        extend: 'print',
                        exportOptions: {
                            columns: function ( idx, data, node ) {
                                var dt = $(node).closest('table').DataTable();
                                var isVisible = getColumnVisibility(dt, idx, node);
                                var isExportable = $(node).attr('data-visible-in-export') == 'true';
                                var isForceExport = $(node).attr('data-force-export') == 'true';

                                if(isForceExport && isExportable) {
                                   return true;
                                }
                                
                                return isVisible && isExportable;
                            },
                            format: dataTablesExportFormat,
                        },
                        orientation: 'landscape',
                    }
                ]
            }
            @endif
            @if($crud->get('list.showTableColumnPicker'))
            ,{
                extend: 'colvis',
                text: '<i class="la la-eye-slash"></i> {{ trans('backpack::crud.export.column_visibility') }}',
                className: 'buttons-collection dropdown-toggle',
                columns: function ( idx, data, node ) {
                    return $(node).attr('data-can-be-visible-in-table') == 'true';
                },
                dropup: true
            }
            @endif
        ];
    
        // Function to move export buttons to top right and make them smaller
        window.crud.moveExportButtonsToTopRight = function(tableId) {
            tableId = tableId || 'crudTable';
            var table = window.crud.tables[tableId];
            
            if (!table || !table.buttons) return;
            table.buttons().each(function(button) {
                if (button.node.className.indexOf('buttons-columnVisibility') == -1 && button.node.nodeName=='BUTTON') {
                    button.node.className = button.node.className.replace('btn-secondary', 'btn-sm');
                }
            });

            $('.dt-buttons').addClass('d-xs-block')
                            .addClass('d-sm-inline-block')
                            .addClass('d-md-inline-block')
                            .addClass('d-lg-inline-block');
        };

        window.crud.setupExportHandlers = function(tableId) {
            tableId = tableId || 'crudTable';
            var table = window.crud.tables[tableId];
            
            if (!table || !table.buttons) return;
            
            // Add click handlers to all export buttons
            table.buttons().each(function(button, idx) {
                var buttonNode = button.node;
                if (buttonNode && (
                    buttonNode.classList.contains('buttons-pdf') ||
                    buttonNode.classList.contains('buttons-excel') ||
                    buttonNode.classList.contains('buttons-csv') ||
                    buttonNode.classList.contains('buttons-copy') ||
                    buttonNode.classList.contains('buttons-print')
                )) {
                    // Remove any existing handlers
                    $(buttonNode).off('click.responsiveExport');
                    
                    // Add our custom handler
                    $(buttonNode).on('click.responsiveExport', function(e) {
                        e.preventDefault();
                        e.stopImmediatePropagation();
                        
                        window.crud.responsiveToggle(table);
                        
                        setTimeout(function() {
                            $(buttonNode).off('click.responsiveExport');
                            
                            $(buttonNode).trigger('click');
                            
                            setTimeout(function() {
                                $(buttonNode).on('click.responsiveExport', arguments.callee);
                            }, 100);
                        }, 50);
                    });
                }
            });
        };
    </script>
    @push('after_styles')
        @basset('https://cdn.datatables.net/buttons/3.2.6/css/buttons.bootstrap5.min.css')
    @endpush
@endif
