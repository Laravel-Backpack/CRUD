<script>
    if (typeof window.initializeFieldsWithJavascript === 'undefined') {
        window.initializeFieldsWithJavascript = function(container) {
            var selector;
            if (container instanceof jQuery) {
                selector = container;
            } else {
                selector = $(container);
            }
            
            var fieldsToInit = selector.find("[data-init-function]").not("[data-initialized=true]");
            
            fieldsToInit.each(function () {
                var element = $(this);
                var functionName = element.data('init-function');

                if (typeof window[functionName] === "function") {
                    try {
                        window[functionName](element);
                        element.attr('data-initialized', 'true');
                    } catch (error) {
                        element.attr('data-initialized', 'true');
                        console.error('[FieldInit] Error initializing field with function ' + functionName + ':', error);
                    }
                }
            });
        };
    }

    if (!window._select2FocusFixInstalled) {
        document.addEventListener('focusin', function(e) {
            if (e.target.classList.contains('select2-search__field') ||
                e.target.closest('.select2-container') ||
                e.target.closest('.select2-dropdown')) {
                e.stopImmediatePropagation();
            }
        }, true);
        
        window._select2FocusFixInstalled = true;
    }

    if (!window._modalObservers) {
        window._modalObservers = new Map();
    }

    /**
     * Fix select2 fields to work properly in Bootstrap modals
     * Ensures proper positioning even when modal is scrolled or field is in repeatable container
     */
    window.fixSelect2InModal = function($field) {
        if (!$field.hasClass('select2-hidden-accessible')) {
            return;
        }
        
        const $modal = $field.closest('.modal');
        if ($modal.length === 0) {
            return;
        }
        
        const select2Data = $field.data('select2');
        if (!select2Data) {
            return;
        }
        
        const select2Options = select2Data.options.options;
        const $dropdownParent = $(document.body);
        
        if (select2Options.dropdownParent && select2Options.dropdownParent[0] === document.body) {
            attachSelect2PositioningFix($field, $modal);
            return;
        }
        
        $field.select2('destroy');
        select2Options.dropdownParent = $dropdownParent;
        $field.select2(select2Options);
        
        attachSelect2PositioningFix($field, $modal);
    };
    
    /**
     * Attach event handlers to fix Select2 dropdown positioning in modals
     */
    function attachSelect2PositioningFix($field, $modal) {
        $field.off('select2:open.positionFix');
        
        $field.on('select2:open.positionFix', function() {
            setTimeout(function() {
                const $dropdown = $('.select2-dropdown:last');
                if ($dropdown.length) {
                    const modalZIndex = parseInt($modal.css('z-index')) || 1050;
                    $dropdown.css('z-index', modalZIndex + 10);
                    
                    const $container = $field.next('.select2-container');
                    if ($container.length) {
                        const containerOffset = $container.offset();
                        const containerHeight = $container.outerHeight();
                        
                        $dropdown.css({
                            'top': (containerOffset.top + containerHeight) + 'px',
                            'left': containerOffset.left + 'px',
                            'width': $container.outerWidth() + 'px'
                        });
                    }
                }
            }, 1);
        });
    }

    /**
     * Set up MutationObserver for repeatable fields in a specific modal
     * Watches for new rows and initializes their fields automatically
     */
    window.observeModalForRepeatable = function(modalElement) {
        const modalId = modalElement.id;
        
        if (window._modalObservers.has(modalId)) {
            return;
        }

        const modalBody = modalElement.querySelector('.modal-body');
        if (!modalBody) {
            return;
        }

        const processedRows = new Set();
        
        modalBody.querySelectorAll('.repeatable-element').forEach(row => {
            const rowId = row.getAttribute('data-repeatable-holder') + '-' + row.getAttribute('data-row-number');
            processedRows.add(rowId);
        });

        let debounceTimer;
        const observer = new MutationObserver(() => {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                const allRows = modalBody.querySelectorAll('.repeatable-element');
                
                if (allRows.length === 0) return;
                
                allRows.forEach(row => {
                    const rowId = row.getAttribute('data-repeatable-holder') + '-' + row.getAttribute('data-row-number');
                    
                    if (processedRows.has(rowId)) {
                        return;
                    }
                    
                    processedRows.add(rowId);
                    
                    row.querySelectorAll('[data-initialized="true"]').forEach(field => {
                        field.removeAttribute('data-initialized');
                    });
                    
                    const fieldsToInit = row.querySelectorAll('[data-init-function]:not([data-initialized="true"])');
                    
                    fieldsToInit.forEach(field => {
                        const functionName = field.getAttribute('data-init-function');
                        
                        if (typeof window[functionName] === 'function') {
                            try {
                                window[functionName]($(field));
                                field.setAttribute('data-initialized', 'true');
                                
                                if (functionName.includes('select2') || functionName.includes('Relationship')) {
                                    setTimeout(() => {
                                        const $field = $(field);
                                        if ($field.hasClass('select2-hidden-accessible')) {
                                            const select2Data = $field.data('select2');
                                            if (select2Data) {
                                                const oldOptions = select2Data.options.options;
                                                const newOptions = $.extend({}, oldOptions, {
                                                    dropdownParent: $(document.body)
                                                });
                                                
                                                $field.select2('destroy');
                                                $field.select2(newOptions);
                                                
                                                const $modal = $field.closest('.modal');
                                                if ($modal.length) {
                                                    attachSelect2PositioningFix($field, $modal);
                                                }
                                            }
                                        }
                                    }, 50);
                                }
                            } catch (error) {
                                field.setAttribute('data-initialized', 'true');
                                console.error('[RepeatableObserver] Error initializing field:', functionName, error);
                            }
                        }
                    });
                });
            }, 100);
        });

        observer.observe(modalBody, {
            childList: true,
            subtree: true
        });

        window._modalObservers.set(modalId, observer);
    };

    /**
     * Disconnect and remove observer for a modal
     */
    window.disconnectModalObserver = function(modalElement) {
        const modalId = modalElement.id || modalElement.getAttribute('id');
        
        if (window._modalObservers.has(modalId)) {
            const observer = window._modalObservers.get(modalId);
            observer.disconnect();
            window._modalObservers.delete(modalId);
        }
    };

    window.initializeAllModals = function() {
        const initializedModals = new Set();
        
        const modalTemplates = document.querySelectorAll('[id^="modalTemplate"]');
        
        modalTemplates.forEach(modalTemplate => {
        const controllerId = modalTemplate.id.replace('modalTemplate', '');
        const modalEl = modalTemplate.querySelector('.modal');
        if(!modalEl) {
            console.warn(`No modal found in template with ID ${modalTemplate.id}`);
            return;
        }
        
        const modalId = modalEl.id;
        const modalKey = `${controllerId}-${modalId}`;
        
        if (initializedModals.has(modalKey)) {
            modalTemplate.remove();
            return;
        }
        
        initializedModals.add(modalKey);
        
        const formContainer = document.getElementById(`modal-form-container${controllerId}`);
        const submitButton = document.getElementById(`submitForm${controllerId}`);
        if (!formContainer || !submitButton) {
            console.warn(`Missing form elements for controller ID ${controllerId}`);
            return;
        }
        
        modalTemplate.classList.remove('d-none');
        
        if (!modalEl._loadHandler) {
            modalEl._loadHandler = function() {
                loadModalForm(controllerId, modalEl, formContainer, submitButton);
            };
            modalEl.addEventListener('shown.bs.modal', modalEl._loadHandler);
        }

        if (!modalEl._cleanupHandler) {
            modalEl._cleanupHandler = function() {
                disconnectModalObserver(modalEl);
            };
            modalEl.addEventListener('hidden.bs.modal', modalEl._cleanupHandler);
        }
        
        if (!submitButton._saveHandler) {
            submitButton._saveHandler = function() {
                submitModalForm(controllerId, formContainer, submitButton, modalEl);
            };
            submitButton.addEventListener('click', submitButton._saveHandler);
        }
        
        modalTemplate.setAttribute('data-initialized', 'true');
        
        if (typeof bootstrap !== 'undefined' && !bootstrap.Modal.getInstance(modalEl)) {
            new bootstrap.Modal(modalEl);
        }
    });
}
    
