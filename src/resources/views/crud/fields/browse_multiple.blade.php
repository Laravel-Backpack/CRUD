@php
$multiple = Arr::get($field, 'multiple', true);
$sortable = Arr::get($field, 'sortable', false);
$value = oldValueDefaultOrFallback($field, '');

if (!$multiple && is_array($value)) {
    $value = Arr::first($value);
}

$field['wrapper'] = $field['wrapper'] ?? $field['wrapperAttributes'] ?? [];
$field['wrapper']['data-init-function'] = $field['wrapper']['data-init-function'] ?? 'bpFieldInitBrowseMultipleElement';
$field['wrapper']['data-elfinder-trigger-url'] = $field['wrapper']['data-elfinder-trigger-url'] ?? url(config('elfinder.route.prefix').'/popup/'.$field['name'].'?multiple=1');

if (isset($field['mime_types'])) {
    $field['wrapper']['data-elfinder-trigger-url'] .= '&mimes='.urlencode(serialize($field['mime_types']));
}

if ($multiple) {
    $field['wrapper']['data-multiple'] = "true";
} else {
    $field['wrapper']['data-multiple'] = "false";
}

if($sortable){
    $field['wrapper']['sortable'] = "true";
}
@endphp

@include('crud::fields.inc.wrapper_start')

    <div><label>{!! $field['label'] !!}</label></div>
    @include('crud::fields.inc.translatable_icon')
    <div class="list" data-field-name="{{ $field['name'] }}">
    @if ($multiple)
        <input type="hidden" data-marker="multipleBrowseInput" name="{{ $field['name'] }}" value="{{ json_encode($value) }}">
    @else
        <input type="text" data-marker="multipleBrowseInput" name="{{ $field['name'] }}" value="{{ $value }}" @include('crud::fields.inc.attributes') readonly>
    @endif
</div>
    <div class="btn-group" role="group" aria-label="..." style="margin-top: 3px;">
        <button type="button" class="browse popup btn btn-sm btn-light">
            <i class="la la-cloud-upload"></i>
            {{ trans('backpack::crud.browse_uploads') }}
        </button>
        <button type="button" class="browse clear btn btn-sm btn-light">
            <i class="la la-eraser"></i>
            {{ trans('backpack::crud.clear') }}
        </button>
    </div>

    @if (isset($field['hint']))
        <p class="help-block">{!! $field['hint'] !!}</p>
    @endif

    <script type="text/html" data-marker="browse_multiple_template">
        <div class="input-group input-group-sm">
            <input type="text" @include('crud::fields.inc.attributes') readonly>
            <div class="input-group-btn">
                <button type="button" class="browse remove btn btn-sm btn-light">
                    <i class="la la-trash"></i>
                </button>
                @if($sortable)
                    <button type="button" class="browse move btn btn-sm btn-light"><span class="la la-sort"></span></button>
                @endif
            </div>
        </div>
    </script>
@include('crud::fields.inc.wrapper_end')


{{-- ########################################## --}}
{{-- Extra CSS and JS for this particular field --}}
{{-- If a field type is shown multiple times on a form, the CSS and JS will only be loaded once --}}
@if ($crud->fieldTypeNotLoaded($field))
    @php
        $crud->markFieldTypeAsLoaded($field);
    @endphp

    {{-- FIELD CSS - will be loaded in the after_styles section --}}
    @push('crud_fields_styles')
        <link href="{{ asset('packages/jquery-colorbox/example2/colorbox.css') }}" rel="stylesheet" type="text/css" />
        <style>
            #cboxContent, #cboxLoadedContent, .cboxIframe {
                background: transparent;
            }
        </style>
    @endpush

    @push('crud_fields_scripts')

        <script src="{{ asset('packages/jquery-ui-dist/jquery-ui.min.js') }}"></script>
        <script src="{{ asset('packages/jquery-colorbox/jquery.colorbox-min.js') }}"></script>
        <script>
            // this global variable is used to remember what input to update with the file path
            // because elfinder is actually loaded in an iframe by colorbox
            var elfinderTarget = false;

            // function to use the files selected inside elfinder
            function processSelectedMultipleFiles(files, requestingField) {
                elfinderTarget.trigger('createInputsForItemsSelectedWithElfinder', [files]);
                elfinderTarget = false;
            }

            function bpFieldInitBrowseMultipleElement(element) {
                var $triggerUrl = element.data('elfinder-trigger-url');
                var $template = element.find("[data-marker=browse_multiple_template]").html();
                var $list = element.find(".list");
                var $input = element.find('input[data-marker=multipleBrowseInput]');
                var $multiple = element.attr('data-multiple');
                var $sortable = element.attr('sortable');

                // show existing items - display visible inputs for each stored path
                if ($input.val() != '' && $input.val() != null && $multiple === 'true') {
                    $paths = JSON.parse($input.val());
                    if (Array.isArray($paths) && $paths.length) {
                        // remove any already visible inputs
                        $list.find('.input-group').remove();

                        // add visible inputs for each item inside the hidden input array
                        $paths.forEach(function (path) {
                            var newInput = $($template);
                            newInput.find('input').val(path);
                            $list.append(newInput);
                        });
                    }
                }

                // make the items sortable, if configurations says so
                if($sortable){
                    $list.sortable({
                        handle: 'button.move',
                        cancel: '',
                        update: function (event, ui) {
                            element.trigger('saveToJson');
                        }
                    });
                }

                element.on('click', 'button.popup', function (event) {
                    event.preventDefault();

                    // remember which element the elFinder was triggered by
                    elfinderTarget = element;

                    // trigger the elFinder modal
                    $.colorbox({
                        href: $triggerUrl,
                        fastIframe: true,
                        iframe: true,
                        width: '80%',
                        height: '80%'
                    });
                });

                // turn non-hidden inputs into a JSON
                // and save them inside the hidden input that ACTUALLY holds all paths
                element.on('saveToJson', function(event) {
                    var $paths = element.find('input').not('[type=hidden]').map(function (idx, item) {
                        return $(item).val();
                    }).toArray();

                    // save the JSON inside the hidden input
                    $input.val(JSON.stringify($paths));
                });

                if ($multiple === 'true') {
                    // remote item button
                    element.on('click', 'button.remove', function (event) {
                        event.preventDefault();
                        $(this).closest('.input-group').remove();
                        element.trigger('saveToJson');
                    });

                    // clear button
                    element.on('click', 'button.clear', function (event) {
                        event.preventDefault();

                        $('.input-group', $list).remove();
                        element.trigger('saveToJson');
                    });

                    // called after one or more items are selected in the elFinder window
                    element.on('createInputsForItemsSelectedWithElfinder', element, function(event, files) {
                        files.forEach(function (file) {
                            var newInput = $($template);
                            newInput.find('input').val(file.path);
                            $list.append(newInput);
                        });

                        if($sortable){
                            $list.sortable("refresh")
                        }

                        element.trigger('saveToJson');
                    });

                } else {
                    // clear button
                    element.on('click', 'button.clear', function (event) {
                        $input.val('');
                    });

                    // called after an item has been selected in the elFinder window
                    element.on('createInputsForItemsSelectedWithElfinder', element, function(event, files) {
                        $input.val(files[0].path);
                    });
                }
            }
        </script>
    @endpush
@endif

{{-- End of Extra CSS and JS --}}
{{-- ########################################## --}}
