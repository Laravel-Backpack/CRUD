@php
    if (!isset($widget['edit_button']) || $widget['edit_button'] !== false) {
        $widget['edit_button'] = true;
    }
    if (!isset($widget['delete_button']) || $widget['delete_button'] !== false) {
        $widget['delete_button'] = true;
    }
    if(!isset($widget['columns']) && isset($widget['model'])){
        $model = new $widget['model']();
        foreach ($model->getFillable() as $propertyName){
            $widget['columns'][$propertyName] = $crud->makeLabel($propertyName);
        }
    }
    if(!isset($widget['columns']) && !isset($widget['model'])){
        $widget['columns'] = [];
    }

@endphp
<div>
    <div class="d-flex align-items-center mb-2">
        <h5 class="mr-2 mb-0">{{$widget['label']}}</h5>
        <a href="/admin/{{$widget['backpack_crud']}}/create" class="btn btn-primary" data-style="zoom-in">
            <span class="ladda-label"><i class="la la-plus"></i> {{ trans('backpack::crud.add') }}</span>
        </a>
    </div>
    <table class="bg-white table table-striped table-hover nowrap rounded shadow-xs border-xs dataTable dtr-inline"
           cellspacing="0" aria-describedby="crudTable_info" role="grid"
    >
        <thead>
            <tr role="row">
                @foreach($widget['columns'] as $propertyName => $propertyLabel)
                    <th>{{$propertyLabel}}</th>
                @endforeach
                @if($widget['edit_button'] === true || $widget['delete_button'] === true)
                    <th>{{ trans('backpack::crud.actions') }}</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach($entry->{$widget['name']} as $model)
                <tr role="row">
                    @foreach($widget['columns'] as $propertyName => $propertyLabel)
                        <td>
                            <span>{{$model->$propertyName}}</span>
                        </td>
                    @endforeach
                    @if($widget['edit_button'] === true || $widget['delete_button'] === true)
                        <td>
                            @if ($widget['edit_button'] === true)
                                <a href="/admin/{{$widget['backpack_crud']}}/{{$model->id}}/edit"
                                   class="btn btn-sm btn-link">
                                    <i class="la la-edit"></i> {{ trans('backpack::crud.edit') }}
                                </a>
                            @endif
                            @if ($widget['delete_button'] === true)
                                <a href="javascript:void(0)" onclick="deleteEntryRelationHasManyWidget(this)"
                                   data-route="/admin/{{$widget['backpack_crud']}}/{{$model->id}}"
                                   class="btn btn-sm btn-link" data-button-type="delete">
                                    <i class="la la-trash"></i> {{ trans('backpack::crud.delete') }}
                                </a>
                            @endif
                        </td>
                    @endif
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                @foreach($widget['columns'] as $propertyName => $propertyLabel)
                    <th>{{$propertyLabel}}</th>
                @endforeach
                @if($widget['edit_button'] === true || $widget['delete_button'] === true)
                    <th rowspan="1" colspan="1">{{ trans('backpack::crud.actions') }}</th>
                @endif
            </tr>
        </tfoot>
    </table>
</div>

@push('after_scripts') @if (request()->ajax()) @endpush @endif
<script>
    if (typeof deleteEntryRelationHasManyWidget != 'function') {
        $('[data-button-type=delete]').unbind('click');

        function deleteEntryRelationHasManyWidget(button) {
            // ask for confirmation before deleting an item
            // e.preventDefault();
            var button = $(button);
            var route = button.attr('data-route');
            var row = button.closest('tr');

            swal({
                title: "{!! trans('backpack::base.warning') !!}",
                text: "{!! trans('backpack::crud.delete_confirm') !!}",
                icon: 'warning',
                buttons: {
                    cancel: {
                        text: "{!! trans('backpack::crud.cancel') !!}",
                        value: null,
                        visible: true,
                        className: 'bg-secondary',
                        closeModal: true
                    },
                    delete: {
                        text: "{!! trans('backpack::crud.delete') !!}",
                        value: true,
                        visible: true,
                        className: 'bg-danger'
                    }
                }
            }).then((value) => {
                if (value) {
                    $.ajax({
                        url: route,
                        type: 'DELETE',
                        success: function (result) {
                            if (result == 1) {
                                // Show a success notification bubble
                                new Noty({
                                    type: 'success',
                                    text: "{!! '<strong>'.trans('backpack::crud.delete_confirmation_title').'</strong><br>'.trans('backpack::crud.delete_confirmation_message') !!}"
                                }).show();

                                // Hide the modal, if any
                                $('.modal').modal('hide');

                                // Remove the details row, if it is open
                                if (row.hasClass('shown')) {
                                    row.next().remove();
                                }
                                // Remove the row from the datatable
                                row.remove();
                            } else {
                                // if the result is an array, it means
                                // we have notification bubbles to show
                                if (result instanceof Object) {
                                    // trigger one or more bubble notifications
                                    Object.entries(result).forEach(function (entry, index) {
                                        var type = entry[0];
                                        entry[1].forEach(function (message, i) {
                                            new Noty({
                                                type: type,
                                                text: message
                                            }).show();
                                        });
                                    });
                                } else {// Show an error alert
                                    swal({
                                        title: "{!! trans('backpack::crud.delete_confirmation_not_title') !!}",
                                        text: "{!! trans('backpack::crud.delete_confirmation_not_message') !!}",
                                        icon: 'error',
                                        timer: 4000,
                                        buttons: false
                                    });
                                }
                            }
                        },
                        error: function (result) {
                            // Show an alert with the result
                            swal({
                                title: "{!! trans('backpack::crud.delete_confirmation_not_title') !!}",
                                text: "{!! trans('backpack::crud.delete_confirmation_not_message') !!}",
                                icon: 'error',
                                timer: 4000,
                                buttons: false
                            });
                        }
                    });
                }
            });

        }
    }

    // make it so that the function above is run after each DataTable draw event
    // crud.addFunctionToDataTablesDrawEventQueue('deleteEntry');
</script>
@if (!request()->ajax()) @endpush @endif