function loadModalForm(controllerId, modalEl, formContainer, submitButton) {
    submitButton.disabled = true;
    
    if (formContainer && !formContainer.dataset.loaded) {
        const formUrl = formContainer.dataset.formLoadRoute || modalEl.dataset.formLoadRoute || '';
        const hashedFormId = modalEl.dataset.hashedFormId;
        
        const url = new URL(formUrl, window.location.origin);
        url.searchParams.append('_form_id', hashedFormId);

        fetch(url.toString(), {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            return response.text();
        })
        .then(data => {
            formContainer.innerHTML = data;
            formContainer.dataset.loaded = 'true';
            
            let html = data;
            if (!html) {
                submitButton.disabled = false;
                return;
            }

            if (formContainer.dataset.hasUploadFields === 'true') {
                html = html.replace(/<form /, '<form enctype="multipart/form-data" ');
            }

            const formAction = formContainer.dataset.formAction || '';
            html = html.replace(/<form(\s+[^>]*?)(?:action="[^"]*")?([^>]*?)>/, (match, before, after) => {
                return `<form${before} action="${formAction}"${after}>`;
            });

            formContainer.innerHTML = html;
            formContainer.dataset.loaded = 'true';
            
            const scriptElements = formContainer.querySelectorAll('script');
            const scriptsToLoad = [];
            const inlineScripts = [];

            scriptElements.forEach((scriptElement, index) => {
                if (scriptElement.src) {
                    const srcUrl = scriptElement.src;
                    
                    if (!document.querySelector(`script[src="${srcUrl}"]`)) {
                        scriptsToLoad.push(new Promise((resolve, reject) => {
                            const newScript = document.createElement('script');
                            
                            Array.from(scriptElement.attributes).forEach(attr => {
                                newScript.setAttribute(attr.name, attr.value);
                            });
                            
                            newScript.onload = () => {
                                resolve();
                            };
                            newScript.onerror = (error) => {
                                console.error('[ModalForm] Error loading external script:', srcUrl, error);
                                reject(error);
                            };
                            
                            document.head.appendChild(newScript);
                        }));
                    }
                    
                    scriptElement.parentNode.removeChild(scriptElement);
                } else {
                    const scriptContent = scriptElement.textContent;
                    
                    const scriptData = {
                        content: scriptContent,
                        attributes: Array.from(scriptElement.attributes)
                    };
                    inlineScripts.push(scriptData);
                    
                    scriptElement.parentNode.removeChild(scriptElement);
                }
            });
    
        Promise.all(scriptsToLoad)
            .then(() => {
                inlineScripts.forEach((scriptData, index) => {
                    try {
                        const newScript = document.createElement('script');
                        
                        scriptData.attributes.forEach(attr => {
                            newScript.setAttribute(attr.name, attr.value);
                        });
                        
                        const wrappedContent = `
                            try {
                                ${scriptData.content}
                            } catch (error) {
                                console.error('Error in inline script ${index + 1}:', error);
                            }
                        `;
                        
                        newScript.textContent = wrappedContent;
                        
                        document.head.appendChild(newScript);
                    } catch (e) {
                        console.error('[ModalForm] Error executing inline script ' + (index + 1) + ':', e);
                    }
                });
                
                if (typeof initializeFieldsWithJavascript === 'function') {
                    try {
                        initializeFieldsWithJavascript(modalEl);
                        
                        $(modalEl).find('select.select2-hidden-accessible').each(function() {
                            fixSelect2InModal($(this));
                        });
                        
                        setTimeout(() => {
                            $(modalEl).find('.repeatable-element select.select2-hidden-accessible').each(function() {
                                const $field = $(this);
                                const select2Data = $field.data('select2');
                                
                                if (select2Data) {
                                    const oldOptions = select2Data.options.options;
                                    const newOptions = $.extend({}, oldOptions, {
                                        dropdownParent: $(document.body)
                                    });
                                    
                                    $field.select2('destroy');
                                    $field.select2(newOptions);
                                    
                                    const $modal = $field.closest('.modal');
                                    if ($modal.length) {
                                        attachSelect2PositioningFix($field, $modal);
                                    }
                                }
                            });
                        }, 100);
                        
                        observeModalForRepeatable(modalEl);
                        
                        $(modalEl).find('select[data-field-is-inline="true"]').on('select2:open', function(e) {
                            const $field = $(this);
                            
                            setTimeout(function() {
                                const $container = $field.next('.select2-container--open');
                                
                                const $dropdown = $('.select2-dropdown:visible').last();
                                
                                if ($dropdown.length && $container.length) {
                                    const containerRect = $container[0].getBoundingClientRect();
                                    
                                    $dropdown.css({
                                        'position': 'fixed',
                                        'top': (containerRect.bottom) + 'px',
                                        'left': containerRect.left + 'px',
                                        'width': containerRect.width + 'px',
                                        'z-index': 9999
                                    });
                                    
                                    const $searchInput = $dropdown.find('.select2-search__field');
                                    if ($searchInput.length) {
                                        $searchInput.focus();
                                    }
                                }
                            }, 1);
                        });
                    } catch (e) {
                        console.error('[ModalForm] Error initializing form fields:', e);
                    }
                } else {
                    console.error('[ModalForm] initializeFieldsWithJavascript function not found!');
                }
                
                submitButton.disabled = false;
            })
            .catch(error => {
                console.error('[ModalForm] Error loading external scripts:', error);
                submitButton.disabled = false;
            });
        
        })
        .catch(error => {
            console.error('[ModalForm] Error fetching form:', error);
            formContainer.innerHTML = '<div class="alert alert-danger">Error loading form. Please try again.</div>';
            submitButton.disabled = false;
        });
    } else {
        submitButton.disabled = false;
    }
}

