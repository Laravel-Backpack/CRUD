@if ($crud->hasAccess('create'))
    @php
        $backToAllEntriesUrl = $crud->getOperationSetting('backToAllEntriesUrl');
        $createUrl = url($crud->route.'/create') . ($backToAllEntriesUrl ? '?_backToAllEntriesUrl='.urlencode($backToAllEntriesUrl) : '');
    @endphp
    {{-- Regular create button that redirects to create page --}}
    <a href="{{ $createUrl }}" class="btn btn-primary" bp-button="create" data-style="zoom-in">
        <i class="la la-plus"></i> <span>{{ trans('backpack::crud.add') }} {{ $crud->entity_name }}</span>
    </a>
@endif