@if ($crud->hasAccess('update', $entry))
    @php
        $controllerClass = $crud->controller ?? get_class(app('request')->route()->getController());
    @endphp
    
    {{-- Update button that opens modal form --}}
    @if (!$crud->model->translationEnabled())
        <button 
            type="button" 
            class="btn btn-sm btn-link" 
            data-bs-toggle="modal"
            data-bs-target="#updateModal{{ md5($controllerClass.'update'.$entry->getKey()) }}"
            bp-button="update"
        >
            <i class="la la-edit"></i> <span>{{ trans('backpack::crud.edit') }}</span>
        </button>
    @else
        {{-- Edit button group for translated models --}}
        <div class="btn-group">
            <button 
                type="button" 
                class="btn btn-sm btn-link pr-0" 
                data-bs-toggle="modal"
                data-bs-target="#updateModal{{ md5($controllerClass.'update'.$entry->getKey()) }}"
            >
                <span><i class="la la-edit"></i> {{ trans('backpack::crud.edit') }}</span>
            </button>
            <a class="btn btn-sm btn-link dropdown-toggle text-primary pl-1" data-toggle="dropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <span class="caret"></span>
            </a>
            <ul class="dropdown-menu dropdown-menu-right">
                <li class="dropdown-header">{{ trans('backpack::crud.edit_translations') }}:</li>
                @foreach ($crud->model->getAvailableLocales() as $key => $locale)
                    <li><a class="dropdown-item" href="{{ url($crud->route.'/'.$entry->getKey().'/edit') }}?_locale={{ $key }}">{{ $locale }}</a></li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Include the modal form component --}}
    <x-backpack::dataform-modal
        :controller="$controllerClass"
        formOperation="update"
        :entry="$entry"
        formId="updateModal{{ md5($controllerClass.'update'.$entry->getKey()) }}"
        :title="trans('backpack::crud.edit') . ' ' . $crud->entity_name"
        refresh-datatable="true"
    />
@endif