function submitModalForm(controllerId, formContainer, submitButton, modalEl) {
    const form = formContainer.querySelector('form');
    if (!form) {
        return; 
    }

    const errorsContainer = document.getElementById(`modal-form-errors${controllerId}`);
    const errorsList = document.getElementById(`modal-form-errors-list${controllerId}`);

    errorsContainer.classList.add('d-none');
    errorsList.innerHTML = '';
    form.querySelectorAll('.invalid-feedback').forEach(el => el.remove());
    form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
    
    submitButton.disabled = true;

    const formData = new FormData(form);
    if (formContainer.dataset.formMethod) {
        formData.set('_method', formContainer.dataset.formMethod);
    }

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
            new Noty({
                type: 'success',
                text: 'Entry saved successfully!',
                timeout: 3000
            }).show();
            
            try {
                const bsModal = bootstrap.Modal.getInstance(modalEl);
                if (bsModal) {
                    bsModal.hide();
                }
            } catch (e) {
                console.warn('Could not close modal automatically:', e);
            }
            
            document.dispatchEvent(new CustomEvent(`FormModalSaved_${controllerId}`, {
                detail: { controllerId: controllerId, response: result.data }
            }));
            
            document.dispatchEvent(new CustomEvent('FormModalSaved', {
                detail: { controllerId: controllerId, response: result.data }
            }));

            if(formContainer.dataset.refreshDatatable === 'true') {
                setTimeout(function() {
                    try {
                        const triggerButtons = document.querySelectorAll(`[data-bs-target="#${modalEl.id}"], [data-target="#${modalEl.id}"]`);
                        
                        let closestTable = null;
                        triggerButtons.forEach(button => {
                            if (!closestTable) {
                                closestTable = button.closest('table.crud-table, [id^="crudTable"], table[id*="Table"], .dataTables_wrapper table');
                            }
                        });
                        
                        if (!closestTable) {
                            closestTable = document.querySelector('table.crud-table, [id^="crudTable"], table[id*="Table"], .dataTables_wrapper table, table.dataTable');
                        }
                        
                        if (closestTable && closestTable.id) {
                            const tableId = closestTable.id;
                            
                            if (window.crud && window.crud.tables && window.crud.tables[tableId]) {
                                window.crud.tables[tableId].ajax.reload(null, false);
                                return;
                            }
                            
                            if (typeof $ !== 'undefined' && $.fn.DataTable) {
                                const dataTable = $(`#${tableId}`).DataTable();
                                if (dataTable) {
                                    dataTable.ajax.reload(null, false);
                                    return;
                                }
                            }
                            
                            if (typeof DataTable !== 'undefined') {
                                const dataTable = new DataTable(`#${tableId}`);
                                if (dataTable) {
                                    dataTable.ajax.reload();
                                }
                            }
                        }
                    } catch (e) {
                        try {
                            if (typeof table !== 'undefined') {
                                table.draw(false);
                            }
                        } catch (e2) { 
                            console.warn('Could not refresh datatable:', e2);
                        }
                    }
                }, 100);
            }    
            } else if (result.status === 422) {
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

    $(document).ready(function() {
        initializeAllModals();
        
        $(document).on('draw.dt', function(e, settings) {
            setTimeout(initializeAllModals, 100); 
        });
        
        $(document).on('responsive-resize.dt responsive-display.dt', function(e, settings) {
            setTimeout(initializeAllModals, 50);
        });
        
        $(document).on('DOMNodeInserted', function(e) {
            if (e.target.nodeType === 1 && (
                e.target.classList.contains('modal') ||
                e.target.querySelector && e.target.querySelector('.modal') ||
                e.target.classList.contains('crud-table') ||
                e.target.querySelector && e.target.querySelector('table') ||
                e.target.id && e.target.id.toLowerCase().includes('modal') ||
                e.target.id && e.target.id.toLowerCase().includes('table')
            )) {
                setTimeout(initializeAllModals, 50);
            }
        });
    });
    
    </script>
