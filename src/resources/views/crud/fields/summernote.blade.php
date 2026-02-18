{{-- summernote editor --}}
@php
    // make sure that the option array is defined,
    // and at the very least, dialogsInBody is true;
    // that's needed for modals to show above the overlay in Bootstrap 4
    $field['options'] = array_merge(['dialogsInBody' => true, 'tooltip' => false], $field['options'] ?? []);

$themeNamespace = config('backpack.ui.view_namespace') ?? config('backpack.ui.view_namespace_fallback', '');

$summernoteTheme = match ($themeNamespace) {
    'backpack.theme-coreuiv4::', 'backpack.theme-tabler::' => 'bs5',
    'backpack.theme-coreuiv2::' => 'bs4',
    default => 'lite',
};

$locales = [
    'en' => 'en-US',
    'ko' => 'ko-KR',
    'vi' => 'vi-VN',
];

$supportedLanguages = [
    'ar-AR',
    'az-AZ',
    'bg-BG',
    'bn-BD',
    'ca-ES',
    'cs-CZ',
    'da-DK',
    'de-CH',
    'el-GR',
    'en-US',
    'es-ES',
    'es-EU',
    'fa-IR',
    'fi-FI',
    'fr-FR',
    'gl-ES',
    'he-IL',
    'hr-HR',
    'hu-HU',
    'id-ID',
    'it-IT',
    'ja-JP',
    'ko-KR',
    'lt-LT',
    'lv-LV',
    'mn-MN',
    'nb-NO',
    'nl-NL',
    'pl-PL',
    'pt-BR',
    'pt-PT',
    'ro-RO',
    'ru-RU',
    'sk-SK',
    'sl-SI',
    'sr-RS-Latin',
    'sr-RS',
    'sv-SE',
    'ta-IN',
    'th-TH',
    'tr-TR',
    'uk-UA',
    'uz-UZ',
    'vi-VN',
    'zh-CN',
    'zh-TW',
];

$fullLocale = $locales[app()->getLocale()] ?? 'en-US';

if (!in_array($fullLocale, $supportedLanguages)) {
    $fullLocale = 'en-US';
    }
@endphp

@include('crud::fields.inc.wrapper_start')
<label>{!! $field['label'] !!}</label>
@include('crud::fields.inc.translatable_icon')
<textarea name="{{ $field['name'] }}" data-init-function="bpFieldInitSummernoteElement"
    data-options="{{ json_encode($field['options']) }}"
    data-upload-enabled="{{ isset($field['withFiles']) || isset($field['withMedia']) || isset($field['imageUploadEndpoint']) ? 'true' : 'false' }}"
    data-upload-endpoint="{{ isset($field['imageUploadEndpoint']) ? $field['imageUploadEndpoint'] : 'false' }}"
    data-ajax-upload-endpoint="{{ url($crud->route . '/ajax-upload') }}"
    data-upload-operation="{{ $crud->get('ajax-upload.formOperation') }}" bp-field-main-input
    @include('crud::fields.inc.attributes', ['default_class' => 'form-control summernote'])>{{ old_empty_or_null($field['name'], '') ?? ($field['value'] ?? ($field['default'] ?? '')) }}</textarea>

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
    @basset('https://cdn.jsdelivr.net/npm/summernote@0.9.1/dist/summernote-{{ $summernoteTheme }}.min.css')
    @basset('https://cdn.jsdelivr.net/npm/summernote@0.9.1/dist/font/summernote.woff2', false)
    @bassetBlock('backpack/crud/fields/summernote-field.css')
        <style type="text/css">
            .note-editor.note-frame .note-status-output,
            .note-editor.note-airframe .note-status-output {
                height: auto;
            }

            .note-modal {
                z-index: 1060 !important;
                /* Higher than Bootstrap's default modal z-index */
            }
        </style>
    @endBassetBlock
@endpush

{{-- FIELD JS - will be loaded in the after_scripts section --}}
@push('crud_fields_scripts')
    {{-- include summernote js --}}
    @basset('https://cdn.jsdelivr.net/npm/summernote@0.9.1/dist/summernote-{{ $summernoteTheme }}.min.js')
    @if ($fullLocale !== 'en-US')
        @basset('https://cdn.jsdelivr.net/npm/summernote@0.9.1/dist/lang/summernote-{{ $fullLocale }}.js')
    @endif
    @bassetBlock('backpack/crud/fields/summernote-field.js')
        <script>
            function bpFieldInitSummernoteElement(element) {
                let summernoteOptions = element.data('options');
                summernoteOptions.lang = '{{ $fullLocale }}';

                let summernoteCallbacks = {
                    onChange: function(contents, $editable) {
                        element.val(contents).trigger('change');
                    },
                }

                if (element.data('upload-enabled')) {
                    const imageUploadEndpoint = element.data('upload-endpoint') !== false ? element.data('upload-endpoint') :
                        element.data('ajax-upload-endpoint');

                    const paramName = typeof element.attr('data-repeatable-input-name') !== 'undefined' ?
                        element.closest('[data-repeatable-identifier]').attr('data-repeatable-identifier') + '#' + element.attr(
                            'data-repeatable-input-name') : element.attr('name');

                    summernoteCallbacks.onImageUpload = function(files) {
                        const data = new FormData();
                        data.append(paramName, files[0]);
                        data.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
                        data.append('fieldName', paramName);
                        data.append('operation', element.data('upload-operation'));

                        const xhr = new XMLHttpRequest();
                        xhr.open('POST', imageUploadEndpoint, true);
                        xhr.setRequestHeader('Accept', 'application/json');

                        xhr.onload = function() {
                            const response = JSON.parse(xhr.responseText);
                            if (xhr.status >= 200 && xhr.status < 300) {
                                element.summernote('insertImage', response.data.filePath);
                            } else {
                                const errorBagName = paramName.includes('#') ? paramName.replace('#', '.0.') :
                                    paramName;

                                const errorMessages = typeof response.errors !== 'undefined' ? response.errors[
                                    errorBagName].join('<br/>') : (typeof response === 'string' ? response :
                                    'Upload failed') + '<br/>';

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

                summernoteOptions['callbacks'] = summernoteCallbacks;
                element.summernote(summernoteOptions);
            }
        </script>
    @endBassetBlock
@endpush

{{-- End of Extra CSS and JS --}}
{{-- ########################################## --}}
