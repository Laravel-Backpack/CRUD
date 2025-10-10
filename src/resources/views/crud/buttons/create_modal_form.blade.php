@if ($crud->hasAccess('create'))
    @php
        $controllerClass = get_class(app('request')->route()->getController());
    @endphp
    
    {{-- Create button that opens modal form --}}
    <button 
        type="button" 
        class="btn btn-primary" 
        data-bs-toggle="modal"
        data-bs-target="#createModal{{ md5($controllerClass.'create') }}"
        bp-button="create" 
        data-style="zoom-in"
    >
        <i class="la la-plus"></i> <span>{{ trans('backpack::crud.add') }} {{ $crud->entity_name }}</span>
    </button>

    {{-- Include the modal form component --}}
    <x-backpack::dataform-modal
        :controller="$controllerClass"
        operation="create"
        id="createModal{{ md5($controllerClass.'create') }}"
        :title="trans('backpack::crud.add') . ' ' . $crud->entity_name"
        refresh-datatable="true"
    />
@endif
