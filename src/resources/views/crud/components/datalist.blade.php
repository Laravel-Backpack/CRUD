<table class="table table-striped m-0 p-0">
    <tbody>
        @foreach($columns as $column)
        <tr>
            <td @if($loop->index === 0) class="border-top-0" @endif>
                <strong>{!! $column['label'] !!}@if(!empty($column['label'])):@endif</strong>
            </td>
            <td @if($loop->index === 0) class="border-top-0" @endif>
                @includeFirst(\Backpack\CRUD\ViewNamespaces::getViewPathsWithFallbackFor('columns', $column['type'], 'crud::columns.text'))
            </td>
        </tr>
        @endforeach

        @if($displayButtons && $crud && $crud->buttons()->where('stack', 'line')->count())
        <tr>
            <td>
                <strong>{{ trans('backpack::crud.actions') }}</strong>
            </td>
            <td>
                @include('crud::inc.button_stack', ['stack' => 'line'])
            </td>
        </tr>
        @endif
    </tbody>
</table>
