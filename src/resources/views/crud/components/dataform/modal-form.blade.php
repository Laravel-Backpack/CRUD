{{-- Modal HTML (initially hidden from DOM) --}}
@php
    if(isset($formRouteOperation)) {
        if(!\Str::isUrl($formRouteOperation)) {
            $formRouteOperation = url($crud->route . '/' . $formRouteOperation);
        }
    }
@endphp
@push('after_scripts') @if (request()->ajax()) @endpush @endif
    <div class="d-none" id="modalTemplate{{ md5($controller.$id) }}">
        <div class="modal fade" id="{{$id}}" tabindex="-1" role="dialog" data-bs-backdrop="static" data-backdrop="static" aria-labelledby="formModalLabel{{ md5($controller.$id) }}" aria-hidden="true">
            <div class="{{$classes}}" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="formModalLabel{{ md5($controller.$id) }}">{{ $title }}</h5>
                        <button type="button" class="btn-close close" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close"></button>
                   </div>
                    <div class="modal-body bg-light">
                        <div id="modal-form-errors{{ md5($controller.$id) }}" class="alert alert-danger d-none">
                            <ul id="modal-form-errors-list{{ md5($controller.$id) }}"></ul>
                        </div>
                        <div 
                            id="modal-form-container{{ md5($controller.$id) }}" 
                            data-form-load-route="{{ $formRouteOperation }}"
                            data-form-action="{{ $action }}"
                            data-form-method="{{ $method }}"
                            data-has-upload-fields="{{ $hasUploadFields ? 'true' : 'false' }}"
                            data-refresh-datatable="{{ $refreshDatatable ? 'true' : 'false' }}"
                            >
                            <div class="text-center">
                                <i class="fa fa-spinner fa-spin fa-2x"></i>
                                <p>Loading form...</p>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary" id="submitForm{{ md5($controller.$id) }}">Save</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@if (!request()->ajax()) @endpush @endif
@push('after_scripts') @if (request()->ajax()) @endpush @endif
<script>
    !(function() {
    // Initialize modals immediately when the script runs
    initializeAllModals();
    // Also listen for DataTable draw events which might add new modals
    document.addEventListener('draw.dt', initializeAllModals);
})();
    function initializeAllModals() {
    // First, track all initialized modals by their unique ID to avoid duplicates
    const initializedModals = new Set();
    
    document.querySelectorAll('[id^="modalTemplate"]').forEach(modalTemplate => {
        // Extract controller hash from the ID
        const controllerId = modalTemplate.id.replace('modalTemplate', '');
        const modalEl = modalTemplate.querySelector('.modal');
        if(!modalEl) {
            console.warn(`No modal found in template with ID ${modalTemplate.id}`);
            return;
        }
        
        const modalId = modalEl.id;
        const scrollPosition = window.pageYOffset || document.documentElement.scrollTop;
        
        // Create a unique key for this modal
        const modalKey = `${controllerId}-${modalId}`;
        
        // Skip if we've already processed an identical modal in this batch
        if (initializedModals.has(modalKey)) {
            modalTemplate.remove(); // Remove duplicate
            return;
        }
        
        initializedModals.add(modalKey);
        
        // Get other elements
        const formContainer = document.getElementById(`modal-form-container${controllerId}`);
        const submitButton = document.getElementById(`submitForm${controllerId}`);
        if (!formContainer || !submitButton) {
            console.warn(`Missing form elements for controller ID ${controllerId}`);
            return;
        }
        
        // Make modal template visible (but modal stays hidden until triggered)
        modalTemplate.classList.remove('d-none');
        
        // Only set up the event handlers if they don't exist yet
        if (!modalEl._loadHandler) {
            modalEl._loadHandler = function() {
                loadModalForm(controllerId, modalEl, formContainer, submitButton, scrollPosition);
            };
            modalEl.addEventListener('shown.bs.modal', modalEl._loadHandler);
        }
        
        if (!submitButton._saveHandler) {
            submitButton._saveHandler = function() {
                submitModalForm(controllerId, formContainer, submitButton, modalEl);
            };
            submitButton.addEventListener('click', submitButton._saveHandler);
        }
        
        // Mark as initialized
        modalTemplate.setAttribute('data-initialized', 'true');
        
        // Initialize Bootstrap modal if it hasn't been initialized yet
        if (typeof bootstrap !== 'undefined' && !bootstrap.Modal.getInstance(modalEl)) {
            new bootstrap.Modal(modalEl);
        }
    });
}
    
