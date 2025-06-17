<div class="datagrid p-3">
    @foreach($columns as $column)
        <div class="datagrid-item">
            <div class="datagrid-title">{!! $column['label'] !!}</div>
            <div class="datagrid-content">
                @includeFirst(\Backpack\CRUD\ViewNamespaces::getViewPathsWithFallbackFor('columns', $column['type'], 'crud::columns.text'))
            </div>
        </div>
    @endforeach

    @if($crud && $crud->buttons()->where('stack', 'line')->count() && ($displayActionsColumn ?? true))
        <div class="datagrid-item">
            <div class="datagrid-title">{{ trans('backpack::crud.actions') }}</div>
            <div class="datagrid-content">
                @include('crud::inc.button_stack', ['stack' => 'line'])
            </div>
        </div>
    @endif
</div>
