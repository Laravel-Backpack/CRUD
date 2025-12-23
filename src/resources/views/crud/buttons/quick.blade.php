@php
    // Access
    $access = $button->meta['access'] ?? null;
    if ($access === null) {
        $access = !is_null($crud->get(Str::of($button->name)->studly().'.access')) ? Str::of($button->name)->studly() : $button->name;
    }

    // Icon & Label
    $icon = $button->meta['icon'] ?? '';
    $label = $button->meta['label'] ?? Str::of($button->name)->headline();

    // Default Href
    $defaultHref = url($crud->route. ($entry?->getKey() ? '/'.$entry?->getKey().'/' : '/') . Str::of($button->name)->kebab());

    // Default Class
    $defaultClass = match ($button->stack) {
        'line' => 'btn btn-sm btn-link',
        'top' => 'btn btn-outline-primary',
        'bottom' => 'btn btn-sm btn-secondary',
        default => 'btn btn-outline-primary',
    };

    // Wrapper
    $wrapper = $button->meta['wrapper'] ?? [];
    $wrapper['element'] = $wrapper['element'] ?? 'a';
    $wrapper['href'] = $wrapper['href'] ?? $defaultHref;

    if (is_a($wrapper['href'], \Closure::class, true)) {
        $wrapper['href'] = ($wrapper['href'])($entry, $crud);
    }

    $wrapper['class'] = $wrapper['class'] ?? $defaultClass;

    // Ajax Configuration
    $ajaxConfiguration = $button->meta['ajax'] ?? false;
    $bulkConfiguration = $button->meta['bulk'] ?? false;

    if ($ajaxConfiguration) {
        $wrapper['data-route'] = $wrapper['href'];
        $wrapper['data-method'] = $ajaxConfiguration['method'] ?? 'GET';
        $wrapper['data-refresh-table'] = $ajaxConfiguration['refreshCrudTable'] ?? false;
        $wrapper['href'] = 'javascript:void(0)';

        // Bulk Configuration
        if ($bulkConfiguration) {
            $wrapper['onclick'] = 'sendQuickBulkButtonAjaxRequest(this)';
            $wrapper['data-button-type'] = 'quick-bulk-ajax';
            $wrapper['class'] .= ' bulk-button';

            $wrapper['data-bulk-no-entries-title'] = $bulkConfiguration['no_entries_title'] ?? trans('backpack::crud.bulk_no_entries_selected_title');
            $wrapper['data-bulk-no-entries-message'] = $bulkConfiguration['no_entries_message'] ?? trans('backpack::crud.bulk_no_entries_selected_message');
            $wrapper['data-bulk-confirm-title'] = $bulkConfiguration['confirm_title'] ?? trans('backpack::base.warning');
            $wrapper['data-bulk-confirm-message'] = $bulkConfiguration['confirm_message'] ?? trans('backpack::crud.bulk_operation_are_you_sure');
        } else {
            $wrapper['onclick'] = 'sendQuickButtonAjaxRequest(this)';
            $wrapper['data-button-type'] = 'quick-ajax';
        }

        // Messages
        $wrapper['data-success-title'] = $ajaxConfiguration['success_title'] ?? trans('backpack::crud.quick_button_ajax_success_title');
        $wrapper['data-success-message'] = $ajaxConfiguration['success_message'] ?? trans('backpack::crud.quick_button_ajax_success_message');
        $wrapper['data-error-title'] = $ajaxConfiguration['error_title'] ?? trans('backpack::crud.quick_button_ajax_error_title');
        $wrapper['data-error-message'] = $ajaxConfiguration['error_message'] ?? trans('backpack::crud.quick_button_ajax_error_message');
    }
@endphp

@if ($access === true || $crud->hasAccess($access, isset($entry) ? $entry : null))
    <{{ $wrapper['element'] }}
        bp-button="{{ $button->name }}"
        data-table-id="{{ isset($crudTableId) ? $crudTableId : 'crudTable' }}"
        @foreach ($wrapper as $attribute => $value)
            @if (is_string($attribute))
            {{ $attribute }}="{{ $value }}"
            @endif
        @endforeach
        >
        @if ($icon) <i class="{{ $icon }}"></i> @endif
        <span>{{ $label }}</span>
    </{{ $wrapper['element'] }}>
@endif