// Load form contents via AJAX
function loadModalForm(controllerId, modalEl, formContainer, submitButton, scrollPosition) {
    submitButton.disabled = true;
    window.scrollTo(0, scrollPosition);
    
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
            if (!html) {
                console.error(`No HTML content returned for controller ID ${controllerId}`);
                return;
            }

            // add the enctype to the form if it has upload fields
            if (formContainer.dataset.hasUploadFields === 'true') {
                html = html.replace(/<form /, '<form enctype="multipart/form-data" ');
            }

            // Replace form action in the HTML
            const formAction = formContainer.dataset.formAction || '';
            html = html.replace(/<form(\s+[^>]*?)(?:action="[^"]*")?([^>]*?)>/, (match, before, after) => {
                return `<form${before} action="${formAction}"${after}>`;
            });

            formContainer.innerHTML = html;
            formContainer.dataset.loaded = 'true';
            
            // Handle any scripts that came with the response
            const scriptElements = formContainer.querySelectorAll('script');
            const scriptsToLoad = [];

            scriptElements.forEach(scriptElement => {
                if (scriptElement.src) {
                    // For external scripts with src attribute
                    const srcUrl = scriptElement.src;
                    
                    // Only load the script if it's not already loaded
                    if (!document.querySelector(`script[src="${srcUrl}"]`)) {
                        scriptsToLoad.push(new Promise((resolve, reject) => {
                            const newScript = document.createElement('script');
                            
                            // Copy all attributes from the original script
                            Array.from(scriptElement.attributes).forEach(attr => {
                                newScript.setAttribute(attr.name, attr.value);
                            });
                            
                            // Set up load and error handlers
                            newScript.onload = resolve;
                            newScript.onerror = reject;
                            
                            // Append to document to start loading
                            document.head.appendChild(newScript);
                        }));
                    }
                    
                    // Remove the original script tag
                    scriptElement.parentNode.removeChild(scriptElement);
                } else {
                    // For inline scripts
                    const newScript = document.createElement('script');
                    
                    // Copy all attributes from the original script
                    Array.from(scriptElement.attributes).forEach(attr => {
                        newScript.setAttribute(attr.name, attr.value);
                    });
                    
                    // Copy the content
                    newScript.textContent = scriptElement.textContent;

                    try {
                        document.head.appendChild(newScript);
                    }catch (e) {
                        console.warn('Error appending inline script:', e);
                    }
                }
            });
    
        // Wait for all external scripts to load before continuing
        Promise.all(scriptsToLoad)
            .then(() => {
                // Initialize the form fields after all scripts are loaded
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
                submitButton.disabled = false;
            });
        
        });
    }else{
        submitButton.disabled = false;
    }
}

// Handle form submission
function submitModalForm(controllerId, formContainer, submitButton, modalEl) {
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

    const formData = new FormData(form);
    // change the form data _method to the one defined in the container
    if (formContainer.dataset.formMethod) {
        formData.set('_method', formContainer.dataset.formMethod);
    }

    // Submit form via AJAX
    fetch(form.action, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
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

            // Reload the datatable if developer asked for it
            if(formContainer.dataset.refreshDatatable === 'true') {
                setTimeout(function() {
                    try {
                        // Find closest DataTable
                        const triggerButton = document.querySelector(`[data-target="#${modalEl.id}"]`);
                        const closestTable = triggerButton ? triggerButton.closest('.dataTable') : null;    
                        if (closestTable && closestTable.id) {
                            // Access the DataTable instance using the DataTables API
                            const dataTable = window.DataTable.tables({ visible: true, api: true }).filter(
                                table => table.getAttribute('id') === closestTable.id
                            );
                            if (dataTable) {
                                dataTable.ajax.reload();
                            }
                        }
                    } catch (e) {
                        try {
                            // Fallback approach if first method fails
                            if (typeof table !== 'undefined') {
                                table.draw(false);
                            }
                        } catch (e2) { }
                    }
                }, 100);
            }    
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
    </script>
@if (!request()->ajax()) @endpush @endif