<!-- Tiny MCE -->
@php
$defaultOptions = [
    'file_picker_callback' => 'elFinderBrowser',
    'selector' => 'textarea.tinymce',
    'plugins' => 'image,link,media,anchor',
    //these two options allow tinymce to save the path of images "/upload/image.jpg" instead of the relative server path "../../../uploads/image.jpg"
    'relative_urls' =>  false,
    'remove_script_host' => true,
];

$field['options'] = array_merge($defaultOptions, $field['options'] ?? []);
@endphp

@include('crud::fields.inc.wrapper_start')
    <label>{!! $field['label'] !!}</label>
    @include('crud::fields.inc.translatable_icon')
    <textarea
        name="{{ $field['name'] }}"
        data-init-function="bpFieldInitTinyMceElement"
        data-options='{!! trim(json_encode($field['options'])) !!}'
        @include('crud::fields.inc.attributes', ['default_class' =>  'form-control tinymce'])
        >{{ oldValueDefaultOrFallback($field, '') }}</textarea>

    {{-- HINT --}}
    @if (isset($field['hint']))
        <p class="help-block">{!! $field['hint'] !!}</p>
    @endif
@include('crud::fields.inc.wrapper_end')


{{-- ########################################## --}}
{{-- Extra CSS and JS for this particular field --}}
{{-- If a field type is shown multiple times on a form, the CSS and JS will only be loaded once --}}
@if ($crud->fieldTypeNotLoaded($field))
    @php
        $crud->markFieldTypeAsLoaded($field);
    @endphp

    {{-- FIELD JS - will be loaded in the after_scripts section --}}
    @push('crud_fields_scripts')
    <!-- include tinymce js-->
    <script src="{{ asset('packages/tinymce/tinymce.min.js') }}"></script>

    <script type="text/javascript">
    function bpFieldInitTinyMceElement(element) {
        // grab the configuration defined in PHP
        var configuration = element.data('options');

        // the target should be the element the function has been called on
        configuration['target'] = element;
        configuration['file_picker_callback'] = eval(configuration['file_picker_callback']);

        // automatically update the textarea value on focusout
        configuration['setup'] = (function (editor) {
            editor.on('change', function () {
                tinymce.triggerSave();
            });
        });

        // initialize the TinyMCE editor
        tinymce.init(element.data('options'));
    }

    function elFinderBrowser (callback, value, meta) {
        tinymce.activeEditor.windowManager.openUrl({
            title: 'elFinder 2.0',
            url: '{{ backpack_url('elfinder/tinymce5') }}',
            width: 900,
            height: 460,
            onMessage: function (dialogApi, details) {
                if (details.mceAction === 'fileSelected') {
                    const file = details.data.file;

                    // Make file info
                    const info = file.name;

                    // Provide file and text for the link dialog
                    if (meta.filetype === 'file') {
                        callback(file.url, {text: info, title: info});
                    }

                    // Provide image and alt text for the image dialog
                    if (meta.filetype === 'image') {
                        callback(file.url, {alt: info});
                    }

                    // Provide alternative source and posted for the media dialog
                    if (meta.filetype === 'media') {
                        callback(file.url);
                    }

                    dialogApi.close();
                }
            }
        });
    }
    </script>
    @endpush

@endif
{{-- End of Extra CSS and JS --}}
{{-- ########################################## --}}
