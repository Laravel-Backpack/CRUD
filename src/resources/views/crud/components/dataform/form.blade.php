<div class="backpack-form">
    @include('crud::inc.grouped_errors')

    <form method="{{ $method }}"
        action="{{ $action }}"
        id="{{ $id }}"
        @if ($crud->hasUploadFields($operation))
        enctype="multipart/form-data"
        @endif
    >
        {!! csrf_field() !!}
        @if($method !== 'post')
            @formMethod($method)
        @endif

        <input type="hidden" name="_form_id" value="{{ $id }}">

        {{-- Include the form fields --}}
        @include('crud::form_content', ['fields' => $crud->fields(), 'action' => $operation, 'id' => $id])

        {{-- This makes sure that all field assets are loaded. --}}
        <div class="d-none" id="parentLoadedAssets">{{ json_encode(Basset::loaded()) }}</div>

        @include('crud::inc.form_save_buttons')
    </form>
</div>

@push('after_scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize the form fields after loading
        if (typeof initializeFieldsWithJavascript === 'function') {
            try {
                initializeFieldsWithJavascript(document.querySelector('.backpack-form'));
            } catch (e) {
                console.error('Error initializing form fields:', e);
            }
        }

        // Focus on first focusable field when form is loaded
        const form = document.querySelector('.backpack-form form');
        if (form) {
            const firstField = form.querySelector('input:not([type=hidden]), select, textarea');
            if (firstField) {
                firstField.focus();
            }
        }
    });
</script>
@endpush