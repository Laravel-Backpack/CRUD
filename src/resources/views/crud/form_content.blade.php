<input type="hidden" name="_http_referrer" value="{{ session('referrer_url_override') ?? old('_http_referrer') ?? \URL::previous() ?? url($crud->route) }}">
<input type="hidden" name="_form_id" value="{{ $formId ?? $id ?? 'crudForm' }}">

{{-- See if we're using tabs --}}
@if ($crud->tabsEnabled() && count($crud->getTabs()))
    @include('crud::inc.show_tabbed_fields')
    <input type="hidden" name="_current_tab" value="{{ Str::slug($crud->getTabs()[0]) }}" />
@else
  <div class="{{isset($formInsideCard) && $formInsideCard ? 'card' : (!isset($formInsideCard) ? 'card' : '')}}">
    <div class="{{isset($formInsideCard) && $formInsideCard ? 'card-body' : (!isset($formInsideCard) ? 'card-body' : '')}} row">
      @include('crud::inc.show_fields', ['fields' => $crud->fields()])
    </div>
  </div>
@endif

@foreach (app('widgets')->toArray() as $currentWidget)
@php
    $currentWidget = \Backpack\CRUD\app\Library\Widget::add($currentWidget);
@endphp
    @if($currentWidget->getAttribute('inline'))
        @include($currentWidget->getFinalViewPath(), ['widget' => $currentWidget->toArray()])
    @endif
@endforeach

{{-- Define blade stacks so css and js can be pushed from the fields to these sections. --}}

@push('after_styles')

    {{-- CRUD FORM CONTENT - crud_fields_styles stack --}}
    @stack('crud_fields_styles')

@endpush

@push('before_scripts')
  @include('crud::inc.form_fields_script')
@endpush

@push('after_scripts')

    {{-- CRUD FORM CONTENT - crud_fields_scripts stack --}}
    @stack('crud_fields_scripts')

    <script>
    @include('crud::components.dataform.common_js')

    jQuery('document').ready(function($){

      @if(! isset($initFields) || $initFields !== false)
        initializeFieldsWithJavascript('form');
      @endif

      // Retrieves the current form data
      function getFormData() {
        let formData = new FormData(document.querySelector("main form"));
        // remove internal inputs from formData, the ones that start with "_", like _token, _http_referrer, etc.
        let pairs = [...formData].map(pair => pair[0]);
        for (let pair of pairs) {
          if (pair.startsWith('_')) {
            formData.delete(pair);
          }
        }
        return new URLSearchParams(formData).toString();
      }

      // Prevents unloading of page if form data was changed
      function preventUnload(event) {
        if (initData !== getFormData()) {
          // Cancel the event as stated by the standard.
          event.preventDefault();
          // Older browsers supported custom message
          event.returnValue = '';
        }
      }

      @if($crud->getOperationSetting('warnBeforeLeaving'))
        const initData = getFormData();
        window.addEventListener('beforeunload', preventUnload);
      @endif

      // Save button has multiple actions: save and exit, save and edit, save and new
      document.querySelectorAll('form').forEach(function(form) {
          if (form.querySelector('.saveActions')) {
              // prevent duplicate entries on double-clicking the submit form
              form.addEventListener('submit', function(event) {
                  window.removeEventListener('beforeunload', preventUnload);
                  const submitButtons = form.querySelectorAll('button[type=submit]');
                  submitButtons.forEach(button => button.disabled = true);
              });
          }
      });
      
      // Ctrl+S and Cmd+S trigger Save button click
      document.addEventListener('keydown', function(e) {
          if ((e.which === 115 || e.which === 83) && (e.ctrlKey || e.metaKey)) {
              e.preventDefault();
              
              // Find the form that contains the currently focused element
              let activeForm = null;
              const focusedElement = document.activeElement;
              
              if (focusedElement) {
                  activeForm = focusedElement.closest('form');
                  // Check if this form has saveActions
                  if (!activeForm || !activeForm.querySelector('.saveActions')) {
                      activeForm = null;
                  }
              }
              
              // If no focused form with save actions, use the first form with save actions
              if (!activeForm) {
                  const formsWithSaveActions = document.querySelectorAll('form');
                  for (let form of formsWithSaveActions) {
                      if (form.querySelector('.saveActions')) {
                          activeForm = form;
                          break;
                      }
                  }
              }
              
              if (activeForm) {
                  const submitButton = activeForm.querySelector('.saveActions button[type=submit]');
                  
                  if (submitButton) {
                      submitButton.click();
                  } else {
                      // Create and dispatch a submit event
                      const submitEvent = new Event('submit', {
                          bubbles: true,
                          cancelable: true
                      });
                      activeForm.dispatchEvent(submitEvent);
                  }
              }
              return false;
          }
          return true;
      });

      // Place the focus on the first element in the form
      @if( $crud->getAutoFocusOnFirstField() )
        @php
          $focusField = Arr::first($fields, function($field) {
              return isset($field['auto_focus']) && $field['auto_focus'] === true;
          });
        @endphp

        let focusField, focusFieldTab;

        @if ($focusField)
          @php
            $focusFieldName = isset($focusField['value']) && is_iterable($focusField['value']) ? $focusField['name'] . '[]' : $focusField['name'];
            $focusFieldTab = $focusField['tab'] ?? null;
          @endphp
            focusFieldTab = '{{ Str::slug($focusFieldTab) }}';

                // if focus is not 'null' navigate to that tab before focusing.
                if(focusFieldTab !== 'null'){
                  try {
                    // find the form id stored in the hidden input within this form instance
                    const currentFormEl = focusField.closest('form');
                    const formIdInput = currentFormEl ? currentFormEl.querySelector('input[name="_form_id"]') : null;
                    const theFormId = formIdInput ? formIdInput.value : ('{{ $formId ?? 'crudForm' }}');
                    const selector = `#form_tabs[data-form-id="${theFormId}"] a[tab_name="${focusFieldTab}"]`;
                    var tabEl = document.querySelector(selector);
                    if (tabEl) { bootstrap.Tab.getOrCreateInstance(tabEl).show(); }
                  } catch (e) {
                    // fallback to global selector
                    var tabEl = document.querySelector('#form_tabs a[tab_name="'+focusFieldTab+'"]');
                    if (tabEl) { bootstrap.Tab.getOrCreateInstance(tabEl).show(); }
                  }
                }
            focusField = document.querySelector('[name="{{ $focusFieldName }}"]');
        @else
            focusField = getFirstFocusableField(document.querySelector('form'));
        @endif
        if(focusField) {
          const fieldOffset = focusField.getBoundingClientRect().top + window.scrollY;
          const scrollTolerance = window.innerHeight / 2;

          triggerFocusOnFirstInputField(focusField);

          if( fieldOffset > scrollTolerance ){
              window.scrollTo({ top: fieldOffset - 30, behavior: 'smooth' });
          }
        }
      @endif

      // Add inline errors to the DOM
      @if ($crud->inlineErrorsEnabled() && session()->get('errors'))

        window.errors = {!! json_encode(session()->get('errors')->getBags()) !!};
        var submittedFormId = "{{ old('_form_id') ?? 'crudForm' }}";
        var currentFormId = '{{ $formId ?? $id ?? 'crudForm' }}';

        // Only display errors if this is the form that was submitted
        if (submittedFormId && submittedFormId === currentFormId) {
          var firstErrorField = null;
          var firstErrorTab = null;
          
          Object.entries(errors).forEach(function(bagEntry) {
            var errorMessages = bagEntry[1];
            Object.entries(errorMessages).forEach(function(msgEntry) {
              var inputName = msgEntry[0];
              var messages = msgEntry[1];
              var normalizedProperty = inputName.split('.').map(function(item, index){
                      return index === 0 ? item : '['+item+']';
                  }).join('');

              // Only select fields within the current form
              var field = document.querySelector('#' + currentFormId + ' [name="' + normalizedProperty + '[]"]') ||
                          document.querySelector('#' + currentFormId + ' [name="' + normalizedProperty + '"]');
              if (!field) return;
              var container = field.closest('.form-group');
              if (!container) return;

              // Store the first error field for focusing
              if (firstErrorField === null) {
                firstErrorField = field;
                @if ($crud->tabsEnabled())
                var tab_container = container.closest('[role="tabpanel"]');
                if (tab_container) {
                  firstErrorTab = tab_container.getAttribute('id');
                }
                @endif
              }

              // iterate the inputs to add invalid classes to fields and red text to the field container.
              container.querySelectorAll('input, textarea, select').forEach(function(containerField) {
                  containerField.classList.add('is-invalid');
                  let fieldContainer = containerField.closest('.form-group');
                  if (fieldContainer && !fieldContainer.classList.contains('repeatable-group') && !fieldContainer.classList.contains('no-error-display')) {
                    fieldContainer.classList.add('text-danger');
                  }
              });

              messages.forEach(function(msg) {
                  var row = document.createElement('div');
                  row.className = 'invalid-feedback d-block';
                  row.textContent = msg;

                  if(!container.classList.contains('repeatable-group') && !container.classList.contains('no-error-display')){
                    container.append(row);
                  }

                  // highlight its parent tab
                  @if ($crud->tabsEnabled())
                    var tab_id = container.closest('[role="tabpanel"]')?.getAttribute('id');
                    if (tab_id) {
                      try {
                        var tabLink = document.querySelector('#form_tabs[data-form-id="' + (typeof currentFormId !== 'undefined' ? currentFormId : '{{ $formId ?? 'crudForm' }}') + '"] [aria-controls="'+tab_id+'"]');
                        if (tabLink) tabLink.classList.add('text-danger');
                      } catch (e) {
                        var tabLink = document.querySelector('#form_tabs [aria-controls="'+tab_id+'"]');
                        if (tabLink) tabLink.classList.add('text-danger');
                      }
                    }
                  @endif
              });
            });
          });

          // Focus on the first error field
          if (firstErrorField !== null) {
            @if ($crud->tabsEnabled())
            // Switch to the tab containing the first error if needed
            if (firstErrorTab) {
              try {
                  var tabSelector = '#form_tabs[data-form-id="' + (typeof currentFormId !== 'undefined' ? currentFormId : '{{ $formId ?? 'crudForm' }}') + '"] .nav a[href="#' + firstErrorTab + '"]';
                  var tabLink = document.querySelector(tabSelector);
                  if (tabLink) { bootstrap.Tab.getOrCreateInstance(tabLink).show(); }
              } catch (e) {
                  var tabLink = document.querySelector('.nav a[href="#' + firstErrorTab + '"]');
                  if (tabLink) { bootstrap.Tab.getOrCreateInstance(tabLink).show(); }
              }
            }
            @endif
            
            // Focus on the first error field
            setTimeout(function() {
              const fieldOffset = firstErrorField.getBoundingClientRect().top + window.scrollY;
              const scrollTolerance = window.innerHeight / 2;
              
              triggerFocusOnFirstInputField(firstErrorField);
              
              if (fieldOffset > scrollTolerance) {
                window.scrollTo({ top: fieldOffset - 30, behavior: 'smooth' });
              }
            }, 100);
          }
        }
      @endif

      // Track current tab in hidden input
      document.querySelectorAll("a[data-bs-toggle='tab']").forEach(function(tabLink) {
          tabLink.addEventListener('click', function() {
              var currentTabName = this.getAttribute('tab_name');
              var currentTabInput = document.querySelector("input[name='_current_tab']");
              if (currentTabInput) currentTabInput.value = currentTabName;
          });
      });

      if (window.location.hash) {
          var currentTabInput = document.querySelector("input[name='_current_tab']");
          if (currentTabInput) currentTabInput.value = window.location.hash.substr(1);
      }
      });
    </script>
@endpush