{{-- summernote editor --}}
@php
    // make sure that the options array is defined
    // and at the very least, dialogsInBody is true;
    // that's needed for modals to show above the overlay in Bootstrap 4
    $field['options'] = array_merge(['dialogsInBody' => true, 'tooltip' => false], $field['options'] ?? []);
@endphp

@include('crud::fields.inc.wrapper_start')
    <label>{!! $field['label'] !!}</label>
    @include('crud::fields.inc.translatable_icon')
    <textarea
        name="{{ $field['name'] }}"
        data-init-function="bpFieldInitSummernoteElement"
        data-options="{{ json_encode($field['options']) }}"
        data-upload-enabled="{{ isset($field['withFiles']) || isset($field['withMedia']) || isset($field['imageUploadEndpoint']) ? 'true' : 'false'}}"
        data-upload-endpoint="{{ isset($field['imageUploadEndpoint']) ? $field['imageUploadEndpoint'] : 'false'}}"
        data-upload-operation="{{ $crud->get('ajax-upload.formOperation') }}"
        bp-field-main-input
        @include('crud::fields.inc.attributes', ['default_class' =>  'form-control summernote'])
        >{{ old_empty_or_null($field['name'], '') ??  $field['value'] ?? $field['default'] ?? '' }}</textarea>

    {{-- HINT --}}
    @if (isset($field['hint']))
        <p class="help-block">{!! $field['hint'] !!}</p>
    @endif
@include('crud::fields.inc.wrapper_end')


{{-- ########################################## --}}
{{-- Extra CSS and JS for this particular field --}}
{{-- If a field type is shown multiple times on a form, the CSS and JS will only be loaded once --}}

{{-- FIELD CSS - will be loaded in the after_styles section --}}
@push('crud_fields_styles')
    {{-- include summernote css --}}
    @basset('https://unpkg.com/summernote@0.9.1/dist/summernote-lite.min.css')
    @basset('https://unpkg.com/summernote@0.9.1/dist/font/summernote.woff2', false)
    @bassetBlock('backpack/crud/fields/summernote-field.css')
    <style type="text/css">
        .note-editor.note-frame .note-status-output, .note-editor.note-airframe .note-status-output {
                height: auto;
        }

        .note-modal {
            z-index: 1060 !important; /* Higher than Bootstrap's default modal z-index */
        }
    </style>
    @endBassetBlock
@endpush

{{-- FIELD JS - will be loaded in the after_scripts section --}}
@push('crud_fields_scripts')
    {{-- include summernote js --}}
    @basset('https://unpkg.com/summernote@0.9.1/dist/summernote-lite.min.js')
    @bassetBlock('backpack/crud/fields/summernote-field.js')
    <script>
        function bpFieldInitSummernoteElement(element) {
             var summernoteOptions = element.data('options');

            let summernotCallbacks = {
                onChange: function(contents, $editable) {
                    element.val(contents).trigger('change');
                },
            }

            if(element.data('upload-enabled') === true){
                let imageUploadEndpoint =  element.data('upload-endpoint') !== false ? element.data('upload-endpoint') : '{{ url($crud->route. '/ajax-upload') }}';
                let paramName = typeof element.attr('data-repeatable-input-name') !== 'undefined' ? element.closest('[data-repeatable-identifier]').attr('data-repeatable-identifier')+'#'+element.attr('data-repeatable-input-name') : element.attr('name');
                summernotCallbacks.onImageUpload = function(file) {
                    var data = new FormData();
                    data.append(paramName, file[0]);
                    data.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
                    data.append('fieldName', paramName);
                    data.append('operation', element.data('upload-operation'));

                    var xhr = new XMLHttpRequest();
                    xhr.open('POST', imageUploadEndpoint, true);
                    xhr.setRequestHeader('Accept', 'application/json');

                    xhr.onload = function() {
                        if (xhr.status >= 200 && xhr.status < 300) {
                            var response = JSON.parse(xhr.responseText);
                            element.summernote('insertImage', response.data.filePath);
                        } else {
                            var response = JSON.parse(xhr.responseText);
                            let errorBagName = paramName;
                            // it's in a repeatable field
                            if(errorBagName.includes('#')) {
                                errorBagName = errorBagName.replace('#', '.0.');
                            }
                            let errorMessages = typeof response.errors !== 'undefined' ? response.errors[errorBagName].join('<br/>') : response + '<br/>';

                            let summernoteTextarea = element[0];

                            // remove previous error messages
                            summernoteTextarea.parentNode.querySelector('.invalid-feedback')?.remove();

                            // add the red text classes
                            summernoteTextarea.parentNode.classList.add('text-danger');

                            // create the error message container
                            let errorContainer = document.createElement("div");
                            errorContainer.classList.add('invalid-feedback', 'd-block');
                            errorContainer.innerHTML = errorMessages;
                            summernoteTextarea.parentNode.appendChild(errorContainer);
                        }
                    };

                    xhr.onerror = function() {
                        console.error('An error occurred during the upload process');
                    };

                    xhr.send(data);
                }
                
            }

            element.on('CrudField:disable', function(e) {
                element.summernote('disable');
            });

            element.on('CrudField:enable', function(e) {
                element.summernote('enable');
            });

            summernoteOptions['callbacks'] = summernotCallbacks;

            element.summernote(summernoteOptions);
        }
    </script>
    @endBassetBlock
@endpush

{{-- End of Extra CSS and JS --}}
{{-- ########################################## --}}
