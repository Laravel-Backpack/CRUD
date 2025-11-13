@php
    $access = (function() use ($crud, $button) {
        if (isset($button->meta['access']) && $button->meta['access'] !== null && $button->meta['access'] !== false) {
            return $button->meta['access'];
        }
        return !is_null($crud->get(Str::of($button->name)->studly().'.access'))  ? Str::of($button->name)->studly() : $button->name;
    })();
    $icon = $button->meta['icon'] ?? '';
    $label = $button->meta['label'] ?? Str::of($button->name)->headline();

    $defaultHref = url($crud->route. ($entry?->getKey() ? '/'.$entry?->getKey().'/' : '/') . Str::of($button->name)->kebab());
    $defaultClass = match ($button->stack) {
        'line' => 'btn btn-sm btn-link',
        'top' => 'btn btn-outline-primary',
        'bottom' => 'btn btn-sm btn-secondary',
        default => 'btn btn-outline-primary',
    };

    $wrapper = $button->meta['wrapper'] ?? [];
    $wrapper['element'] = $wrapper['element'] ?? 'a';
    $wrapper['href'] = $wrapper['href'] ?? $defaultHref;
    if (is_a($wrapper['href'], \Closure::class, true)) {
        $wrapper['href'] = ($wrapper['href'])($entry, $crud);
    }
    $wrapper['class'] = $wrapper['class'] ?? $defaultClass;
    //if ajax enabled
    $buttonAjaxConfiguration = $button->meta['ajax'] ?? false;
    $bulkConfiguration = $button->meta['bulk'] ?? false;
    if($buttonAjaxConfiguration) {
        $wrapper['data-route'] = $wrapper['href'];
		$wrapper['data-method'] = $button->meta['ajax']['method'] ?? 'GET';
        $wrapper['data-refresh-table'] = $button->meta['ajax']['refreshCrudTable'] ?? false;

        $wrapper['href'] = 'javascript:void(0)';

        // Check if this is a bulk button
        if($bulkConfiguration) {
            $wrapper['onclick'] = 'sendQuickBulkButtonAjaxRequest(this)';
            $wrapper['data-button-type'] = 'quick-bulk-ajax';
            $wrapper['class'] = $wrapper['class'] . ' bulk-button';

            // Bulk-specific messages
            $wrapper['data-bulk-no-entries-title'] = $button->meta['bulk']['no_entries_title'] ?? trans('backpack::crud.bulk_no_entries_selected_title');
            $wrapper['data-bulk-no-entries-message'] = $button->meta['bulk']['no_entries_message'] ?? trans('backpack::crud.bulk_no_entries_selected_message');
            $wrapper['data-bulk-confirm-title'] = $button->meta['bulk']['confirm_title'] ?? trans('backpack::base.warning');
            $wrapper['data-bulk-confirm-message'] = $button->meta['bulk']['confirm_message'] ?? trans('backpack::crud.bulk_operation_are_you_sure');
        } else {
            $wrapper['onclick'] = 'sendQuickButtonAjaxRequest(this)';
            $wrapper['data-button-type'] = 'quick-ajax';
        }

        //success message
        $wrapper['data-success-title'] = $button->meta['ajax']['success_title'] ?? trans('backpack::crud.quick_button_ajax_success_title');
        $wrapper['data-success-message'] = $button->meta['ajax']['success_message'] ?? trans('backpack::crud.quick_button_ajax_success_message');
        //error message
        $wrapper['data-error-title'] = $button->meta['ajax']['error_title'] ?? trans('backpack::crud.quick_button_ajax_error_title');
        $wrapper['data-error-message']  = $button->meta['ajax']['error_message'] ?? trans('backpack::crud.quick_button_ajax_error_message');
    }
    //endif ajax enabled
@endphp

@if ($access === true || $crud->hasAccess($access, isset($entry) ? $entry : null))
    <{{ $wrapper['element'] }}
        bp-button="{{ $button->name }}"
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


