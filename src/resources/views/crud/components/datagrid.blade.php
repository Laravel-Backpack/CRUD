<div class="datagrid p-3">
    @foreach($columns as $column)
        <div class="datagrid-item">
            <div class="datagrid-title">{!! $column['label'] !!}</div>
            <div class="datagrid-content">
                @php
                // create a list of paths to column blade views
                // including the configured view_namespaces
                $columnPaths = array_map(function($item) use ($column) {
                    return $item.'.'.$column['type'];
                }, \Backpack\CRUD\ViewNamespaces::getFor('columns'));

                // but always fall back to the stock 'text' column
                // if a view doesn't exist
                if (!in_array('crud::columns.text', $columnPaths)) {
                    $columnPaths[] = 'crud::columns.text';
                }
                @endphp
                @includeFirst($columnPaths)
            </div>
        </div>
    @endforeach

    @if($crud->buttons()->where('stack', 'line')->count() && ($displayActionsColumn ?? true))
        <div class="datagrid-item">
            <div class="datagrid-title">{{ trans('backpack::crud.actions') }}</div>
            <div class="datagrid-content">
                @include('crud::inc.button_stack', ['stack' => 'line'])
            </div>
        </div>
    @endif
</div>
