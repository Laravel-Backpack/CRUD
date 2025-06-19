<div class="bp-datagrid p-3">
    @foreach($columns as $column)
        <div class="bp-datagrid-item size-{{ $column['size'] ?? '3' }}">
            <div class="bp-datagrid-title">{!! $column['label'] !!}</div>
            <div class="bp-datagrid-content">
                @includeFirst(\Backpack\CRUD\ViewNamespaces::getViewPathsWithFallbackFor('columns', $column['type'], 'crud::columns.text'))
            </div>
        </div>
    @endforeach

    @if($displayButtons && $crud && $crud->buttons()->where('stack', 'line')->count())
        <div class="bp-datagrid-item size-12">
            <div class="bp-datagrid-title">{{ trans('backpack::crud.actions') }}</div>
            <div class="bp-datagrid-content">
                @include('crud::inc.button_stack', ['stack' => 'line'])
            </div>
        </div>
    @endif
</div>
