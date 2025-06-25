<div class="backpack-form">
    @include('crud::inc.grouped_errors', ['id' => $id])


    <form method="post"
        action="{{ $action }}"
        id="{{ $id }}"
        @if(!empty($name)) name="{{ $name }}" @endif
        @if($hasUploadFields) enctype="multipart/form-data" @endif
    >
        {!! csrf_field() !!}

        <input type="hidden" name="_form_id" value="{{ $id }}">
        @if($method !== 'post')
            @method($method)
        @endif
        {{-- Include the form fields --}}
        @include('crud::form_content', ['fields' => $crud->fields(), 'action' => $operation, 'id' => $id])

        {{-- This makes sure that all field assets are loaded. --}}
        <div class="d-none" id="parentLoadedAssets">{{ json_encode(Basset::loaded()) }}</div>

        @include('crud::inc.form_save_buttons')
    </form>
</div>