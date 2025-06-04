<div class="card">
    <div class="card-header">
        <h3 class="card-title">{!! $crud->getSubheading() ?? trans('backpack::crud.add').' '.$crud->entity_name !!}</h3>
    </div>
    <div class="card-body">
        <div class="backpack-form">
            @include('crud::inc.grouped_errors')

            <form method="{{ $formMethod }}"
                action="{{ $formAction }}"
                @if ($crud->hasUploadFields($operation))
                enctype="multipart/form-data"
                @endif
            >
                {!! csrf_field() !!}
                @if($formMethod !== 'post')
                    @method($formMethod)
                @endif

                {{-- Include the form fields --}}
                @include('crud::form_content', ['fields' => $crud->fields(), 'action' => $operation])
                
                {{-- This makes sure that all field assets are loaded. --}}
                <div class="d-none" id="parentLoadedAssets">{{ json_encode(Basset::loaded()) }}</div>

                {{-- Include form save buttons --}}
                @if(!isset($hideButtons) || !$hideButtons)
                    <div class="form-group mt-3">
                        <button type="submit" class="btn btn-success">
                            <span class="la la-save" role="presentation" aria-hidden="true"></span> &nbsp;
                            <span>{{ trans('backpack::crud.save') }}</span>
                        </button>
                        <a href="{{ url()->previous() }}" class="btn btn-secondary">{{ trans('backpack::crud.cancel') }}</a>
                    </div>
                @endif
            </form>
        </div>
    </div>
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