    {{-- Button to trigger modal --}}
    <button type="button" class="{{ $buttonClass }}" data-toggle="modal" data-bs-toggle="modal" data-target="#formModal{{ md5($controller) }}" data-bs-target="#formModal{{ md5($controller) }}">
        {{ $buttonText }}
    </button>
    {{-- Modal HTML (initially hidden from DOM) --}}
    @push('after_scripts')
    <div class="d-none" id="modalTemplate{{ md5($controller) }}">
        <div class="modal fade" id="formModal{{ md5($controller) }}" tabindex="-1" role="dialog" aria-labelledby="formModalLabel{{ md5($controller) }}" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="formModalLabel{{ md5($controller) }}">{{ $modalTitle }}</h5>
                        <button type="button" class="btn-close close" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close"></button>
                   </div>
                    <div class="modal-body">
                        <div id="modal-form-errors{{ md5($controller) }}" class="alert alert-danger d-none">
                            <ul id="modal-form-errors-list{{ md5($controller) }}"></ul>
                        </div>
                        <div id="modal-form-container{{ md5($controller) }}">
                            <div class="text-center">
                                <i class="fa fa-spinner fa-spin fa-2x"></i>
                                <p>Loading form...</p>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary" id="submitForm{{ md5($controller) }}">Save</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endpush

@push('after_scripts')
<script>
(function() {
    // Create a unique namespace for this modal instance
    const modalNamespace = 'modal_{{ md5($controller) }}';
    
    document.addEventListener('DOMContentLoaded', function() {
    // Listen for modal show event
    const modalEl = document.getElementById('formModal{{ md5($controller) }}');
    const modalTemplate = document.getElementById('modalTemplate{{ md5($controller) }}');

    modalEl.addEventListener('shown.bs.modal', loadModalForm);
    
    function loadModalForm() {
        const formContainer = document.getElementById('modal-form-container{{ md5($controller) }}');
        const submitButton = document.getElementById('submitForm{{ md5($controller) }}');
    
        submitButton.disabled = true;

        // remove the d-none class from the modal not the container
        modalTemplate.classList.remove('d-none');

        submitButton.addEventListener('click', function() {
            submitModalForm{{ md5($controller) }}();
        });

        
        if (formContainer && !formContainer.dataset.loaded) {
            submitButton.disabled = true;
            // Load the form via AJAX
            fetch('{{ url($crud->route . "/create-form") }}', {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.text())
            .then(html => {
                formContainer.innerHTML = html;
                formContainer.dataset.loaded = 'true';
                
                // Initialize the form fields after loading
                if (typeof initializeFieldsWithJavascript === 'function') {
                    try {
                        initializeFieldsWithJavascript(modalEl);
                    } catch (e) {
                        console.error('Error initializing form fields:', e);
                    }
                }
                submitButton.disabled = false;
            })
            .catch(error => {
                console.error('Error loading form:', error);
                formContainer.innerHTML = '<div class="alert alert-danger">Failed to load form</div>';
            });
        }
    }
    });

    // Form submission handler with a unique name
    function submitModalForm{{ md5($controller) }}() {
        const formContainer = document.getElementById('modal-form-container{{ md5($controller) }}');
        const form = formContainer.querySelector('form');
        if (!form) {
            console.error('Form not found in modal');
            return; 
        }
        const errorsContainer = document.getElementById('modal-form-errors{{ md5($controller) }}');
        const errorsList = document.getElementById('modal-form-errors-list{{ md5($controller) }}');
        
        // Clear previous errors
        errorsContainer.classList.add('d-none');
        errorsList.innerHTML = '';
        form.querySelectorAll('.invalid-feedback').forEach(el => el.remove());
        form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));

        // Submit form via AJAX
        fetch(form.action, {
            method: form.method,
            body: new FormData(form),
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json' 
            }
        })
        .then(response => {
            if (response.headers.get('content-type') && response.headers.get('content-type').includes('application/json')) {
                return response.json().then(data => ({ ok: response.ok, data, status: response.status }));
            }
            return response.text().then(text => ({ ok: response.ok, data: text, status: response.status }));
        })
        .then(result => {
            if (result.ok) {
                // Success
                new Noty({
                    type: 'success',
                    text: 'Entry saved successfully!',
                    timeout: 3000
                }).show();
                
                try {
                    const modalEl = document.getElementById('formModal{{ md5($controller) }}');
                    const bsModal = bootstrap.Modal.getInstance(modalEl);
                    if (bsModal) {
                        bsModal.hide();
                    }
                } catch (e) {
                    console.warn('Could not close modal automatically:', e);
                }
                
                // Notify listeners - using a more specific event name
                document.dispatchEvent(new CustomEvent('FormModalSaved_{{ md5($controller) }}', {
                    detail: { controller: '{{ $controller }}', response: result.data }
                }));
                
                // Also dispatch the general event for backward compatibility
                document.dispatchEvent(new CustomEvent('FormModalSaved', {
                    detail: { controller: '{{ $controller }}', response: result.data }
                }));
                
                setTimeout(function() {
                    if (window.crud && window.crud.table) {
                        try {
                            window.crud.table.ajax.reload();
                        } catch (e) {
                            console.warn('Could not reload table using ajax.reload()', e);
                            try {
                                window.crud.table.draw(false);
                            } catch (e2) {
                                console.warn('Could not reload table using draw()', e2);
                            }
                        }
                    }
                }, 100);
            } else if (result.status === 422) {
                // Validation errors
                errorsContainer.classList.remove('d-none');
                
                for (const field in result.data.errors) {
                    result.data.errors[field].forEach(message => {
                        const li = document.createElement('li');
                        li.textContent = message;
                        errorsList.appendChild(li);
                    });
                    
                    const inputField = form.querySelector(`[name="${field}"]`);
                    if (inputField) {
                        inputField.classList.add('is-invalid');
                        
                        const formGroup = inputField.closest('.form-group');
                        if (formGroup) {
                            result.data.errors[field].forEach(message => {
                                const feedback = document.createElement('div');
                                feedback.className = 'invalid-feedback d-block';
                                feedback.textContent = message;
                                formGroup.appendChild(feedback);
                            });
                        }
                    }
                }
            } else {
                errorsContainer.classList.remove('d-none');
                const li = document.createElement('li');
                li.textContent = 'An error occurred while saving the form.';
                errorsList.appendChild(li);
            }
        })
        .catch(error => {
            console.error('Form submission error:', error);
            errorsContainer.classList.remove('d-none');
            const li = document.createElement('li');
            li.textContent = 'A network error occurred.';
            errorsList.appendChild(li);
        });
    }
})(); // End IIFE
</script>
@endpush