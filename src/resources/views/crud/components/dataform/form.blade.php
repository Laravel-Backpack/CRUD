<div class="backpack-form">
    @include('crud::inc.grouped_errors', ['formId' => $formId])

    <form method="post"
        action="{{ $formAction }}"
        id="{{ $formId }}"
        @if($hasUploadFields) enctype="multipart/form-data" @endif
    >
        {!! csrf_field() !!}
        @if($formMethod !== 'post')
            @method($formMethod)
        @endif
        {{-- Include the form fields --}}
        @include('crud::form_content', ['fields' => $crud->fields(), 'action' => $formOperation, 'formId' => $formId])

        {{-- This makes sure that all field assets are loaded. --}}
        <div class="d-none" id="parentLoadedAssets">{{ json_encode(Basset::loaded()) }}</div>

        @include('crud::inc.form_save_buttons')
    </form>
</div>