@php
    $cancelButtonUrl = empty($crud->getOperationSetting('cancelButtonUrl')) ? ($crud->hasAccess('list') ? url($crud->route) : url()->previous()) :
        (is_callable($crud->getOperationSetting('cancelButtonUrl')) ? $crud->getOperationSetting('cancelButtonUrl')($crud) : $crud->getOperationSetting('cancelButtonUrl'));
@endphp

<a href="{{ $cancelButtonUrl }}" class="btn btn-secondary text-decoration-none"><span class="la la-ban"></span> &nbsp;{{ trans('backpack::crud.cancel') }}</a>
