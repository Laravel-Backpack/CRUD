<div class="saveActions form-group my-3">
    @if(isset($saveAction['active']) && !is_null($saveAction['active']['value']))
    
        <input type="hidden" name="_save_action" value="{{ $saveAction['active']['value'] }}">

        @if(empty($saveAction['options']))
            <button type="submit" class="btn btn-success text-white">
                <span class="la la-save" role="presentation" aria-hidden="true"></span> &nbsp;
                <span data-value="{{ $saveAction['active']['value'] }}">{{ $saveAction['active']['label'] }}</span>
            </button>
        @else
            <div class="btn-group" role="group">
                <button type="submit" class="btn btn-success text-white">
                    <span class="la la-save" role="presentation" aria-hidden="true"></span> &nbsp;
                    <span data-value="{{ $saveAction['active']['value'] }}">{{ $saveAction['active']['label'] }}</span>
                </button>
                <button type="button" class="bpSaveButtonsGroup btn btn-success text-white dropdown-toggle dropdown-toggle-split" data-toggle="dropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <span class="d-none visually-hidden">Toggle Dropdown</span>
                </button>
                <ul class="dropdown-menu" aria-labelledby="bpSaveButtonsGroup">
                    @foreach( $saveAction['options'] as $value => $label)
                        <li><button class="dropdown-item" type="button" data-value="{{ $value }}">{{ $label }}</button></li>
                    @endforeach
                </ul>
            </div>
        @endif
    @endif
    @if(!$crud->hasOperationSetting('showCancelButton') || $crud->getOperationSetting('showCancelButton') == true)
        <a href="{{ $crud->getOperationSetting('backToAllEntriesUrl') ?? ($crud->hasAccess('list') ? url($crud->route) : url()->previous()) }}" class="btn btn-secondary text-decoration-none"><span class="la la-ban"></span> &nbsp;{{ trans('backpack::crud.cancel') }}</a>
    @endif

    @if ($crud->get('update.showDeleteButton') && $crud->get('delete.configuration') && $crud->hasAccess('delete'))
        <button onclick="confirmAndDeleteEntry()" type="button" class="btn btn-danger float-right float-end"><i class="la la-trash-alt"></i> {{ trans('backpack::crud.delete') }}</button>
    @endif
</div>


@push('after_scripts')
<script>

    // this function checks if form is valid.
    function checkFormValidity(form) {
        var formEl = form.length !== undefined ? form[0] : form;
        if (formEl && formEl.checkValidity) {
            return formEl.checkValidity();
        }
        return false;
    }

    // this function checks if any of the inputs has errors and report them on page.
    function reportValidity(form) {
        var formEl = form.length !== undefined ? form[0] : form;
        if (formEl && formEl.reportValidity) {
            // hide the save actions drop down if open
            formEl.querySelector('.dropdown-menu')?.classList.remove('show');
            formEl.reportValidity();
        }
    }

    function changeTabIfNeededAndDisplayErrors(form) {
        var formEl = form.length !== undefined ? form[0] : form;
        // we get the first invalid field
        var firstErrorField = formEl.querySelector(':invalid');
        if (firstErrorField) {
            // we find the closest tab
            var closestTab = firstErrorField.closest('.tab-pane');
            // if we found the tab we will change to that tab before reporting validity of form
            if(closestTab) {
                var id = closestTab.getAttribute('id');
                // switch tabs using Bootstrap API
                var tabTrigger = document.querySelector('.nav a[href="#' + id + '"]');
                if (tabTrigger) {
                    bootstrap.Tab.getOrCreateInstance(tabTrigger).show();
                }
            }
        }
        reportValidity(form);
    }

    // make all submit buttons trigger HTML5 validation
    document.addEventListener('DOMContentLoaded', function() {
        // Find all save actions containers and attach handlers to each one
        document.querySelectorAll('.saveActions').forEach(function(saveActionsContainer) {
            var form = saveActionsContainer.closest('form');
            var saveActionField = form.querySelector('[name="_save_action"]');
            var defaultSubmitButton = form.querySelector('button[type=submit], input[type=submit]');

            // Handle the main submit button (default save action)
            if (defaultSubmitButton) {
                defaultSubmitButton.addEventListener('click', function(e) {
                    e.preventDefault();
                    var saveActionSpan = this.querySelector('span:last-child');
                    
                    if(checkFormValidity(form)) {
                        saveActionField.value = saveActionSpan.getAttribute('data-value');
                        form.requestSubmit();
                    } else {
                        changeTabIfNeededAndDisplayErrors(form);
                    }
                });
            }

            // Handle the dropdown save actions
            saveActionsContainer.querySelectorAll('.dropdown-item').forEach(function(dropdownItem) {
                dropdownItem.addEventListener('click', function(e) {
                    if (checkFormValidity(form)) {
                        var saveAction = this.getAttribute('data-value');
                        saveActionField.value = saveAction;
                        form.requestSubmit();
                    } else {
                        changeTabIfNeededAndDisplayErrors(form);
                    }
                    e.stopPropagation();
                });
            });
        });
    });