@if($buttonAjaxConfiguration)
{{-- Button Javascript --}}
{{-- Pushed to the end of the page, after jQuery is loaded --}}
@push('after_scripts') @if (request()->ajax()) @endpush @endif
@bassetBlock('backpack/crud/buttons/quick-button.js')
<script>
	if (typeof sendQuickButtonAjaxRequest != 'function') {
        $("[data-button-type=quick-ajax]").unbind('click');

        function sendQuickButtonAjaxRequest(button) {
            let route = $(button).attr('data-route');

            const defaultButtonMessage = function(button, type) {
                let buttonTitle = button.getAttribute(`data-${type}-title`);
                let buttonMessage =  button.getAttribute(`data-${type}-message`);
                return `<strong>${buttonTitle}</strong><br/>${buttonMessage}`;
            }

            $.ajax({
                url: route,
                type: $(button).attr('data-method'),
                success: function(result) {

                    if($(button).attr('data-refresh-table') && typeof crud != 'undefined' && typeof crud.table != 'undefined'){
                        crud.table.draw(false);
                    }
                    let message;
                    //if message is returned from the API use that message
                    if(result.message){
                        message = result.message;
                    }

                    message ??= defaultButtonMessage(button, 'success');

                    new Noty({
                        type: "success",
                        text: message,
                    }).show();
                },
                error: function(result) {

                    let message;

                    //if message is returned from the API use that message
                    if(result.responseJSON.message){
                        message = result.responseJSON.message;
                    }

                    message ??= defaultButtonMessage(button, 'error');

                    new Noty({
                        type: "error",
                        text: message,
                    }).show();
                }
            });
        }
	}

    if (typeof sendQuickBulkButtonAjaxRequest != 'function') {
        $("[data-button-type=quick-bulk-ajax]").unbind('click');

        function sendQuickBulkButtonAjaxRequest(button) {
            // Check if items are selected
            if (typeof crud.checkedItems === 'undefined' || crud.checkedItems.length == 0) {
                let noEntriesTitle = $(button).attr('data-bulk-no-entries-title');
                let noEntriesMessage = $(button).attr('data-bulk-no-entries-message');

                new Noty({
                    type: "warning",
                    text: `<strong>${noEntriesTitle}</strong><br/>${noEntriesMessage}`
                }).show();

                return;
            }

            let route = $(button).attr('data-route');
            let confirmTitle = $(button).attr('data-bulk-confirm-title');
            let confirmMessage = $(button).attr('data-bulk-confirm-message').replace(':number', crud.checkedItems.length);

            const defaultButtonMessage = function(button, type) {
                let buttonTitle = button.getAttribute(`data-${type}-title`);
                let buttonMessage = button.getAttribute(`data-${type}-message`);
                return `<strong>${buttonTitle}</strong><br/>${buttonMessage}`;
            }

            // Show confirmation dialog
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
                    // Submit AJAX request with selected items
                    $.ajax({
                        url: route,
                        type: $(button).attr('data-method'),
                        data: { entries: crud.checkedItems },
                        success: function(result) {
                            if($(button).attr('data-refresh-table') && typeof crud != 'undefined' && typeof crud.table != 'undefined'){
                                // Move to previous page if all items on current page were processed
                                if(crud.table.rows().count() === crud.checkedItems.length) {
                                    crud.table.page("previous");
                                }

                                // Clear selections and refresh table
                                crud.checkedItems = [];
                                crud.table.draw(false);
                            }

                            let message;
                            // If message is returned from the API use that message
                            if(result.message){
                                message = result.message;
                            } else {
                                message = defaultButtonMessage(button, 'success');
                            }

                            new Noty({
                                type: "success",
                                text: message,
                            }).show();
                        },
                        error: function(result) {
                            let message;

                            // If message is returned from the API use that message
                            if(result.responseJSON && result.responseJSON.message){
                                message = result.responseJSON.message;
                            } else {
                                message = defaultButtonMessage(button, 'error');
                            }

                            new Noty({
                                type: "error",
                                text: message,
                            }).show();
                        }
                    });
                }
            });
        }
    }
</script>
@endBassetBlock
@if (!request()->ajax()) @endpush @endif
@endif