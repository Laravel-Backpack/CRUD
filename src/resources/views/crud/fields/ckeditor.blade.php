<!-- CKeditor -->
@php
    $field['extra_plugins'] = isset($field['extra_plugins']) ? implode(',', $field['extra_plugins']) : "embed,widget";

    $defaultOptions = [
        "filebrowserBrowseUrl" => backpack_url('elfinder/ckeditor'),
        "extraPlugins" => $field['extra_plugins'],
        "embed_provider" => "//ckeditor.iframe.ly/api/oembed?url={url}&callback={callback}",
    ];

    $field['options'] = array_merge($defaultOptions, $field['options'] ?? []);
@endphp

@include('crud::fields.inc.wrapper_start')
    <label>{!! $field['label'] !!}</label>
    @include('crud::fields.inc.translatable_icon')
    <textarea
        name="{{ $field['name'] }}"
        data-init-function="bpFieldInitCKEditorElement"
        data-options="{{ trim(json_encode($field['options'])) }}"
        @include('crud::fields.inc.attributes', ['default_class' => 'form-control'])
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
        <script src="{{ asset('packages/ckeditor/ckeditor.js') }}"></script>
        <script src="{{ asset('packages/ckeditor/adapters/jquery.js') }}"></script>
        <script>
            function bpFieldInitCKEditorElement(element) {


                //when removing ckeditor field from page html the instance is not properly deleted.
                //this event is triggered in repeatable on deletion so this field can intercept it
                //and properly delete the instances so it don't throw errors of unexistent elements in page that has initialized ck instances.
                element.on('backpack_field.deleted', function(e) {
                    $ck_instance_name = element.siblings("[id^='cke_editor']").attr('id');

                    //if the instance name starts with cke_ it was an auto-generated name from ckeditor
                    //that happens because in repeatable we stripe the field names used by ckeditor, so it renders a random name
                    //that starts with cke_
                    if($ck_instance_name.startsWith('cke_')) {
                        $ck_instance_name = $ck_instance_name.substr(4);
                    }
                    //we fully destroy the instance when element is deleted from the page.
                    CKEDITOR.instances[$ck_instance_name].destroy(true);
                });
                // trigger a new CKEditor
                element.ckeditor(element.data('options'));
            }
        </script>
    @endpush

@endif

{{-- End of Extra CSS and JS --}}
{{-- ########################################## --}}
