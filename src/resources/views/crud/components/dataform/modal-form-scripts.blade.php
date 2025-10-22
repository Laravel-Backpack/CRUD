<script>
    @include('crud::components.dataform.common_js')

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
     * Prevent infinite focus loops when third-party overlays (like Colorbox)
     * attempt to grab focus while a Bootstrap modal is open. 
     */
    (function installFocusGuard() {
        if (window._backpackFocusGuardInstalled) return;
        window._backpackFocusGuardInstalled = true;

        // Keep a short-lived map of recently-handled targets to avoid rapid re-entrancy
        const recentFocusTargets = new WeakMap();
        const REENTRANCY_WINDOW_MS = 50;
        // When the page becomes visible again, some libraries fire programmatic
        // focus events immediately. Suppress those for a short window.
        let suppressFocusAfterVisibility = false;
        const VISIBILITY_SUPPRESSION_MS = 200;

        document.addEventListener('visibilitychange', function () {
            if (document.visibilityState === 'visible') {
                // Only suppress if a modal is currently open.
                const anyOpenModal = !!document.querySelector('.modal.show');
                if (!anyOpenModal) return;

                suppressFocusAfterVisibility = true;
                setTimeout(function () { suppressFocusAfterVisibility = false; }, VISIBILITY_SUPPRESSION_MS);
            }
        });

        document.addEventListener('focusin', function (e) {
            try {
                // If there's no Bootstrap modal shown, do nothing.
                const openModal = document.querySelector('.modal.show');
                if (!openModal) return;

                const target = e.target;

                // If the focus is inside the open modal, allow it.
                if (openModal.contains(target)) return;

                if (suppressFocusAfterVisibility) {
                    if (typeof e.stopImmediatePropagation === 'function') e.stopImmediatePropagation();
                    if (typeof e.preventDefault === 'function') e.preventDefault();
                    return;
                }

                // If we've recently handled focus for this target, bail out to prevent loops
                const last = recentFocusTargets.get(target) || 0;
                const now = Date.now();
                if (now - last < REENTRANCY_WINDOW_MS) {
                    if (typeof e.stopImmediatePropagation === 'function') e.stopImmediatePropagation();
                    if (typeof e.preventDefault === 'function') e.preventDefault();
                    return;
                }

                if (!window._backpackFocusGuardLock) {
                    window._backpackFocusGuardLock = false;
                }

                if (window._backpackFocusGuardLock) {
                    // another handler is already restoring focus; bail out
                    if (typeof e.stopImmediatePropagation === 'function') e.stopImmediatePropagation();
                    if (typeof e.preventDefault === 'function') e.preventDefault();
                    return;
                }

                // Mark this target as handled for a short window
                recentFocusTargets.set(target, now);

                // Acquire lock and stop other listeners from handling this focusin to avoid re-entrant focus loops
                window._backpackFocusGuardLock = true;
                if (typeof e.stopImmediatePropagation === 'function') {
                    e.stopImmediatePropagation();
                }

                // restore focus to a sensible element inside the modal
                setTimeout(function () {
                    try {
                        const restore = openModal.querySelector('[autofocus], input, select, textarea, button, [tabindex]:not([tabindex="-1"])');
                        if (restore && typeof restore.focus === 'function') {
                            try { restore.focus(); } catch (err) { /* ignore */ }
                        } else {
                            // fallback: focus the modal itself
                            try { openModal.focus(); } catch (err) { /* ignore */ }
                        }
                    } finally {
                        // release the lock shortly after restoring focus
                        setTimeout(function () { window._backpackFocusGuardLock = false; }, Math.max(REENTRANCY_WINDOW_MS, 50));
                    }
                }, 0);
            } catch (err) {
                // ignore any errors from the guard
            }
        }, true); // capture phase so we can stop propagation before bubble handlers
    })();

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
                                // error initializing repeatable field - suppressed in production
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
    url.searchParams.append('_modal_form_id', hashedFormId);

    // loadModalForm invoked for controllerId, formUrl and hashedFormId

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
            // fetch response received
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

            // HTML injected and scripts extracted

            
            
            const scriptElements = formContainer.querySelectorAll('script');
            const scriptsToLoad = [];
            const inlineScripts = [];

            // helper: watch for a script that timed out and re-run initialization when it finally loads
            if (!window._modalLateWatchers) window._modalLateWatchers = new Map();
            const scheduleLateScriptWatcher = (normalizedSrc, modalEl) => {
                try {
                    if (window._modalLateWatchers.has(normalizedSrc)) return;
                } catch (e) {}

                let attempts = 0;
                const maxAttempts = 5; 
                const checkIntervalMs = 500;

                const id = setInterval(() => {
                    attempts++;
                    try {
                        const found = Array.from(document.querySelectorAll('script[src]')).find(s => {
                            try { return normalize(s.getAttribute('src')) === normalizedSrc; } catch (e) { return false; }
                        });

                        if (found) {
                            // attach events
                            try {
                                const doInit = () => {
                                    try {
                                        try {
                                            if (modalEl && modalEl.querySelectorAll) {
                                                modalEl.querySelectorAll('[data-initialized]').forEach(el => {
                                                    try { el.removeAttribute('data-initialized'); } catch (e) {}
                                                });
                                            }
                                        } catch (e) {}

                                        if (typeof initializeFieldsWithJavascript === 'function') {
                                            initializeFieldsWithJavascript(modalEl);
                                            setTimeout(() => {
                                                try { $(modalEl).find('select.select2-hidden-accessible').each(function() { fixSelect2InModal($(this)); }); } catch (e) {}
                                            }, 50);
                                        }
                                    } catch (e) {}
                                };

                                if (found.readyState && (found.readyState === 'loaded' || found.readyState === 'complete')) {
                                    doInit();
                                } else {
                                    found.addEventListener('load', () => {
                                        doInit();
                                    }, { once: true });
                                }
                            } catch (e) {}

                            clearInterval(id);
                            try { window._modalLateWatchers.delete(normalizedSrc); } catch (e) {}
                            return;
                        }
                    } catch (e) {}

                    if (attempts >= maxAttempts) {
                        clearInterval(id);
                        try { window._modalLateWatchers.delete(normalizedSrc); } catch (e) {}
                    }
                }, checkIntervalMs);

                try { window._modalLateWatchers.set(normalizedSrc, id); } catch (e) {}
            };

            // track normalized srcs seen in this response to avoid processing the same external
            // script multiple times if the AJAX response contained duplicate <script src=...> tags.
            const seenResponseSrcs = new Set();
            scriptElements.forEach((scriptElement, index) => {
                
                if (scriptElement.src) {
                    const srcUrl = scriptElement.src;

                    // Normalize URL to absolute form for reliable comparison
                    const normalize = (u) => {
                        try {
                            return new URL(u, window.location.origin).toString();
                        } catch (e) {
                            return u;
                        }
                    };

                    const normalizedSrc = normalize(srcUrl);

                    // If the same src appears multiple times in the AJAX response, skip duplicates.
                    if (seenResponseSrcs.has(normalizedSrc)) {
                        try { scriptElement.parentNode && scriptElement.parentNode.removeChild(scriptElement); } catch (e) {}
                        return; // continue to next scriptElement
                    }
                    seenResponseSrcs.add(normalizedSrc);

                    const WITH_SCRIPT_TIMEOUT_MS = (window._modalScriptLoaderTimeout && typeof window._modalScriptLoaderTimeout === 'number') ? window._modalScriptLoaderTimeout : 1000;

                    // helper to re-run initialization when a managed promise resolves
                    const attemptReinitOnPromise = (p, modalEl) => {
                        try {
                            if (!p || typeof p.then !== 'function') return;
                            if (p._reinitAttached) return;
                            p._reinitAttached = true;
                            p.then(() => {
                                try {
                                    if (modalEl && modalEl.dataset && modalEl.dataset.modalInitIncomplete === 'true') {
                                        try { modalEl.removeAttribute && modalEl.removeAttribute('data-modal-init-incomplete'); } catch (e) {}
                                        try {
                                            if (typeof initializeFieldsWithJavascript === 'function') {
                                                initializeFieldsWithJavascript(modalEl);
                                                setTimeout(() => {
                                                    try { $(modalEl).find('select.select2-hidden-accessible').each(function() { fixSelect2InModal($(this)); }); } catch (e) {}
                                                }, 50);
                                            }
                                        } catch (e) {}
                                    }
                                } catch (e) {}
                            }, () => {});
                        } catch (e) {}
                    };

                    const wrapPromiseWithTimeout = (p) => Promise.race([p, new Promise(res => setTimeout(() => {
                        try {
                            try { if (modalEl && modalEl.dataset) modalEl.dataset.modalInitIncomplete = 'true'; } catch (ee) {}

                            // If the passed promise is not a managed promise (we can't attach to it), fall back to polling watcher.
                            try {
                                if (!p || !p._modalManaged) {
                                    scheduleLateScriptWatcher(normalizedSrc, modalEl);
                                }
                            } catch (e) {}
                        } catch (e) {}
                        try { if (window._modalScriptLoaderStats) window._modalScriptLoaderStats.timeouts = (window._modalScriptLoaderStats.timeouts || 0) + 1; } catch (e) {}
                        res();
                    }, WITH_SCRIPT_TIMEOUT_MS))]);

                    try {
                        // ensure global maps exist for promises and injection locks
                        if (!window._modalScriptLoaderPromises) window._modalScriptLoaderPromises = new Map();
                        if (!window._modalScriptInjecting) window._modalScriptInjecting = new Map();

                        // enumerate existing script tags and filter out those inside modals
                        let existingScripts = Array.from(document.querySelectorAll('script[src]'));
                        existingScripts = existingScripts.filter(s => {
                            try { return !s.closest || !s.closest('.modal'); } catch (e) { return true; }
                        });

                        const matches = existingScripts.filter(s => {
                            try { return normalize(s.getAttribute('src')) === normalizedSrc; } catch (e) { return s.getAttribute('src') === srcUrl; }
                        });

                            if (matches.length) {
                                

                                try {
                                    // If any matching script is already loaded, skip injecting a duplicate.
                                    const alreadyLoaded = matches.some(s => {
                                        try {
                                            return (s.getAttribute && s.getAttribute('data-modal-loaded') === 'true') || (s.readyState && (s.readyState === 'loaded' || s.readyState === 'complete'));
                                        } catch (e) { return false; }
                                    });

                                    if (alreadyLoaded) {
                                        scriptsToLoad.push(Promise.resolve());
                                        try { scriptElement.parentNode.removeChild(scriptElement); } catch (e) {}
                                        return; // continue to next scriptElement
                                    }

                                    // Reuse existing promise if one is attached to any matching script
                                    let existingPromise = null;
                                    for (const s of matches) {
                                        try { if (s.__modalLoadPromise) { existingPromise = s.__modalLoadPromise; break; } } catch (e) {}
                                    }

                                    if (existingPromise) {
                                        try { if (!window._modalScriptLoaderStats) window._modalScriptLoaderStats = {timeouts:0,reused:0,inFlight:0,waitingMarked:0}; window._modalScriptLoaderStats.reused++; } catch (e) {}
                                        try { existingPromise._modalManaged = true; } catch (e) {}
                                        try { attemptReinitOnPromise(existingPromise, modalEl); } catch (e) {}
                                        scriptsToLoad.push(wrapPromiseWithTimeout(existingPromise));
                                        try { scriptElement.parentNode.removeChild(scriptElement); } catch (e) {}
                                        return; // continue to next scriptElement
                                    }

                                    // If another loader already started injecting or waiting for this src, reuse that promise
                                    if (window._modalScriptInjecting.has(normalizedSrc)) {
                                        const inFlight = window._modalScriptInjecting.get(normalizedSrc);
                                        try { if (!inFlight._modalManaged) try { inFlight._modalManaged = true; } catch (e) {} } catch (e) {}
                                        try { attemptReinitOnPromise(inFlight, modalEl); } catch (e) {}
                                        scriptsToLoad.push(wrapPromiseWithTimeout(inFlight));
                                        try { scriptElement.parentNode.removeChild(scriptElement); } catch (e) {}
                                        return;
                                    }

                                    // Attach a promise to the first existing script and wait for it (and mark it immediately)
                                    const existing = matches[0];
                                    const waitForExisting = new Promise((res, rej) => {
                                        try {
                                            if (existing.readyState && (existing.readyState === 'loaded' || existing.readyState === 'complete')) {
                                                res();
                                                return;
                                            }
                                        } catch (e) {}

                                        existing.addEventListener('load', () => res(), { once: true });
                                        existing.addEventListener('error', (err) => rej(err), { once: true });
                                    });

                                    try {
                                        // mark the existing element as managed so later diagnostics and timeouts see it as ours
                                        try { existing.setAttribute && existing.setAttribute('data-modal-waiting', 'true'); } catch (e) {}
                                        try { existing.setAttribute && existing.setAttribute('data-modal-src', normalizedSrc); } catch (e) {}
                                        try { if (!window._modalScriptLoaderStats) window._modalScriptLoaderStats = {timeouts:0,reused:0,inFlight:0,waitingMarked:0}; window._modalScriptLoaderStats.waitingMarked++; } catch (e) {}

                                        window._modalScriptLoaderPromises.set(normalizedSrc, waitForExisting);
                                        try { existing.__modalLoadPromise = waitForExisting; } catch (e) {}
                                        // mark as 'injecting' so concurrent runs reuse this promise
                                        try { window._modalScriptInjecting.set(normalizedSrc, waitForExisting); } catch (e) {}
                                        try { waitForExisting._modalManaged = true; } catch (e) {}
                                        try { attemptReinitOnPromise(waitForExisting, modalEl); } catch (e) {}
                                        // once settled, remove the injecting lock
                                        waitForExisting.finally(() => {
                                            try { window._modalScriptInjecting.delete(normalizedSrc); } catch (e) {}
                                            try { existing.removeAttribute && existing.removeAttribute('data-modal-waiting'); } catch (e) {}
                                        });
                                    } catch (e) {
                                        // ignore
                                    }

                                    
                                    scriptsToLoad.push(wrapPromiseWithTimeout(waitForExisting));
                                    try { scriptElement.parentNode.removeChild(scriptElement); } catch (e) {}
                                    return; // continue to next scriptElement
                                } catch (e) {
                                    // If reuse fails for any reason, fall back to injecting below
                                }
                            } else {
                                
                            }
                    } catch (e) {
                        // ignore debug failures
                    }

                    scriptsToLoad.push(new Promise((resolve, reject) => {
                        const newScript = document.createElement('script');

                        // If another loader started injecting this src between our checks, reuse its promise instead
                        if (window._modalScriptInjecting && window._modalScriptInjecting.has(normalizedSrc)) {
                            try {
                                const inFlight = window._modalScriptInjecting.get(normalizedSrc);
                                scriptsToLoad.push(inFlight);
                                try { scriptElement.parentNode.removeChild(scriptElement); } catch (e) {}
                                resolve();
                                return;
                            } catch (e) {}
                        }

                        let injectionResolve, injectionReject;
                        const injectionPromise = new Promise((res, rej) => { injectionResolve = res; injectionReject = rej; });
                        try { window._modalScriptInjecting.set(normalizedSrc, injectionPromise); } catch (e) {}
                        try { injectionPromise._modalManaged = true; } catch (e) {}
                        try { attemptReinitOnPromise(injectionPromise, modalEl); } catch (e) {}

                        // carry over attributes from original script element
                        const copiedAttrs = [];
                        Array.from(scriptElement.attributes).forEach(attr => {
                            try { newScript.setAttribute(attr.name, attr.value); } catch (e) {}
                            copiedAttrs.push({ name: attr.name, value: attr.value });
                        });
                        

                        // mark scripts injected
                        try { newScript.setAttribute('data-modal-injected', 'true'); } catch (e) {}
                        try { newScript.setAttribute('data-modal-src', normalizedSrc); } catch (e) {}

                        newScript.onload = () => {
                            try { newScript.setAttribute('data-modal-loaded', 'true'); } catch (e) {}
                            try { injectionResolve(); } catch (e) {}
                            try { window._modalScriptLoaderPromises.set(normalizedSrc, Promise.resolve()); } catch (e) {}
                            try { window._modalScriptInjecting.delete(normalizedSrc); } catch (e) {}
                            resolve();
                        };
                        newScript.onerror = (error) => {
                            try { newScript.setAttribute('data-modal-error', 'true'); } catch (e) {}
                            try { injectionReject(error); } catch (e) {}
                            try { window._modalScriptInjecting.delete(normalizedSrc); } catch (e) {}
                            reject(error);
                        };

                        try {
                            if (!window._modalScriptLoaderPromises) window._modalScriptLoaderPromises = new Map();
                            const pRef = { promise: null };
                            pRef.promise = new Promise((res, rej) => {
                                newScript.addEventListener('load', () => res(), { once: true });
                                newScript.addEventListener('error', (err) => rej(err), { once: true });
                            });
                                newScript.__modalLoadPromise = pRef.promise;
                                try { pRef.promise._modalManaged = true; } catch (e) {}
                                try { attemptReinitOnPromise(pRef.promise, modalEl); } catch (e) {}
                                window._modalScriptLoaderPromises.set(normalizedSrc, pRef.promise);
                        } catch (e) {
                            // ignore
                        }

                        document.head.appendChild(newScript);
                        try {
                        } catch (e) {}
                    }));
             

                        try {
                        scriptElement.parentNode.removeChild(scriptElement);
                    } catch (e) {
                        // ignore
                    }
                } else {
                    const scriptContent = scriptElement.textContent;
                    
                    const scriptData = {
                        content: scriptContent,
                        attributes: Array.from(scriptElement.attributes)
                    };
                    inlineScripts.push(scriptData);
                    
                    try {
                        scriptElement.parentNode.removeChild(scriptElement);
                    } catch (e) {}
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
                        
                        const execScript = document.createElement('script');
                        scriptData.attributes.forEach(attr => execScript.setAttribute(attr.name, attr.value));
                        execScript.textContent = scriptData.content;
                        document.head.appendChild(execScript);
                    } catch (e) {
                        // error executing inline script suppressed
                    }
                });
                
                if (typeof initializeFieldsWithJavascript === 'function') {
                    try {
                        initializeFieldsWithJavascript(modalEl);

                        try {
                            const initElements = modalEl.querySelectorAll('[data-init-function]');
                            initElements.forEach((el, i) => {
                                try {
                                    const initName = el.getAttribute('data-init-function');
                                    const initFunc = window[initName];
                                    // Skip if element already initialized by initializeFieldsWithJavascript or previous run
                                    if (el.getAttribute('data-initialized') === 'true') {
                                        // already initialized
                                    } else if (typeof initFunc === 'function') {
                                        try {
                                            initFunc($(el));
                                            el.setAttribute('data-initialized', 'true');
                                        } catch (err) {
                                            // mark as initialized to avoid repeated failing attempts
                                            el.setAttribute('data-initialized', 'true');
                                        }
                                    }
                                } catch (err) {
                                    // ignore errors when processing init elements
                                }
                            });
                        } catch (err) {
                            // ignore
                        }
                        
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
                        // error initializing form fields suppressed
                    }
                } else {
                    // initializeFieldsWithJavascript not available
                }
                
                submitButton.disabled = false;
            })
            .catch(error => {
                submitButton.disabled = false;
            });
        
        })
        .catch(error => {
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
                // could not close modal automatically
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
                            // could not refresh datatable
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