@if($ajaxConfiguration)
    {{-- Button Javascript --}}
    {{-- Pushed to the end of the page, after jQuery is loaded --}}
    @push('after_scripts') @if (request()->ajax()) @endpush @endif
    @bassetBlock('backpack/crud/buttons/quick-button.js')
    <script>
        if (typeof sendQuickButtonAjaxRequest !== 'function') {
            function sendQuickButtonAjaxRequest(button) {
                const tableId = button.getAttribute('data-table-id') ?? 'crudTable';
                const table = window.crud.tables[tableId];
                const route = button.getAttribute('data-route');
                const method = button.getAttribute('data-method');
                const refreshTable = button.getAttribute('data-refresh-table') == '1';

                const defaultButtonMessage = function(button, type) {
                    const buttonTitle = button.getAttribute(`data-${type}-title`);
                    const buttonMessage =  button.getAttribute(`data-${type}-message`);
                    return `<strong>${buttonTitle}</strong><br/>${buttonMessage}`;
                }

                fetch(route, {
                    method: method,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                    },
                })
                .then(response => response.json().then(data => ({ status: response.status, body: data })))
                .then(({ status, body }) => {
                    if (status >= 200 && status < 300) {
                        if (refreshTable && typeof table !== 'undefined') {
                            table.draw(false);
                        }
                        new Noty({
                            type: "success",
                            text: body.message || defaultButtonMessage(button, 'success'),
                        }).show();
                    } else {
                        throw new Error(body.message || defaultButtonMessage(button, 'error'));
                    }
                })
                .catch(error => {
                    new Noty({
                        type: "error",
                        text: error.message,
                    }).show();
                });
            }
        }

        if (typeof sendQuickBulkButtonAjaxRequest !== 'function') {
            function sendQuickBulkButtonAjaxRequest(button) {
                const tableId = button.getAttribute('data-table-id') ?? 'crudTable';
                const tableConfig = window.crud.tableConfigs[tableId] || window.crud;
                const table = window.crud.tables[tableId];
                const checkedItems = tableConfig.checkedItems;

                if (typeof checkedItems === 'undefined' || checkedItems.length === 0) {
                    const noEntriesTitle = button.getAttribute('data-bulk-no-entries-title');
                    const noEntriesMessage = button.getAttribute('data-bulk-no-entries-message');

                    new Noty({
                        type: "warning",
                        text: `<strong>${noEntriesTitle}</strong><br/>${noEntriesMessage}`
                    }).show();

                    return;
                }

                const route = button.getAttribute('data-route');
                const method = button.getAttribute('data-method');
                const confirmTitle = button.getAttribute('data-bulk-confirm-title');
                const confirmMessage = button.getAttribute('data-bulk-confirm-message').replace(':number', checkedItems.length);
                const refreshTable = button.getAttribute('data-refresh-table') == '1';

                const defaultButtonMessage = function(button, type) {
                    const buttonTitle = button.getAttribute(`data-${type}-title`);
                    const buttonMessage = button.getAttribute(`data-${type}-message`);
                    return `<strong>${buttonTitle}</strong><br/>${buttonMessage}`;
                }

                swal({
                    title: confirmTitle,
                    text: confirmMessage,
                    icon: "warning",
                    buttons: {
                        cancel: {
                            text: "{{ trans('backpack::crud.no') }}",
                            value: null,
                            visible: true,
                            className: "bg-secondary",
                            closeModal: true,
                        },
                        confirm: {
                            text: "{{ trans('backpack::crud.yes') }}",
                            value: true,
                            visible: true,
                            className: "bg-primary",
                        }
                    },
                }).then((value) => {
                    if (value) {
                        fetch(route, {
                            method: method,
                            headers: {
                                'Content-Type': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                            },
                            body: JSON.stringify({ entries: checkedItems })
                        })
                        .then(response => response.json().then(data => ({ status: response.status, body: data })))
                        .then(({ status, body }) => {
                            if (status >= 200 && status < 300) {
                                if (refreshTable && typeof table !== 'undefined') {
                                    if (table.rows().count() === checkedItems.length) {
                                        table.page("previous");
                                    }
                                    tableConfig.checkedItems = [];
                                    table.draw(false);
                                }
                                new Noty({
                                    type: "success",
                                    text: body.message || defaultButtonMessage(button, 'success'),
                                }).show();
                            } else {
                                throw new Error(body.message || defaultButtonMessage(button, 'error'));
                            }
                        })
                        .catch(error => {
                            new Noty({
                                type: "error",
                                text: error.message,
                            }).show();
                        });
                    }
                });
            }
        }
    </script>
    @endBassetBlock
    @if (!request()->ajax()) @endpush @endif
@endif