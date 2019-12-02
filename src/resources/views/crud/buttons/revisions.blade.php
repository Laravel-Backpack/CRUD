@if ($crud->hasAccess('revisions') && count($entry->revisionHistory))
    <a href="{{ url($crud->route.'/'.$entry->getRouteKey().'/revisions') }}" class="btn btn-sm btn-link"><i class="fa fa-history"></i> {{ trans('backpack::crud.revisions') }}</a>
@endif
