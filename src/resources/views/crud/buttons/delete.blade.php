
@php
	$redirectUrl = $crud->getOperationSetting('deleteButtonRedirect');
	if($redirectUrl && $redirectUrl instanceof \Closure){
		$redirectUrl = $redirectUrl();
	}
	$redirectUrl = filter_var($redirectUrl, FILTER_VALIDATE_URL) ? $redirectUrl : null;
@endphp

@if ($crud->hasAccess('delete', $entry))
    <a href="javascript:void(0)"
        onclick="deleteEntry(this)"
        bp-button="delete"
        data-redirect-route="{{ $redirectUrl }}"
        data-route="{{ url($crud->route.'/'.$entry->getKey()) }}"
        data-table-id="{{ isset($crudTableId) ? $crudTableId : 'crudTable' }}"
        data-warning-text="{!! trans('backpack::base.warning') !!}"
        data-confirm-text="{!! trans('backpack::crud.delete_confirm') !!}"
        data-cancel-text="{!! trans('backpack::crud.cancel') !!}"
        data-delete-text="{!! trans('backpack::crud.delete') !!}"
        data-error-title="{!! trans('backpack::crud.delete_confirmation_not_title') !!}"
        data-error-text="{!! trans('backpack::crud.delete_confirmation_not_message') !!}"
        data-delete-confirmation-text="{!! '<strong>'.trans('backpack::crud.delete_confirmation_title').'</strong><br>'.trans('backpack::crud.delete_confirmation_message') !!}"
        class="btn btn-sm btn-link"
        data-button-type="delete"
    >
        <i class="la la-trash"></i> <span>{{ trans('backpack::crud.delete') }}</span>
    </a>
@endif

{{-- Button Javascript --}}
{{-- - used right away in AJAX operations (ex: List) --}}
{{-- - pushed to the end of the page, after jQuery is loaded, for non-AJAX operations (ex: Show) --}}
@push('after_scripts') @if (request()->ajax()) @endpush @endif
@bassetBlock('backpack/crud/buttons/delete-button.js')
<script>
    if (typeof deleteEntry != 'function') {
        $("[data-button-type=delete]").unbind('click');

        function deleteEntry(button) {
            var route = $(button).attr('data-route');

            swal({
                title: button.getAttribute('data-warning-text'),
                text: button.getAttribute('data-confirm-text'),
                icon: "warning",
                buttons: {
                    cancel: {
                        text: button.getAttribute('data-cancel-text'),
                        value: null,
                        visible: true,
                        className: "bg-secondary",
                        closeModal: true,
                    },
                    delete: {
                        text: button.getAttribute('data-delete-text'),
                        value: true,
                        visible: true,
                        className: "bg-danger",
                    },
                },
                dangerMode: true,
            }).then((value) => {
                function showDeleteNotyAlert() {
                    // Show a success notification bubble
                    new Noty({
                        type: "success",
                        text: button.getAttribute('data-delete-confirmation-text')
                    }).show();
                }
                if (value) {
                    $.ajax({
                        url: route,
                        type: 'DELETE',
                        success: function(result) {
                            console.log(result);
                            if (result == 1) {
                                // Get the table ID from the button's data attribute
                                let tableId = $(button).data('table-id') || 'crudTable';
                                
                                // Check if we have a specific DataTable instance
                                if (typeof window.crud !== 'undefined' && 
                                    typeof window.crud.tables !== 'undefined' && 
                                    window.crud.tables[tableId]) {
                                    
                                    let table = window.crud.tables[tableId];
                                    
                                    // Move to previous page in case of deleting the only item in table
                                    if(table.rows().count() === 1) {
                                        table.page("previous");
                                    }
                                    // Hide the modal, if any is displayed
                                    $('.dtr-modal-close').click();

                                    showDeleteNotyAlert();
                                    table.draw(false);
                                } else {
                                    // there is no crud table in the current page, so we will redirect the user to the defined button redirect route in data-redirect-url
                                    let redirectRoute = $(button).data('redirect-route');
                                    if(redirectRoute){
                                        // queue the alert in localstorage to show it after the redirect
                                        localStorage.setItem('backpack_alerts', JSON.stringify({
                                            'success': [ 
                                                button.getAttribute('data-delete-confirmation-title')
                                            ]
                                        }));
                                        window.location.href = redirectRoute;
                                    } else {
                                        // Show a success notification bubble, keep the previous behaviour of not redirecting
                                        // and keeping the entry open after deletion
                                        showDeleteNotyAlert();
                                    }
                                }
                            } else {
                                // if the result is an array, it means 
                                // we have notification bubbles to show
                                if (result instanceof Object) {
                                    // trigger one or more bubble notifications 
                                    Object.entries(result).forEach(function(entry, index) {
                                        var type = entry[0];
                                        entry[1].forEach(function(message, i) {
                                            new Noty({
                                                type: type,
                                                text: message
                                            }).show();
                                        });
                                    });
                                } else {
                                    // Show an error alert
                                    swal({
                                        title: button.getAttribute('data-error-title'),
                                        text: button.getAttribute('data-error-text'),
                                        icon: "error",
                                        timer: 4000,
                                        buttons: false,
                                    });
                                }
                            }
                        },
                        error: function(result) {
                            console.log(result);
                            // Show an alert with the result
                            swal({
                                title: button.getAttribute('data-error-title'),
                                text: button.getAttribute('data-error-text'),
                                icon: "error",
                                timer: 4000,
                                buttons: false,
                            });
                        }
                    });
                }
            });

        }
    }
</script>
@endBassetBlock
@if (!request()->ajax()) @endpush @endif
