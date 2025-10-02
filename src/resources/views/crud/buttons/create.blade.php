@if ($crud->hasAccess('create'))
    @php
        $createButtonWithModalForm = $crud->getOperationSetting('createButtonWithModalForm', 'create') ?? false;
        $controllerClass = get_class(app('request')->route()->getController());
    @endphp
    
    @if($createButtonWithModalForm)
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
    @else
        {{-- Regular create button that redirects to create page --}}
        <a href="{{ url($crud->route.'/create') }}" class="btn btn-primary" bp-button="create" data-style="zoom-in">
            <i class="la la-plus"></i> <span>{{ trans('backpack::crud.add') }} {{ $crud->entity_name }}</span>
        </a>
    @endif
@endif