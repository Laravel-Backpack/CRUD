    {{-- Modal HTML (initially hidden from DOM) --}}
    @push('after_scripts')
    <div class="d-none" id="modalTemplate{{ md5($controller) }}">
        <div class="modal fade" id="{{$id}}" tabindex="-1" role="dialog" aria-labelledby="formModalLabel{{ md5($controller) }}" aria-hidden="true">
            <div class="{{$modalClasses}}" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="formModalLabel{{ md5($controller) }}">{{ $modalTitle }}</h5>
                        <button type="button" class="btn-close close" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close"></button>
                   </div>
                    <div class="modal-body">
                        <div id="modal-form-errors{{ md5($controller) }}" class="alert alert-danger d-none">
                            <ul id="modal-form-errors-list{{ md5($controller) }}"></ul>
                        </div>
                        <div id="modal-form-container{{ md5($controller) }}" data-form-load-route="{{ url($crud->route . '/'.$formRouteOperation) }}">
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
@bassetBlock('form-modal-initialization')

<script>
(function() {
    // This script initializes all modal forms on the page
    document.addEventListener('DOMContentLoaded', function() {
        // Find all modal templates and initialize them
        document.querySelectorAll('[id^="modalTemplate"]').forEach(modalTemplate => {
            // Extract controller hash from the ID
            const controllerId = modalTemplate.id.replace('modalTemplate', '');
            
            // Get the actual modal element
            const modalEl = modalTemplate.querySelector('.modal');
            if (!modalEl) return;
            
            // Get other elements
            const formContainer = document.getElementById(`modal-form-container${controllerId}`);
            const submitButton = document.getElementById(`submitForm${controllerId}`);
            
            if (!formContainer || !submitButton) return;
            
            // Make modal template visible (but modal stays hidden until triggered)
            modalTemplate.classList.remove('d-none');
            
            // Set up event handlers
            modalEl.addEventListener('shown.bs.modal', function() {
                loadModalForm(controllerId, modalEl, formContainer, submitButton);
            });
            
            submitButton.addEventListener('click', function() {
                submitModalForm(controllerId, formContainer, submitButton);
            });
        });
    });
    
    // Load form contents via AJAX
    function loadModalForm(controllerId, modalEl, formContainer, submitButton) {
        submitButton.disabled = true;
        
        if (formContainer && !formContainer.dataset.loaded) {
            // Build URL from current path
            const formUrl = formContainer.dataset.formLoadRoute || modalEl.dataset.formLoadRoute || '';

            fetch(formUrl, {
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
                    console.log('initializing the fields');
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
                submitButton.disabled = false;
            });
        } else {
            submitButton.disabled = false;
        }
    }
    
    // Handle form submission
    function submitModalForm(controllerId, formContainer, submitButton) {
        const form = formContainer.querySelector('form');
        if (!form) {
            console.error('Form not found in modal');
            return; 
        }
        
        const errorsContainer = document.getElementById(`modal-form-errors${controllerId}`);
        const errorsList = document.getElementById(`modal-form-errors-list${controllerId}`);
        
        // Clear previous errors
        errorsContainer.classList.add('d-none');
        errorsList.innerHTML = '';
        form.querySelectorAll('.invalid-feedback').forEach(el => el.remove());
        form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        
        submitButton.disabled = true;
        
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
                
                // Try to close the modal
                try {
                    const modalEl = document.querySelector(`#modalTemplate${controllerId} .modal`);
                    const bsModal = bootstrap.Modal.getInstance(modalEl);
                    if (bsModal) {
                        bsModal.hide();
                    }
                } catch (e) {
                    console.warn('Could not close modal automatically:', e);
                }
                
                // Notify listeners with a specific event for this modal
                document.dispatchEvent(new CustomEvent(`FormModalSaved_${controllerId}`, {
                    detail: { controllerId: controllerId, response: result.data }
                }));
                
                // Also dispatch the general event for backward compatibility
                document.dispatchEvent(new CustomEvent('FormModalSaved', {
                    detail: { controllerId: controllerId, response: result.data }
                }));
                
                // Reload the table if it exists
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
            submitButton.disabled = false;
        })
        .catch(error => {
            console.error('Form submission error:', error);
            errorsContainer.classList.remove('d-none');
            const li = document.createElement('li');
            li.textContent = 'A network error occurred.';
            errorsList.appendChild(li);
            submitButton.disabled = false;
        });
    }
})();
</script>
@endBassetBlock
@endpush