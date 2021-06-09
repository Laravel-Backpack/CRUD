@php
    $field['wrapper'] = $field['wrapper'] ?? $field['wrapperAttributes'] ?? [];
    $field['wrapper']['class'] = $field['wrapper']['class'] ?? "form-group col-sm-12";
    $field['wrapper']['class'] = $field['wrapper']['class'].' cropperImage';
    $field['wrapper']['data-aspectRatio'] = $field['aspect_ratio'] ?? 0;
    $field['wrapper']['data-crop'] = $field['crop'] ?? false;
    $field['wrapper']['data-field-name'] = $field['wrapper']['data-field-name'] ?? $field['name'];
    $field['wrapper']['data-init-function'] = $field['wrapper']['data-init-function'] ?? 'bpFieldInitBase64CropperImageElement';

    // calculate the value of the hidden input
    if (!is_null(old(square_brackets_to_dots($field['name'])))) {
        $value = old(square_brackets_to_dots($field['name']));
    } elseif(isset($field['src']) && isset($entry)) {
        $value = $entry->find($entry->id)->{$field['src']}();
    } else {
        $value = oldValueDefaultOrFallback($field, '');
    }
@endphp

@include('crud::fields.inc.wrapper_start')
    <div>
        <label>{!! $field['label'] !!}</label>
        @include('crud::fields.inc.translatable_icon')
    </div>
    <!-- Wrap the image or canvas element with a block element (container) -->
    <div class="row">
        <div class="col-sm-6" data-handle="previewArea" style="margin-bottom: 20px;">
            <img data-handle="mainImage" src="">
        </div>
        @if(isset($field['crop']) && $field['crop'])
        <div class="col-sm-3" data-handle="previewArea">
            <div class="docs-preview clearfix">
                <div class="img-preview preview-lg">
                    <img src="" style="display: block; min-width: 0px !important; min-height: 0px !important; max-width: none !important; max-height: none !important; margin-left: -32.875px; margin-top: -18.4922px; transform: none;">
                </div>
            </div>
        </div>
        @endif
        <input type="hidden" class="hiddenFilename" name="{{ $field['filename'] }}" value="">
    </div>
    <div class="btn-group">
        <div class="btn btn-light btn-sm btn-file">
            {{ trans('backpack::crud.choose_file') }} <input type="file" accept="image/*" data-handle="uploadImage"  @include('crud::fields.inc.attributes', ['default_class' => 'hide'])>
            <input type="hidden" data-handle="hiddenImage" name="{{ $field['name'] }}" value="{{ $value }}">
        </div>
        @if(isset($field['crop']) && $field['crop'])
        <button class="btn btn-light btn-sm" data-handle="rotateLeft" type="button" style="display: none;"><i class="la la-rotate-left"></i></button>
        <button class="btn btn-light btn-sm" data-handle="rotateRight" type="button" style="display: none;"><i class="la la-rotate-right"></i></button>
        <button class="btn btn-light btn-sm" data-handle="zoomIn" type="button" style="display: none;"><i class="la la-search-plus"></i></button>
        <button class="btn btn-light btn-sm" data-handle="zoomOut" type="button" style="display: none;"><i class="la la-search-minus"></i></button>
        <button class="btn btn-light btn-sm" data-handle="reset" type="button" style="display: none;"><i class="la la-times"></i></button>
        @endif
        <button class="btn btn-light btn-sm" data-handle="remove" type="button"><i class="la la-trash"></i></button>
    </div>

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

    {{-- FIELD CSS - will be loaded in the after_styles section --}}
    @push('crud_fields_styles')
        <link href="{{ asset('packages/cropperjs/dist/cropper.min.css') }}" rel="stylesheet" type="text/css" />
        <style>
            .hide {
                display: none;
            }
            .image .btn-group {
                margin-top: 10px;
            }
            img {
                max-width: 100%; /* This rule is very important, please do not ignore this! */
            }
            .img-container, .img-preview {
                width: 100%;
                text-align: center;
            }
            .img-preview {
                float: left;
                margin-right: 10px;
                margin-bottom: 10px;
                overflow: hidden;
            }
            .preview-lg {
                width: 263px;
                height: 148px;
            }

            .btn-file {
                position: relative;
                overflow: hidden;
            }
            .btn-file input[type=file] {
                position: absolute;
                top: 0;
                right: 0;
                min-width: 100%;
                min-height: 100%;
                font-size: 100px;
                text-align: right;
                filter: alpha(opacity=0);
                opacity: 0;
                outline: none;
                background: white;
                cursor: inherit;
                display: block;
            }
        </style>
    @endpush

    {{-- FIELD JS - will be loaded in the after_scripts section --}}
    @push('crud_fields_scripts')
        <script src="{{ asset('packages/cropperjs/dist/cropper.min.js') }}"></script>
        <script src="{{ asset('packages/jquery-cropper/dist/jquery-cropper.min.js') }}"></script>
        <script>
            function bpFieldInitBase64CropperImageElement(element) {
                    // Find DOM elements under this form-group element
                    var $mainImage = element.find('[data-handle=mainImage]');
                    var $uploadImage = element.find("[data-handle=uploadImage]");
                    var $hiddenImage = element.find("[data-handle=hiddenImage]");
                    var $hiddenFilename = element.find(".hiddenFilename");
                    var $rotateLeft = element.find("[data-handle=rotateLeft]");
                    var $rotateRight = element.find("[data-handle=rotateRight]");
                    var $zoomIn = element.find("[data-handle=zoomIn]");
                    var $zoomOut = element.find("[data-handle=zoomOut]");
                    var $reset = element.find("[data-handle=reset]");
                    var $remove = element.find("[data-handle=remove]");
                    var $previews = element.find("[data-handle=previewArea]");
                    // Options either global for all image type fields, or use 'data-*' elements for options passed in via the CRUD controller
                    var options = {
                        viewMode: 2,
                        checkOrientation: false,
                        autoCropArea: 1,
                        responsive: true,
                        preview : element.find('.img-preview'),
                        aspectRatio : element.attr('data-aspectRatio')
                    };
                    var crop = element.attr('data-crop');

                    // Hide 'Remove' button if there is no image saved
                    if (!$hiddenImage.val()){
                        $previews.hide();
                        $remove.hide();
                    }
                    // Make the main image show the image in the hidden input
                    $mainImage.attr('src', $hiddenImage.val());

                    // Only initialize cropper plugin if crop is set to true
                    if(crop){

                        $remove.click(function() {
                            $mainImage.cropper("destroy");
                            $mainImage.attr('src','');
                            $hiddenImage.val('');
                            if (filename == "true"){
                                $hiddenFilename.val('removed');
                            }
                            $rotateLeft.hide();
                            $rotateRight.hide();
                            $zoomIn.hide();
                            $zoomOut.hide();
                            $reset.hide();
                            $remove.hide();
                            $previews.hide();
                        });
                    } else {

                        $remove.click(function() {
                            $mainImage.attr('src','');
                            $hiddenImage.val('');
                            $hiddenFilename.val('removed');
                            $remove.hide();
                            $previews.hide();
                        });
                    }

                    //Set hiddenFilename field to 'removed' if image has been removed.
                    //Otherwise hiddenFilename will be null if no changes have been made.

                    $uploadImage.change(function() {
                        var fileReader = new FileReader(),
                                files = this.files,
                                file;

                        if (!files.length) {
                            return;
                        }
                        file = files[0];

                        if (/^image\/\w+$/.test(file.type)) {
                            $hiddenFilename.val(file.name);
                            fileReader.readAsDataURL(file);
                            fileReader.onload = function () {
                                $uploadImage.val("");
                                $previews.show();
                                if(crop){
                                    $mainImage.cropper(options).cropper("reset", true).cropper("replace", this.result);
                                    // update the hidden input after selecting a new item or cropping
                                    $mainImage.on('ready cropstart cropend', function() {
                                        var imageURL = $mainImage.cropper('getCroppedCanvas').toDataURL(file.type);
                                        $hiddenImage.val(imageURL);
                                        return true;
                                    });

                                    $rotateLeft.show();
                                    $rotateRight.show();
                                    $zoomIn.show();
                                    $zoomOut.show();
                                    $reset.show();
                                    $remove.show();

                                } else {
                                    $mainImage.attr('src',this.result);
                                    $hiddenImage.val(this.result);
                                    $remove.show();
                                }
                            };
                        } else {
                            new Noty({
                                type: "error",
                                text: "<strong>Please choose an image file</strong><br>The file you've chosen does not look like an image."
                            }).show();
                        }
                    });

                    //moved the click binds outside change event, or we would register as many click events for the same amout of times
                    //we triggered the image change
                    if(crop) {
                        $rotateLeft.click(function() {
                            $mainImage.cropper("rotate", 90);
                        });

                        $rotateRight.click(function() {
                            $mainImage.cropper("rotate", -90);
                        });

                        $zoomIn.click(function() {
                            $mainImage.cropper("zoom", 0.1);
                        });

                        $zoomOut.click(function() {
                            $mainImage.cropper("zoom", -0.1);
                        });

                        $reset.click(function() {
                            $mainImage.cropper("reset");
                        });
                    }
            }
        </script>


    @endpush
@endif
{{-- End of Extra CSS and JS --}}
{{-- ########################################## --}}
