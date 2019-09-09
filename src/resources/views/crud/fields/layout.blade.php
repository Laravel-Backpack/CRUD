<!-- path: resources/views/vendor/backpack/crud/fields/layout.blade.php -->

<!-- layout field type -->
@php
    $current_value = old($field['name']) ?? $field['value'] ?? $field['default'] ?? '';
    $blocks = [];

    if (isset($field['blocks']) && is_array($field['blocks']) && !empty($field['blocks'])) {
    	foreach ($field['blocks'] as $block_line) {
    		if (is_string($block_line)) {
    			$block_files = Storage::disk(config('backpack.base.root_disk_name'))->files($block_line);

    			if (!empty($block_files)) {
    				foreach ($block_files as $block_file) {
    					include(base_path($block_file));
    				}
    			}
    		} else {
    			abort(500, 'Backpack block directory is not string: '.json_encode($block_line));
    		}
    	}
    }
@endphp


<div @include('crud::inc.field_wrapper_attributes') >

    <label>{!! $field['label'] !!}</label>
    @include('crud::inc.field_translatable_icon')

    {{-- The input where the JSON content is actually stored and updated --}}
    <input type="hidden"
        name="{{ $field['name'] }}"
        value="{{ old($field['name']) ?? $field['value'] ?? $field['default'] ?? '' }}"
        data-templates="{{ $field['templates'] ?? 0 }}"
        data-templates-route="{{ $field['templates_resource_route'] ?? '' }}"
        >

    <div class="content-container"></div>

    {{-- HINT --}}
    @if (isset($field['hint']))
        <p class="help-block">{!! $field['hint'] !!}</p>
    @endif
</div>