</script>

@if ($crud->get('update.showDeleteButton') && $crud->get('delete.configuration') && $crud->hasAccess('delete'))
<script>
    function confirmAndDeleteEntry() {
        // Ask for confirmation before deleting an item
        swal({
            title: "{!! trans('backpack::base.warning') !!}",
            text: "{!! trans('backpack::crud.delete_confirm') !!}",
            icon: "warning",
            buttons: {
          	cancel: {
                text: "{!! trans('backpack::crud.cancel') !!}",
                value: null,
                visible: true,
                className: "bg-secondary",
                closeModal: true,
            },
            delete: {
                text: "{!! trans('backpack::crud.delete') !!}",
                value: true,
                visible: true,
                className: "bg-danger",
                },
            },
            dangerMode: true,
        }).then((value) => {
            if (value) {
                fetch('{{ url($crud->route.'/'.$entry->getKey()) }}', {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf_token"]')?.getAttribute('content') || '',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                })
                .then(response => response.json())
                .then(result => {
                        if (result !== '1') {
                            // if the result is an array, it means
                            // we have notification bubbles to show
                            if (result instanceof Object) {
                                // trigger one or more bubble notifications
                                Object.entries(result).forEach(function(entry) {
                                    var type = entry[0];
                                    entry[1].forEach(function(message, i) {
                                        new Noty({
                                            type: type,
                                            text: message
                                        }).show();
                                    });
                                });
                            } else { // Show an error alert
                                swal({
                                    title: "{!! trans('backpack::crud.delete_confirmation_not_title') !!}",
                                    text: "{!! trans('backpack::crud.delete_confirmation_not_message') !!}",
                                    icon: "error",
                                    timer: 4000,
                                    buttons: false,
                                });
                            }
                        }
                        // All is good, show a success message!
                        swal({
                            title: "{!! trans('backpack::crud.delete_confirmation_title') !!}",
                            text: "{!! trans('backpack::crud.delete_confirmation_message') !!}",
                            icon: "success",
                            buttons: false,
                            closeOnClickOutside: false,
                            closeOnEsc: false,
                        });

                        // Redirect in 1 sec so that admins get to see the success message
                        setTimeout(function () {
                            window.location.href = '{{ is_bool($crud->get('update.showDeleteButton')) ? url($crud->route) : (string) $crud->get('update.showDeleteButton') }}';
                        }, 1000);
                    })
                    .catch(error => {
                        console.log(error);
                        // Show an alert with the result
                        swal({
                            title: "{!! trans('backpack::crud.delete_confirmation_not_title') !!}",
                            text: "{!! trans('backpack::crud.delete_confirmation_not_message') !!}",
                            icon: "error",
                            timer: 4000,
                            buttons: false,
                        });
                    });
            }
        });
    }
</script>
@endif
@endpush