{{-- ################################################## --}}
{{-- The blocks that are available for the admin to use --}}
{{-- ################################################## --}}

{{-- @push('before_scripts') --}}
@if (count($blocks))

    <div class="template-types d-none">
        <div class="container-choose form-inline">
            <select class="form-control form-control-sm">

            @foreach($blocks as $block_item)
                <option data-icon="{{ $block_item['icon'] }}" value="{{ $block_item['type'] }}">{{ $block_item['label'] }}</option>
            @endforeach

            <option value="container">[Container]</option>

            </select>
            <button class="btn btn-sm btn-default type-choose" type="button">Edit</button>
        </div>

    </div>

    <div class="template-forms">

    @foreach($blocks as $block_item)

    <div class="modal fade" id="modal{{ $block_item['type'] }}" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true" data-backdrop="false" style="background-color: rgba(0, 0, 0, 0.5);">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="exampleModalLabel">{{ $block_item['label'] }}</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body row bold-labels clearfix">

                @foreach($block_item['fields'] as $block_field)
                    @php
                        $fieldViewNamespace = $block_field['view_namespace'] ?? 'crud::fields';
                        $fieldViewPath = $fieldViewNamespace.'.'.$block_field['type'];
                        $block_field['showAsterisk'] = false;
                    @endphp

                    @include($fieldViewPath, ['field' => $block_field])
                @endforeach

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">{{ trans('backpack::crud.cancel') }}</button>
                    <button type="button" class="btn btn-primary save-block" data-dismiss="modal">{{ trans('backpack::crud.save') }}</button>
                </div>
            </div>
        </div>
    </div>

    @endforeach

    @if (isset($field['row_settings']))
        @php
            $row_settings = $field['row_settings'];
        @endphp
        <div class="modal fade" id="modal_row_settings" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true" data-backdrop="false" style="background-color: rgba(0, 0, 0, 0.5);">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <h4 class="modal-title" id="exampleModalLabel">Row Settings</h4>
                    </div>
                    <div class="modal-body clearfix">

                    @foreach($row_settings as $row_attribute_field)
                        @php
                            $fieldViewNamespace = $row_attribute_field['view_namespace'] ?? 'crud::fields';
                            $fieldViewPath = $fieldViewNamespace.'.'.$row_attribute_field['type'];
                        @endphp

                        @include($fieldViewPath, ['field' => $row_attribute_field])
                    @endforeach

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">{{ trans('backpack::crud.cancel') }}</button>
                        <button type="button" class="btn btn-primary save-block" data-dismiss="modal">{{ trans('backpack::crud.save') }}</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    </div>

    <div class="block-forms"></div>

@endif

@if (isset($field['templates']) && $field['templates'])
    <div class="modal fade" id="browseLayoutTemplatesModal" tabindex="-1" role="dialog" aria-labelledby="browseLayoutTemplatesModal" aria-hidden="true" data-backdrop="false" style="background-color: rgba(0, 0, 0, 0.5);">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="browseLayoutTemplatesModal">Load Template</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body clearfix no-padding">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">{{ trans('backpack::crud.cancel') }}</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="saveLayoutTemplateModal" tabindex="-1" role="dialog" aria-labelledby="saveLayoutTemplateModal" aria-hidden="true" data-backdrop="false" style="background-color: rgba(0, 0, 0, 0.5);">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="saveLayoutTemplateModal">Save as Template</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body clearfix">
                    <div class="form-group">
                        <label for="exampleFormControlSelect1">Template name:</label>
                        <input type="text" class="form-control" id="newTemplateName" />
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">{{ trans('backpack::crud.cancel') }}</button>
                    <button data-trigger="save" type="button" class="btn btn-primary save-block" data-dismiss="modal" onclick="bpSaveLayoutTemplate()">{{ trans('backpack::crud.save') }}</button>
                </div>
            </div>
        </div>
    </div>
@endif

{{-- @endpush --}}

{{-- ########################################## --}}
{{-- Extra CSS and JS for this particular field --}}
{{-- If a field type is shown multiple times on a form, the CSS and JS will only be loaded once --}}
@if ($crud->fieldTypeNotLoaded($field))
    @php
      $crud->markFieldTypeAsLoaded($field);
    @endphp

    {{-- FIELD CSS - will be loaded in the after_styles section --}}
    @push('crud_fields_styles')
        <style>
            .ge-mainControls {
              position: relative;
              min-height: 22px;
              margin-bottom: 10px;
            }
            .ge-mainControls .ge-wrapper {
              padding-top: 5px;
            }
            .ge-mainControls .ge-wrapper.ge-top .container {
              margin: 0;
              padding: 0;
              width: auto;
            }
            .ge-mainControls .ge-wrapper.ge-fixed {
              position: fixed;
              z-index: 20;
              top: 0;
            }
            .ge-mainControls .ge-addRowGroup .btn.btn-primary {
              background: #f4f4f4;
              color: #666;
              border: 1px solid #999;
              display: flex;
              align-items: center;
            }
            .ge-mainControls .ge-addRowGroup .btn.btn-primary:hover {
              background: #E3E3E3;
            }
            .ge-mainControls .ge-addRowGroup .ge-row-icon {
              width: 48px;
              margin: 0;
            }
            .ge-mainControls .ge-addRowGroup .ge-row-icon .column {
              height: 10px;
              background: #666;
              padding: 0;
              border-left: 2px solid #f4f4f4;
            }
            .ge-mainControls .ge-layout-mode {
              margin-left: 5px;
            }
            .ge-mainControls .btn:focus {
              box-shadow: none;
              outline: none;
            }
            .ge-html-output {
              width: 100%;
              display: none;
            }
            /* Not editing */
            .ge-canvas .ge-tools-drawer {
              display: none;
            }
            /* While editing */
            .ge-canvas.ge-editing {
              /* Content area */
              /* Sorting */
            }
            .ge-canvas.ge-editing .ge-tools-drawer {
              -webkit-touch-callout: none;
              -webkit-user-select: none;
              -moz-user-select: none;
              -ms-user-select: none;
              user-select: none;
              background: blue;
              margin: 0 -5px 5px;
              display: block;
              width: calc(100% + 10px);
            }
            .ge-canvas.ge-editing .ge-tools-drawer > a {
              display: inline-block;
              padding: 5px;
            }
            .ge-canvas.ge-editing .ge-tools-drawer > a:hover {
              background: rgba(255, 255, 255, 0.5);
              cursor: pointer;
            }
            .ge-canvas.ge-editing .ge-tools-drawer .ge-details {
              padding: 5px;
              border-top: 1px solid rgba(0, 0, 0, 0.05);
              display: none;
            }
            .ge-canvas.ge-editing .ge-tools-drawer .ge-details .btn-group a {
              color: #333;
            }
            .ge-canvas.ge-editing .ge-tools-drawer .ge-details .btn-group a.active {
              color: white;
            }
            .ge-canvas.ge-editing .ge-tools-drawer .ge-details .btn-group a:hover {
              text-decoration: none;
              cursor: pointer;
            }
            .ge-canvas.ge-editing .ge-tools-drawer .ge-details input.ge-id {
              border: 1px solid #7F7F7F;
              border-radius: 4px;
              font-size: 11px;
              padding: 2px 5px;
              margin-right: 5px;
              width: 80px;
            }
            .ge-canvas.ge-editing .row {
              background-color: #efefef;
              border: 1px solid #999999;
              padding: 0 5px 5px;
              margin-bottom: 5px;
              margin-left: 0;
              margin-right: 0;
            }
            .ge-canvas.ge-editing .row > .ge-tools-drawer {
              background: #e5e5e5;
            }
            .ge-canvas.ge-editing .row > .ge-tools-drawer > a {
              color: #666;
            }
            .ge-canvas.ge-editing .row > .ge-tools-drawer > a.ge-add-column {
              color: #178acc;
            }
            .ge-canvas.ge-editing .column {
              background-color: rgba(47, 164, 231, 0.15);
              border: 1px solid #2FA4E7;
              padding: 0 5px 5px;
            }
            .ge-canvas.ge-editing .column > .ge-tools-drawer {
              background: #afd5ea;
            }
            .ge-canvas.ge-editing .column > .ge-tools-drawer a {
              color: #178acc;
            }
            .ge-canvas.ge-editing .column > .ge-tools-drawer > a.ge-add-row {
              color: #666;
            }
            .ge-canvas.ge-editing .ge-content-type-tinymce.active {
              outline: 1px dotted #333;
            }
            .ge-canvas.ge-editing .ui-sortable-placeholder {
              background: rgba(255, 255, 0, 0.2);
              visibility: visible !important;
              min-height: 50px;
            }
            /* Layout modes */
            .ge-canvas.ge-layout-phone {
              max-width: 400px;
              margin-left: auto;
              margin-right: auto;
            }
            .ge-canvas.ge-layout-phone .col-sm-1,
            .ge-canvas.ge-layout-phone .col-md-1,
            .ge-canvas.ge-layout-phone .col-lg-1 {
              width: inherit;
            }
            .ge-canvas.ge-layout-phone .col-sm-2,
            .ge-canvas.ge-layout-phone .col-md-2,
            .ge-canvas.ge-layout-phone .col-lg-2 {
              width: inherit;
            }
            .ge-canvas.ge-layout-phone .col-sm-3,
            .ge-canvas.ge-layout-phone .col-md-3,
            .ge-canvas.ge-layout-phone .col-lg-3 {
              width: inherit;
            }
            .ge-canvas.ge-layout-phone .col-sm-4,
            .ge-canvas.ge-layout-phone .col-md-4,
            .ge-canvas.ge-layout-phone .col-lg-4 {
              width: inherit;
            }
            .ge-canvas.ge-layout-phone .col-sm-5,
            .ge-canvas.ge-layout-phone .col-md-5,
            .ge-canvas.ge-layout-phone .col-lg-5 {
              width: inherit;
            }
            .ge-canvas.ge-layout-phone .col-sm-6,
            .ge-canvas.ge-layout-phone .col-md-6,
            .ge-canvas.ge-layout-phone .col-lg-6 {
              width: inherit;
            }
            .ge-canvas.ge-layout-phone .col-sm-7,
            .ge-canvas.ge-layout-phone .col-md-7,
            .ge-canvas.ge-layout-phone .col-lg-7 {
              width: inherit;
            }
            .ge-canvas.ge-layout-phone .col-sm-8,
            .ge-canvas.ge-layout-phone .col-md-8,
            .ge-canvas.ge-layout-phone .col-lg-8 {
              width: inherit;
            }
            .ge-canvas.ge-layout-phone .col-sm-9,
            .ge-canvas.ge-layout-phone .col-md-9,
            .ge-canvas.ge-layout-phone .col-lg-9 {
              width: inherit;
            }
            .ge-canvas.ge-layout-phone .col-sm-10,
            .ge-canvas.ge-layout-phone .col-md-10,
            .ge-canvas.ge-layout-phone .col-lg-10 {
              width: inherit;
            }
            .ge-canvas.ge-layout-phone .col-sm-11,
            .ge-canvas.ge-layout-phone .col-md-11,
            .ge-canvas.ge-layout-phone .col-lg-11 {
              width: inherit;
            }
            .ge-canvas.ge-layout-phone .col-sm-12,
            .ge-canvas.ge-layout-phone .col-md-12,
            .ge-canvas.ge-layout-phone .col-lg-12 {
              width: inherit;
            }
            .ge-canvas.ge-layout-phone .col-1 {
              flex: 0 0 8.33333333%;
              max-width: 8.33333333%;
            }
            .ge-canvas.ge-layout-phone .col-2 {
              flex: 0 0 16.66666667%;
              max-width: 16.66666667%;
            }
            .ge-canvas.ge-layout-phone .col-3 {
              flex: 0 0 25%;
              max-width: 25%;
            }
            .ge-canvas.ge-layout-phone .col-4 {
              flex: 0 0 33.33333333%;
              max-width: 33.33333333%;
            }
            .ge-canvas.ge-layout-phone .col-5 {
              flex: 0 0 41.66666667%;
              max-width: 41.66666667%;
            }
            .ge-canvas.ge-layout-phone .col-6 {
              flex: 0 0 50%;
              max-width: 50%;
            }
            .ge-canvas.ge-layout-phone .col-7 {
              flex: 0 0 58.33333333%;
              max-width: 58.33333333%;
            }
            .ge-canvas.ge-layout-phone .col-8 {
              flex: 0 0 66.66666667%;
              max-width: 66.66666667%;
            }
            .ge-canvas.ge-layout-phone .col-9 {
              flex: 0 0 75%;
              max-width: 75%;
            }
            .ge-canvas.ge-layout-phone .col-10 {
              flex: 0 0 83.33333333%;
              max-width: 83.33333333%;
            }
            .ge-canvas.ge-layout-phone .col-11 {
              flex: 0 0 91.66666667%;
              max-width: 91.66666667%;
            }
            .ge-canvas.ge-layout-phone .col-12 {
              flex: 0 0 100%;
              max-width: 100%;
            }
            .ge-canvas.ge-layout-tablet {
              max-width: 800px;
              margin-left: auto;
              margin-right: auto;
            }
            .ge-canvas.ge-layout-tablet .col-1,
            .ge-canvas.ge-layout-tablet .col-md-1,
            .ge-canvas.ge-layout-tablet .col-lg-1 {
              width: inherit;
            }
            .ge-canvas.ge-layout-tablet .col-2,
            .ge-canvas.ge-layout-tablet .col-md-2,
            .ge-canvas.ge-layout-tablet .col-lg-2 {
              width: inherit;
            }
            .ge-canvas.ge-layout-tablet .col-3,
            .ge-canvas.ge-layout-tablet .col-md-3,
            .ge-canvas.ge-layout-tablet .col-lg-3 {
              width: inherit;
            }
            .ge-canvas.ge-layout-tablet .col-4,
            .ge-canvas.ge-layout-tablet .col-md-4,
            .ge-canvas.ge-layout-tablet .col-lg-4 {
              width: inherit;
            }
            .ge-canvas.ge-layout-tablet .col-5,
            .ge-canvas.ge-layout-tablet .col-md-5,
            .ge-canvas.ge-layout-tablet .col-lg-5 {
              width: inherit;
            }
            .ge-canvas.ge-layout-tablet .col-6,
            .ge-canvas.ge-layout-tablet .col-md-6,
            .ge-canvas.ge-layout-tablet .col-lg-6 {
              width: inherit;
            }
            .ge-canvas.ge-layout-tablet .col-7,
            .ge-canvas.ge-layout-tablet .col-md-7,
            .ge-canvas.ge-layout-tablet .col-lg-7 {
              width: inherit;
            }
            .ge-canvas.ge-layout-tablet .col-8,
            .ge-canvas.ge-layout-tablet .col-md-8,
            .ge-canvas.ge-layout-tablet .col-lg-8 {
              width: inherit;
            }
            .ge-canvas.ge-layout-tablet .col-9,
            .ge-canvas.ge-layout-tablet .col-md-9,
            .ge-canvas.ge-layout-tablet .col-lg-9 {
              width: inherit;
            }
            .ge-canvas.ge-layout-tablet .col-10,
            .ge-canvas.ge-layout-tablet .col-md-10,
            .ge-canvas.ge-layout-tablet .col-lg-10 {
              width: inherit;
            }
            .ge-canvas.ge-layout-tablet .col-11,
            .ge-canvas.ge-layout-tablet .col-md-11,
            .ge-canvas.ge-layout-tablet .col-lg-11 {
              width: inherit;
            }
            .ge-canvas.ge-layout-tablet .col-12,
            .ge-canvas.ge-layout-tablet .col-md-12,
            .ge-canvas.ge-layout-tablet .col-lg-12 {
              width: inherit;
            }
            .ge-canvas.ge-layout-tablet .col-sm-1 {
              flex: 0 0 8.33333333%;
              max-width: 8.33333333%;
            }
            .ge-canvas.ge-layout-tablet .col-sm-2 {
              flex: 0 0 16.66666667%;
              max-width: 16.66666667%;
            }
            .ge-canvas.ge-layout-tablet .col-sm-3 {
              flex: 0 0 25%;
              max-width: 25%;
            }
            .ge-canvas.ge-layout-tablet .col-sm-4 {
              flex: 0 0 33.33333333%;
              max-width: 33.33333333%;
            }
            .ge-canvas.ge-layout-tablet .col-sm-5 {
              flex: 0 0 41.66666667%;
              max-width: 41.66666667%;
            }
            .ge-canvas.ge-layout-tablet .col-sm-6 {
              flex: 0 0 50%;
              max-width: 50%;
            }
            .ge-canvas.ge-layout-tablet .col-sm-7 {
              flex: 0 0 58.33333333%;
              max-width: 58.33333333%;
            }
            .ge-canvas.ge-layout-tablet .col-sm-8 {
              flex: 0 0 66.66666667%;
              max-width: 66.66666667%;
            }
            .ge-canvas.ge-layout-tablet .col-sm-9 {
              flex: 0 0 75%;
              max-width: 75%;
            }
            .ge-canvas.ge-layout-tablet .col-sm-10 {
              flex: 0 0 83.33333333%;
              max-width: 83.33333333%;
            }
            .ge-canvas.ge-layout-tablet .col-sm-11 {
              flex: 0 0 91.66666667%;
              max-width: 91.66666667%;
            }
            .ge-canvas.ge-layout-tablet .col-sm-12 {
              flex: 0 0 100%;
              max-width: 100%;
            }
            .ge-canvas.ge-layout-desktop {
              max-width: none;
              margin-left: auto;
              margin-right: auto;
            }
            .ge-canvas.ge-layout-desktop .col-1,
            .ge-canvas.ge-layout-desktop .col-sm-1,
            .ge-canvas.ge-layout-desktop .col-md-1 {
              width: inherit;
            }
            .ge-canvas.ge-layout-desktop .col-2,
            .ge-canvas.ge-layout-desktop .col-sm-2,
            .ge-canvas.ge-layout-desktop .col-md-2 {
              width: inherit;
            }
            .ge-canvas.ge-layout-desktop .col-3,
            .ge-canvas.ge-layout-desktop .col-sm-3,
            .ge-canvas.ge-layout-desktop .col-md-3 {
              width: inherit;
            }
            .ge-canvas.ge-layout-desktop .col-4,
            .ge-canvas.ge-layout-desktop .col-sm-4,
            .ge-canvas.ge-layout-desktop .col-md-4 {
              width: inherit;
            }
            .ge-canvas.ge-layout-desktop .col-5,
            .ge-canvas.ge-layout-desktop .col-sm-5,
            .ge-canvas.ge-layout-desktop .col-md-5 {
              width: inherit;
            }
            .ge-canvas.ge-layout-desktop .col-6,
            .ge-canvas.ge-layout-desktop .col-sm-6,
            .ge-canvas.ge-layout-desktop .col-md-6 {
              width: inherit;
            }
            .ge-canvas.ge-layout-desktop .col-7,
            .ge-canvas.ge-layout-desktop .col-sm-7,
            .ge-canvas.ge-layout-desktop .col-md-7 {
              width: inherit;
            }
            .ge-canvas.ge-layout-desktop .col-8,
            .ge-canvas.ge-layout-desktop .col-sm-8,
            .ge-canvas.ge-layout-desktop .col-md-8 {
              width: inherit;
            }
            .ge-canvas.ge-layout-desktop .col-9,
            .ge-canvas.ge-layout-desktop .col-sm-9,
            .ge-canvas.ge-layout-desktop .col-md-9 {
              width: inherit;
            }
            .ge-canvas.ge-layout-desktop .col-10,
            .ge-canvas.ge-layout-desktop .col-sm-10,
            .ge-canvas.ge-layout-desktop .col-md-10 {
              width: inherit;
            }
            .ge-canvas.ge-layout-desktop .col-11,
            .ge-canvas.ge-layout-desktop .col-sm-11,
            .ge-canvas.ge-layout-desktop .col-md-11 {
              width: inherit;
            }
            .ge-canvas.ge-layout-desktop .col-12,
            .ge-canvas.ge-layout-desktop .col-sm-12,
            .ge-canvas.ge-layout-desktop .col-md-12 {
              width: inherit;
            }
            .ge-canvas.ge-layout-desktop .col-lg-1 {
              flex: 0 0 8.33333333%;
              max-width: 8.33333333%;
            }
            .ge-canvas.ge-layout-desktop .col-lg-2 {
              flex: 0 0 16.66666667%;
              max-width: 16.66666667%;
            }
            .ge-canvas.ge-layout-desktop .col-lg-3 {
              flex: 0 0 25%;
              max-width: 25%;
            }
            .ge-canvas.ge-layout-desktop .col-lg-4 {
              flex: 0 0 33.33333333%;
              max-width: 33.33333333%;
            }
            .ge-canvas.ge-layout-desktop .col-lg-5 {
              flex: 0 0 41.66666667%;
              max-width: 41.66666667%;
            }
            .ge-canvas.ge-layout-desktop .col-lg-6 {
              flex: 0 0 50%;
              max-width: 50%;
            }
            .ge-canvas.ge-layout-desktop .col-lg-7 {
              flex: 0 0 58.33333333%;
              max-width: 58.33333333%;
            }
            .ge-canvas.ge-layout-desktop .col-lg-8 {
              flex: 0 0 66.66666667%;
              max-width: 66.66666667%;
            }
            .ge-canvas.ge-layout-desktop .col-lg-9 {
              flex: 0 0 75%;
              max-width: 75%;
            }
            .ge-canvas.ge-layout-desktop .col-lg-10 {
              flex: 0 0 83.33333333%;
              max-width: 83.33333333%;
            }
            .ge-canvas.ge-layout-desktop .col-lg-11 {
              flex: 0 0 91.66666667%;
              max-width: 91.66666667%;
            }
            .ge-canvas.ge-layout-desktop .col-lg-12 {
              flex: 0 0 100%;
              max-width: 100%;
            }
            /* Font Awesome Integration for Grid Editor
             * (min. Version 4.0)
             */
            .ge-canvas.ge-editing .ge-tools-drawer > a {
              padding: 3px 5px;
            }

            .container-choose {
              text-align: center;
            }
            .container-glyph {
              font-size: 24px;
              text-align: center;
              cursor: pointer;
              padding: 20px 0;
              text-transform: capitalize;
              width: 100%;
            }
            .container-glyph span:before {
              font-size: 36px;
            }

            .ge-row-icon .col-1 {
              flex: 0 0 8.33333333%;
              max-width: 8.33333333%;
            }
            .ge-row-icon .col-2 {
              flex: 0 0 16.66666667%;
              max-width: 16.66666667%;
            }
            .ge-row-icon .col-3 {
              flex: 0 0 25%;
              max-width: 25%;
            }
            .ge-row-icon .col-4 {
              flex: 0 0 33.33333333%;
              max-width: 33.33333333%;
            }
            .ge-row-icon .col-5 {
              flex: 0 0 41.66666667%;
              max-width: 41.66666667%;
            }
            .ge-row-icon .col-6 {
              flex: 0 0 50%;
              max-width: 50%;
            }
            .ge-row-icon .col-7 {
              flex: 0 0 58.33333333%;
              max-width: 58.33333333%;
            }
            .ge-row-icon .col-8 {
              flex: 0 0 66.66666667%;
              max-width: 66.66666667%;
            }
            .ge-row-icon .col-9 {
              flex: 0 0 75%;
              max-width: 75%;
            }
            .ge-row-icon .col-10 {
              flex: 0 0 83.33333333%;
              max-width: 83.33333333%;
            }
            .ge-row-icon .col-11 {
              flex: 0 0 91.66666667%;
              max-width: 91.66666667%;
            }
            .ge-row-icon .col-12 {
              flex: 0 0 100%;
              max-width: 100%;
            }
            .ge-canvas.ge-editing .row, .ge-row-icon {
                display: flex;
                flex-wrap: wrap;
            }
        </style>
    @endpush

    {{-- FIELD JS - will be loaded in the after_scripts section --}}
    @push('crud_fields_scripts')

        <script src="{{ asset('packages/jquery-ui-dist/jquery-ui.min.js') }}"></script>

        <script>
            var html;
            var colClasses = ['col-lg-', 'col-sm-', 'col-'];
            var gridEditorHiddenInput = $('input[type=hidden][name={{ $field['name'] }}]');
            var gridEditorContentContainer = $('.content-container');
            var gridEditorFieldName = "{{ $field['name'] }}";

            jQuery(document).ready(function($) {

                $('div.modal').each(function(){
                    $(this).find('input, select, textarea')
                        .each(function(){
                            $(this).attr('data-name',$(this).attr('name'));
                            $(this).removeAttr("name");
                        });
                });

                loadGridBlocks();
            });

            /**
             * Initializes the grid editor.
             */
            function loadGridBlocks() {
                if (gridEditorHiddenInput.val()) {
                    const modal_blocks = JSON.parse(gridEditorHiddenInput.val());

                    for (var i = 0; i < modal_blocks.length; ++i) {
                        goDeep(modal_blocks[i])
                    }
                }

                $('.content-container').gridEditor({
                    new_row_layouts: [[12], [6,6], [9,3]],
                    json_output_field: gridEditorFieldName
                });
            }

            /**
             * Creates a new row, in case old values were present.
             */
            function createRow(node) {

                $('.template-forms').find('#modal_row_settings')
                                    .clone()
                                    .attr('id', 'modal-' + node['panel-id'])
                                    .appendTo('.block-forms');
                appendFormValues($('#modal-' + node['panel-id']),node['fields']);

                return $('<div class="row" />').attr('data-id',node['panel-id']);
            }

            /**
             * Creates a new column, in case old values were present.
             */
            function createColumn(node) {

                if (node['block-type'] == 'container') {

                    $('.template-forms').find('#modal_row_settings')
                                        .clone()
                                        .attr('id', 'modal-' + node['panel-id'])
                                        .appendTo('.block-forms');
                    appendFormValues($('#modal-' + node['panel-id']),node['fields']);

                    return $('<div class="column" />')
                        .addClass(colClasses.map(function(c) { return c + node['size']; }).join(' '))
                        .attr('data-id',node['panel-id'])
                        .attr('data-type','container')
                        .append(
                            '<div class="ge-content"></div>'
                            );
                } else {

                    $('.template-forms').find('#modal' + node['block-type'])
                                        .clone()
                                        .attr('id', 'modal-' + node['panel-id'])
                                        .appendTo('.block-forms');
                    appendFormValues($('#modal-' + node['panel-id']),node['fields']);

                    return $('<div class="column" />')
                        .addClass(colClasses.map(function(c) { return c + node.size; }).join(' '))
                        .attr('data-id',node['panel-id'])
                        .attr('data-type',node['block-type'])
                        .attr('data-type-name',node['block-type-name'])
                        .append(
                            '<div class="ge-content"><div class="container-choose form-inline"><div class="container-glyph" data-toggle="modal" data-target="#modal-' + node['panel-id'] + '"><span class="' + node['icon-type'] + '"></span><br>' + node['block-type-name'] + '</div></div></div>'
                            );
                }
            }

            /**
             * Appends old values to blocks that are already in the grid.
             */
            function appendFormValues(modal,fields) {
                modal.find('input, select, textarea').each(function() {
                    if (typeof fields[$(this).data('name')] === 'object') {
                      $(this).val(JSON.stringify(fields[$(this).data('name')]));
                    } else {
                      $(this).val(fields[$(this).data('name')]);
                    }
                });

                modal.find('select[data-old-value]').each(function() {
                    $(this).data('old-value', fields[$(this).data('name')]);
                });
            }

            var json_to_html;

            /**
             * Goes through the tree and appends new rows and columns.
             * Recursive.
             */
            function goDeep(node) {
                // create node row /col and append to parent
                if (node['panel-type'] == 'row') {
                    if (node['parent-id'] == '') {
                        $('.content-container').append(createRow(node));
                    } else if (node['parent-id'] != '') {
                        $('.content-container').find('[data-id="' + node['parent-id'] + '"]')
                                               .append(createRow(node));
                    }
                } else if (node['panel-type'] == 'column') {
                    $('.content-container').find('[data-id="' + node['parent-id'] + '"]')
                                            .append(createColumn(node));

                }

                if (node['children'].length) {
                    for (var i = 0; i < node['children'].length; ++i) {
                        goDeep(node['children'][i])
                    }
                }
            }

            /**
             * Checks if the string in a JSON.
             */
            function hasJsonStructure(str) {
                if (typeof str !== 'string') return false;
                try {
                    const result = JSON.parse(str);
                    return Object.prototype.toString.call(result) === '[object Object]'
                        || Array.isArray(result);
                } catch (err) {
                    return false;
                }
            }

            (function( $ ){

            $.fn.gridEditor = function( options ) {

                var self = this;
                var grideditor = self.data('grideditor');

                if ($('.content-container [data-id]').length) {

                    var block_index =  Math.max.apply(null, $('.content-container [data-id]').map(function() {
                        return parseInt($(this).attr("data-id").replace('block-',''));
                    }).get());
                } else {
                    var block_index = 0;
                }


                var template_type_select = $('.template-types').html();

                if (arguments[0] == 'getHtml') {
                    if (grideditor) {
                        grideditor.deinit();
                        var html = self.html();
                        grideditor.init();
                        return html;
                    } else {
                        return self.html();
                    }
                }

                if (arguments[0] == 'remove') {
                    if (grideditor) {
                        grideditor.remove();
                    }
                    return;
                }

                self.each(function(baseIndex, baseElem) {
                    baseElem = $(baseElem);

                    var settings = $.extend({
                        'new_row_layouts'   : [ // Column layouts for add row buttons
                                                [12],
                                                [6, 6],
                                                [4, 4, 4],
                                                [3, 3, 3, 3],
                                                [2, 2, 2, 2, 2, 2],
                                                [2, 8, 2],
                                                [4, 8],
                                                [8, 4]
                                            ],
                        'row_classes'       : [{ label: 'Example class', cssClass: 'example-class'}],
                        'col_classes'       : [{ label: 'Example class', cssClass: 'example-class'}],
                        'col_tools'         : [],
                        'row_tools'         : [],
                        'custom_filter'     : '',
                        'content_types'     : ['textarea'],
                        'valid_col_sizes'   : [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12],
                        'source_textarea'   : '',
                        'json_output_field' : ''
                    }, options);


                    // Elems
                    var canvas,
                        mainControls,
                        wrapper, // controls wrapper
                        addRowGroup,
                        htmlTextArea
                    ;
                    var colClasses = ['col-lg-', 'col-sm-', 'col-'];
                    var curColClassIndex = 0; // Index of the column class we are manipulating currently
                    var MAX_COL_SIZE = 12;

                    // Copy html to sourceElement if a source textarea is given
                    if (settings.source_textarea) {
                        baseElem.html($(settings.source_textarea).val());
                    }

                    // Wrap content if it is non-bootstrap
                    if (baseElem.children().length && !baseElem.find('div.row').length) {
                        var children = baseElem.children();
                        var newRow = $('<div class="row"><div class="col-lg-12"/></div>').appendTo(baseElem);
                        newRow.find('.col-lg-12').append(children);
                    }

                    setup();
                    init();

                    typeChoose();
                    $(document).on('click', '.save-block', function () {
                        saveToJSON();
                    });
                    // dismissModal();

                    function setup() {
                        /* Setup canvas */
                        canvas = baseElem.addClass('ge-canvas');

                        htmlTextArea = $('<textarea class="ge-html-output"/>').insertBefore(canvas);

                        /* Create main controls*/
                        mainControls = $('<div class="ge-mainControls" />').insertBefore(htmlTextArea);
                        wrapper = $('<div class="ge-wrapper ge-top" />').appendTo(mainControls);

                        // Add row
                        addRowGroup = $('<div class="ge-addRowGroup btn-group" />').appendTo(wrapper);
                        $.each(settings.new_row_layouts, function(j, layout) {
                            var btn = $('<a class="btn btn-sm btn-primary" />')
                                .attr('title', 'Add row ' + layout.join('-'))
                                .on('click', function() {
                                    var row = createRow().appendTo(canvas);
                                    layout.forEach(function(i) {
                                        createColumn(i).appendTo(row);
                                    });
                                    init();
                                })
                                .appendTo(addRowGroup)
                            ;

                            btn.append('<span class="fa fa-plus-circle"/>');

                            var layoutName = layout.join(' - ');
                            var icon = '<div class="row ge-row-icon">';
                            layout.forEach(function(i) {
                                icon += '<div class="column col-' + i + '"/>';
                            });
                            icon += '</div>';
                            btn.append(icon);
                        });

                        // Add the template load/save buttons to the jsGrid interface
                        if (gridEditorHiddenInput.data('templates')) {
                            addTemplateGroup = $('<div class="ge-addRowGroup btn-group float-right layout-field-load-templates-buttons"></div>').appendTo(wrapper);
                            loadTemplateButton = $('<a data-toggle="modal" data-target="#browseLayoutTemplatesModal" class="btn btn-xs btn-primary" title="Load Template"><i class="fa fa-folder-open"></i> &nbsp;  Load</a>').appendTo(addTemplateGroup);
                            loadTemplateButton = $('<a data-toggle="modal" data-target="#saveLayoutTemplateModal" class="btn btn-xs btn-primary" title="Save As Template"><i class="fa fa-save"></i> &nbsp; Save</a>').appendTo(addTemplateGroup);
                        }

                        // Make controls fixed on scroll
                        $(window).on('scroll', onScroll);
                    }

                    function onScroll(e) {
                        var $window = $(window);

                        if (
                            $window.scrollTop() > mainControls.offset().top &&
                            $window.scrollTop() < canvas.offset().top + canvas.height()
                        ) {
                            if (wrapper.hasClass('ge-top')) {
                                wrapper
                                    .css({
                                        left: wrapper.offset().left,
                                        width: wrapper.outerWidth(),
                                    })
                                    .removeClass('ge-top')
                                    .addClass('ge-fixed')
                                ;
                            }
                        } else {
                            if (wrapper.hasClass('ge-fixed')) {
                                wrapper
                                    .css({ left: '', width: '' })
                                    .removeClass('ge-fixed')
                                    .addClass('ge-top')
                                ;
                            }
                        }
                    }

                    function reset() {
                        deinit();
                        init();
                    }

                    function init() {
                        runFilter(true);
                        canvas.addClass('ge-editing');
                        addAllColClasses();
                        wrapContent();
                        createRowControls();
                        createColControls();
                        makeSortable();
                    }

                    function deinit() {
                        canvas.removeClass('ge-editing');
                        canvas.find('.ge-tools-drawer').remove();
                        removeSortable();
                        runFilter(false);
                    }

                    function remove() {
                        deinit();
                        mainControls.remove();
                        htmlTextArea.remove();
                        $(window).off('scroll', onScroll);
                        canvas.removeData('grideditor');
                        canvas.html('');
                        $('.block-forms').html('');
                    }

                    function createRowControls() {
                        canvas.find('.row').each(function() {
                            var row = $(this);
                            var block_data_id = $(this).data('id');
                            if (row.find('> .ge-tools-drawer').length) { return; }

                            var drawer = $('<div class="ge-tools-drawer" />').prependTo(row);
                            createTool(drawer, 'Move', 'ge-move', 'fa fa-arrows-alt');

                            settings.row_tools.forEach(function(t) {
                                createTool(drawer, t.title || '', t.className || '', t.iconClass || 'glyphicon-wrench', t.on);
                            });
                            createTool(drawer, 'Row Settings', 'row-settings', 'fa fa-cog', function () {
                                $('#modal-' + block_data_id).modal('show');
                            });
                            createTool(drawer, 'Add column', 'ge-add-column', 'fa fa-plus-circle', function () {
                                row.append(createColumn(6));

                                init();
                            });
                            createTool(drawer, 'Clone row', 'clone-block', 'fa fa-clone', function () {
                                row.parent().append(cloneRow(row));

                                init();
                                saveToJSON();
                            });
                            createTool(drawer, 'Remove row', '', 'fa fa-trash', function () {
                                if (window.confirm('Delete row?')) {
                                    row.slideUp(function () {
                                        removeRow(row);
                                    });
                                }
                            });
                        });
                    }

                    function createColControls() {
                        canvas.find('.column').each(function() {
                            var col = $(this);
                            var block_data_id = $(this).data('id');
                            if (col.find('> .ge-tools-drawer').length) { return; }

                            var drawer = $('<div class="ge-tools-drawer" />').prependTo(col);

                            createTool(drawer, 'Move', 'ge-move', 'fa fa-arrows-alt');

                            createTool(drawer, 'Make column narrower\n(hold shift for min)', 'ge-decrease-col-width', 'fa fa-minus-square-o', function(e) {
                                var colSizes = settings.valid_col_sizes;
                                var curColClass = colClasses[curColClassIndex];
                                var curColSizeIndex = colSizes.indexOf(getColSize(col, curColClass));
                                var newSize = colSizes[clamp(curColSizeIndex - 1, 0, colSizes.length - 1)];
                                if (e.shiftKey) {
                                    newSize = colSizes[0];
                                }
                                setColSize(col, curColClass, Math.max(newSize, 1));
                            });

                            createTool(drawer, 'Make column wider\n(hold shift for max)', 'ge-increase-col-width', 'fa fa-plus-square-o', function(e) {
                                var colSizes = settings.valid_col_sizes;
                                var curColClass = colClasses[curColClassIndex];
                                var curColSizeIndex = colSizes.indexOf(getColSize(col, curColClass));
                                var newColSizeIndex = clamp(curColSizeIndex + 1, 0, colSizes.length - 1);
                                var newSize = colSizes[newColSizeIndex];
                                if (e.shiftKey) {
                                    newSize = colSizes[colSizes.length - 1];
                                }
                                setColSize(col, curColClass, Math.min(newSize, MAX_COL_SIZE));
                            });

                            settings.col_tools.forEach(function(t) {
                                createTool(drawer, t.title || '', t.className || '', t.iconClass || 'glyphicon-wrench', t.on);
                            });
                            createTool(drawer, 'Clone block', 'clone-block', 'fa fa-clone', function () {

                                col.parent().append(cloneColumn(col));

                                init();
                                saveToJSON();
                            });

                            createTool(drawer, 'Remove col', '', 'fa fa-trash', function () {
                                if (window.confirm('Delete column?')) {
                                    col.animate({
                                        opacity: 'hide',
                                        width: 'hide',
                                        height: 'hide'
                                    }, 400, function () {
                                        removeColumn(col);
                                    });
                                }
                            });
                        });
                    }

                    function removeRow(row) {
                        row.children('div.column').each(function () {
                            removeColumn($(this));
                        })

                        if (row.parent().hasClass('ge-content')) {
                            row.parent().html(
                                template_type_select
                            )
                        } else {
                            row.remove();
                        }
                        removeModal(row.data('id'));
                    }

                    function removeColumn(col) {

                        col.children('.ge-content')
                            .children('.row')
                            .each(function () {
                                removeRow($(this));
                            });

                        removeModal(col.data('id'));
                        col.remove();
                    }

                    function createTool(drawer, title, className, iconClass, eventHandlers) {
                        var tool = $('<a title="' + title + '" class="' + className + '"><span class="' + iconClass + '"></span></a>')
                            .appendTo(drawer)
                        ;
                        if (typeof eventHandlers == 'function') {
                            tool.on('click', eventHandlers);
                        }
                        if (typeof eventHandlers == 'object') {
                            $.each(eventHandlers, function(name, func) {
                                tool.on(name, func);
                            });
                        }
                        if (className != 'ge-add-column' && className != 'ge-move') {
                            saveToJSON();
                        }
                    }

                    function addAllColClasses() {
                        canvas.find('.column, div[class*="col-"]').each(function() {
                            var col = $(this);

                            var size = 2;
                            var sizes = getColSizes(col);
                            if (sizes.length) {
                                size = sizes[0].size;
                            }

                            var elemClass = col.attr('class');
                            colClasses.forEach(function(colClass) {
                                if (elemClass.indexOf(colClass) == -1) {
                                    col.addClass(colClass + size);
                                }
                            });

                            col.addClass('column');
                        });
                    }

                    function getColSize(col, colClass) {
                        var sizes = getColSizes(col);
                        for (var i = 0; i < sizes.length; i++) {
                            if (sizes[i].colClass == colClass) {
                                return sizes[i].size;
                            }
                        }
                        if (sizes.length) {
                            return sizes[0].size;
                        }
                        return null;
                    }

                    function getColSizes(col) {
                        var result = [];
                        colClasses.forEach(function(colClass) {
                            var re = new RegExp(colClass + '(\\d+)', 'i');
                            if (re.test(col.attr('class'))) {
                                result.push({
                                    colClass: colClass,
                                    size: parseInt(re.exec(col.attr('class'))[1])
                                });
                            }
                        });
                        return result;
                    }

                    function setColSize(col, colClass, size) {
                        var re = new RegExp('(' + colClass + '(\\d+))', 'i');
                        var reResult = re.exec(col.attr('class'));
                        if (reResult && parseInt(reResult[2]) !== size) {
                            col.switchClass(reResult[1], colClass + size, 50);
                        } else {
                            col.addClass(colClass + size);
                        }
                        setTimeout(function() {
                          saveToJSON();
                        }, 100);
                    }

                    function makeSortable() {
                        canvas.find('.row').sortable({
                            items: '> .column',
                            connectWith: '.ge-canvas .row',
                            handle: '> .ge-tools-drawer .ge-move',
                            helper: 'clone',
                            start: sortStart,
                            stop: function() {
                                saveToJSON();
                            }
                        });
                        canvas.add(canvas.find('.column')).sortable({
                            items: '> .row, > .ge-content',
                            connectsWith: '.ge-canvas, .ge-canvas .column',
                            handle: '> .ge-tools-drawer .ge-move',
                            helper: 'clone',
                            start: sortStart,
                            stop: function() {
                                saveToJSON();
                            }
                        });

                        function sortStart(e, ui) {
                            ui.placeholder.css({ height: ui.item.outerHeight()});
                        }
                    }

                    function removeSortable() {
                        canvas.add(canvas.find('.column')).add(canvas.find('.row')).sortable('destroy');
                    }

                    function createRow() {

                        block_index++;

                        $('.template-forms').find('#modal_row_settings')
                            .clone()
                            .attr('id', 'modal-block-' + block_index)
                            .appendTo('.block-forms');

                        initializeFieldsWithJavascript($('#modal-block-' + block_index));

                        return $('<div class="row" />').attr('data-id','block-' + block_index);
                    }

                    function createColumn(size) {

                        block_index++;

                        return $('<div/>')
                            .addClass(colClasses.map(function(c) { return c + size; }).join(' '))
                            .attr('data-id','block-' + block_index)
                            .append(createDefaultContentWrapper().html(
                                    template_type_select
                                )
                            )
                        ;
                    }

                    function cloneRow(row) {

                        block_index++;

                        var new_row = $('<div class="row" />').attr('data-id', 'block-' + block_index);
                        cloneModal(row.data('id'));

                        row.children('.column').each(function () {
                            new_row.append(cloneColumn($(this)));
                        });

                        return new_row;
                    }

                    function cloneColumn(col) {

                        if (col.data('type') == null) {
                            return createColumn(getColSize(col));
                        } else {
                            return createColumnCopy(col);
                        }
                    }

                    function createColumnCopy(col) {
                        block_index++;
                        var col_data_type = col.data('type');
                        var col_data_type_name = col.data('type-name');
                        var col_icon_type = col.find('div.container-glyph span.fa').attr('class');

                        if (col_data_type == 'container') {

                            var new_col;
                            col.children('.ge-content')
                                .children('.row')
                                .each(function() {

                                    var new_row = $(this);
                                    new_col = $('<div/>')
                                        .addClass(colClasses.map(function (c) { return c + getColSize(col); }).join(' '))
                                        .attr('data-id', 'block-' + block_index)
                                        .attr('data-type', col_data_type)
                                        .append(
                                            createDefaultContentWrapper().append(cloneRow(new_row))
                                        );
                            });
                            return new_col;

                        } else {

                            cloneModal(col.data('id'));

                            return $('<div/>')
                                .addClass(colClasses.map(function (c) { return c + getColSize(col); }).join(' '))
                                .attr('data-id', 'block-' + block_index)
                                .attr('data-type', col_data_type)
                                .attr('data-type-name', col_data_type_name)
                                .append(createDefaultContentWrapper().html(
                                    '<div class="container-glyph" data-toggle="modal" data-target="#modal-block-' + block_index + '"><span class="' + col_icon_type + '"/><br>' + col_data_type_name + '</div>'
                                )
                            );

                        }
                    }

                    /**
                     * Replaces the empty dropdown block with a content block.
                     */
                    function typeChoose(){
                        $(document).on('click', '.type-choose', function () {

                            var option_val = $(this).parent()
                                                    .find('select option:selected')
                                                    .val();

                            var option_name = $(this).parent()
                                                    .find('select option:selected')
                                                    .text();

                            var option_icon = $(this).parent()
                                                    .find('select option:selected')
                                                    .data('icon');

                            var col = $(this).closest('.column');

                            col.attr('data-type',option_val);
                            col.attr('data-type-name', option_name);

                            if (option_val != 'container') {

                                $('.template-forms').find('#modal' + option_val)
                                                    .clone()
                                                    .attr('id', 'modal-' + col.data('id'))
                                                    .appendTo('.block-forms');

                                initializeFieldsWithJavascript($('#modal-' + col.data('id')));

                                $(this).parent()
                                        .html('<div class="container-glyph" data-toggle="modal" data-target="#modal-' + col.data('id') + '"><span class="' + option_icon + '"/><br>' + option_name + '</div>');

                            } else {

                                $('.template-forms').find('#modal_row_settings')
                                                    .clone()
                                                    .attr('id', 'modal-' + col.data('id'))
                                                    .appendTo('.block-forms');

                                initializeFieldsWithJavascript($('#modal-' + col.data('id')));

                                var row = createRow();

                                $(this).closest('.ge-content')
                                    .html(row);

                                row.append(createColumn(6))
                                .append(createColumn(6));

                                init();
                            }

                            saveToJSON();
                        });
                    }


                    /* function dismissModal(data_id) {
                        $('.modal').on('hidden.bs.modal', function () {
                            var block_id = $(this).attr('id').replace('modal-','');
                            $(this).data('bs.modal', null);
                            recoverModalFromJSON(modal_id);
                        })
                    }

                    function recoverModalFromJSON(block_id) {
                        var json_block = JSON.parse($('input[type=hidden][name=' + settings.json_output_field + ']').val());
                        for (var i = 0; i < json_block.length; ++i) {
                            goDeep(json_block[i])
                        }
                    } */

                    function removeModal(data_id) {
                        if ($('#modal-' + data_id).length) {
                            $('#modal-' + data_id).remove();
                        }
                        saveToJSON();
                    }

                    function cloneModal(data_id) {

                        $('#modal-' + data_id).clone()
                            .attr('id', 'modal-block-' + block_index)
                            .appendTo('.block-forms');

                        $('#modal-block-' + block_index).find('select').each(function(){
                            $(this).val($('#modal-' + data_id).find('select[data-name='+$(this).data('name')+']').val());
                        });

                        initializeFieldsWithJavascript($('#modal-block-' + block_index));
                    }

                    function runFilter(isInit) {
                        if (settings.custom_filter.length) {
                            $.each(settings.custom_filter, function(key, func) {
                                if (typeof func == 'string') {
                                    func = window[func];
                                }

                                func(canvas, isInit);
                            });
                        }
                    }

                    function wrapContent() {
                        canvas.find('.column').each(function() {
                            var col = $(this);
                            var contents = $();
                            col.children().each(function() {
                                var child = $(this);
                                if (child.is('.row, .ge-tools-drawer, .ge-content')) {
                                    doWrap(contents);
                                } else {
                                    contents = contents.add(child);
                                }
                            });
                            doWrap(contents);
                        });
                    }

                    function doWrap(contents) {
                        if (contents.length) {
                            var container = createDefaultContentWrapper().insertAfter(contents.last());
                            contents.appendTo(container);
                            contents = $();
                        }
                    }

                    function createDefaultContentWrapper() {
                        return $('<div/>')
                            .addClass('ge-content')
                        ;
                    }

                    function saveToJSON() {
                        $('input[type=hidden][name=' + settings.json_output_field + ']').val(JSON.stringify(listToTree(blocksToObj())));
                    }


                    function listToTree(list) {
                        var map = {}, node, roots = [], i;
                        for (i = 0; i < list.length; i += 1) {
                            map[list[i]['panel-id']] = i;
                            list[i].children = [];
                        }
                        for (i = 0; i < list.length; i += 1) {
                            node = list[i];
                            if (node['parent-id'] !== "") {
                                list[map[node['parent-id']]].children.push(node);
                            } else {
                                roots.push(node);
                            }
                        }

                        return roots;
                    }

                    function blocksToObj() {

                        var content_container = $('.ge-canvas').clone();
                        content_container.find('.ge-tools-drawer').remove();

                        var content_arr = [], parent_id, element = {};

                        content_container.find('.row, .column').each(function(){

                            if ($(this).hasClass('row')) {

                              if ($(this).children('.column').data('type')) {
                                if ($(this).closest('.column').length) {
                                    parent_id = $(this).closest('.column').data('id');
                                } else {
                                    parent_id = '';
                                }

                                element = { 'panel-id': $(this).data('id'), 'panel-type': 'row', 'fields': inputToObj($('#modal-' + $(this).data('id'))), 'parent-id': parent_id }
                                content_arr.push(element);
                              }

                            } else if ($(this).hasClass('column')) {

                              if (!$(this).children('.ge-content').children('.row').length || $(this).children('.ge-content').children('.row').children('.column').data('type')) {
                                if ($(this).data('type')) {
                                    parent_id = $(this).closest('.row').data('id');
                                    element = { 'panel-id': $(this).data('id'), 'panel-type': 'column', 'size': getColSize($(this)), 'block-type': $(this).data('type'), 'block-type-name': $(this).data('type-name'), 'icon-type': $(this).find('span.fa').attr('class'), 'fields': inputToObj($('#modal-' + $(this).data('id'))), 'parent-id': parent_id }
                                    content_arr.push(element);
                                }
                              }
                            }

                        });

                        return content_arr;
                    }

                    function inputToObj(container) {
                        var obj = {};

                        container.find('input, select, textarea').each(function(){
                            if ($(this).data('name') && $(this).val()) {

                                if (hasJsonStructure($(this).val())) {
                                    obj[$(this).data('name')] = JSON.parse($(this).val());
                                } else {
                                    obj[$(this).data('name')] = $(this).val();
                                }
                            }
                        });

                        return obj;
                    }

                    function clamp(input, min, max) {
                        return Math.min(max, Math.max(min, input));
                    }

                    baseElem.data('grideditor', {
                        init: init,
                        deinit: deinit,
                        remove: remove,
                    });

                });

                return self;

            };

            $.fn.gridEditor.RTEs = {};

            })( jQuery );

        </script>

        {{-- Template Modals Functionality --}}
        <script>
            var existingLayoutTemplates;

            function templateFilenameExists(filename) {
              var result = false;

              jQuery.each(existingLayoutTemplates, function (i, val) {
                if (val.replace('.json', '') == filename) {
                  result = true;
                }
              });

              return result;
            }

            // ----------------
            // Save as Template
            // ----------------
            $('#saveLayoutTemplateModal').on('shown.bs.modal', function (e) {
                // put the cursor on the first and only input
                $("#newTemplateName").focus();

                // get the existing templates so we can double-check the names
                var templatesRoute = gridEditorHiddenInput.data('templates-route');
                var modalBody = $('#saveLayoutTemplateModal .modal-body');

                $.ajax({
                    url: templatesRoute,
                    type: 'GET',
                    dataType: 'json',
                })
                .done(function(response) {
                    existingLayoutTemplates = response;
                })
                .fail(function() {
                    new PNotify({
                        title: "Error fetching templates",
                        text: "Could not find templates. Please try again.",
                        type: "error",
                        icon: false
                    });
                });
            });

            // prevent submitting the entire main form with Enter
            // make Enter trigger a click on the modal button
            $('#saveLayoutTemplateModal').keydown(function(e) {
                  var keyCode = e.keyCode || e.which;

                  if (keyCode === 13) {
                    e.preventDefault();
                    $("#saveLayoutTemplateModal button[data-trigger=save]").trigger('click');
                  }
            });

            function bpSaveLayoutTemplate() {
                var input = $("#saveLayoutTemplateModal input#newTemplateName");
                var templateName = input.val();
                var templatesRoute = gridEditorHiddenInput.data('templates-route');

                // check that the filename does not already exist
                if (templateFilenameExists(templateName)) {
                  confirmation = window.confirm('Filename already exists. Are you sure you want to overwrite it?');
                } else {
                  confirmation = true;
                }

                if (!confirmation) {
                  return;
                }

                // make the ajax call
                $.ajax({
                    url: templatesRoute,
                    type: 'POST',
                    data: {
                        content: gridEditorHiddenInput.val(),
                        name: templateName,
                    },
                    success: function(data) {
                        new PNotify({
                            title: "Done",
                            text: "The current layout has been saved as a template.",
                            type: "success",
                            icon: false
                        });

                        // clear the input
                        input.val('');

                    },
                    error: function() {
                        new PNotify({
                            title: "Failed",
                            text: "The template could not be saved. Please try again.",
                            type: "error",
                            icon: false
                        });
                    }
                });
            }

            // -----------------------
            // Browse & Load Templates
            // -----------------------

            $('#browseLayoutTemplatesModal').on('show.bs.modal', function (e) {
                var templatesRoute = gridEditorHiddenInput.data('templates-route');
                var modalBody = $('#browseLayoutTemplatesModal .modal-body');

                $.ajax({
                    url: templatesRoute,
                    type: 'GET',
                    dataType: 'json',
                })
                .done(function(response) {
                    modalBody.html(function() {
                        var html = "<table class='table table-striped table-bordered table-condensed m-b-0'>";

                        for (var key in response) {
                            var filename = response[key].replace('.json', '');

                            html += '<tr data-template-id="'+key+'">';
                                html += '<td>';
                                    html += ' &nbsp; ' + filename;
                                html += '</td>';
                                html += '<td>';
                                    // html += '<a class="btn btn-link btn-xs" onclick="bpAppendLayoutTemplate(\''+key+'\')"><i class="fa fa-plus"></i> Append</a> ';
                                    html += '<a class="btn btn-link btn-xs" onclick="bpInsertLayoutTemplate(\''+key+'\', \''+filename+'\')"><i class="fa fa-plus"></i> Use Template</a> ';
                                    html += '<a class="btn btn-link btn-xs" onclick="bpDeleteLayoutTemplate(\''+key+'\')"><i class="fa fa-trash"></i> Delete</a> ';
                                html += '</td>';
                            html += '</tr>';
                        }

                        html += "</table>"

                        return html;
                    });

                })
                .fail(function() {
                    new PNotify({
                        title: "Error fetching templates",
                        text: "Could not find templates. Please try again.",
                        type: "error",
                        icon: false
                    });
                });
            });

            function bpInsertLayoutTemplate(id, filename) {
                var templatesRoute = gridEditorHiddenInput.data('templates-route');

                if (gridEditorHiddenInput.val()) {
                    if (!confirm('You will lose all existing blocks. Are you sure?')) {
                        return;
                    }
                }

                // fetch the template contents with JS
                $.ajax({
                    url: templatesRoute+'/'+id,
                    type: 'GET',
                    // dataType: 'json',
                })
                .done(function(response) {
                    // replace the current layout with the one in the template
                    gridEditorHiddenInput.val('');
                    gridEditorContentContainer.gridEditor('remove');
                    gridEditorHiddenInput.val(response);
                    loadGridBlocks();

                    $('#browseLayoutTemplatesModal').modal('hide');
                    $('#saveLayoutTemplateModal #newTemplateName').val(filename);
                })
                .fail(function() {
                    new PNotify({
                        title: "Error loading template",
                        text: "Could not find the template. Please try again.",
                        type: "error",
                        icon: false
                    });
                });

            }

            function bpDeleteLayoutTemplate(id) {
                var templatesRoute = gridEditorHiddenInput.data('templates-route');

                if (!confirm('Are you sure you want to delete this template?')) {
                    return;
                }

                $.ajax({
                    url: templatesRoute+'/'+id,
                    type: 'DELETE',
                })
                .done(function(response) {
                    if (response === 'true') {
                        // show success notification
                        new PNotify({
                            title: "Done",
                            text: "Template deleted.",
                            type: "success",
                            icon: 'fa fa-trash'
                        });

                        // remove the table row
                        $('#browseLayoutTemplatesModal tr[data-template-id="'+id+'"]').remove();
                    } else {
                        // show warning notification
                        new PNotify({
                            title: "Not Deleted",
                            text: "Could not delete the template. Please try again.",
                            type: "warning",
                            icon: 'fa fa-warning'
                        });
                    }
                })
                .fail(function() {
                    new PNotify({
                        title: "Error deleting template",
                        text: "Could not delete template. Please try again.",
                        type: "error",
                        icon: false
                    });
                });

            }
        </script>
    @endpush

@endif
{{-- End of Extra CSS and JS --}}
{{-- ########################################## --}}
