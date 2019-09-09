<!-- browse server input -->

<div @include('crud::inc.field_wrapper_attributes') >

    <label>{!! $field['label'] !!}</label>
    @include('crud::inc.field_translatable_icon')
    <div class="controls">
	    <div class="input-group">
			<input
				type="text"
				id="{{ $field['name'] }}-filemanager"
				name="{{ $field['name'] }}"
		        value="{{ old(square_brackets_to_dots($field['name'])) ?? $field['value'] ?? $field['default'] ?? '' }}"
		        data-init-function="bpFieldInitBrowseElement"
		        data-elfinder-trigger-url="{{ url(config('elfinder.route.prefix').'/popup') }}"
		        @include('crud::inc.field_attributes')

				@if(!isset($field['readonly']) || $field['readonly']) readonly @endif
			>

			<span class="input-group-append">
			  	<button type="button" data-inputid="{{ $field['name'] }}-filemanager" class="btn btn-outline-primary btn-sm popup_selector"><i class="fa fa-cloud-upload"></i> {{ trans('backpack::crud.browse_uploads') }}</button>
				<button type="button" data-inputid="{{ $field['name'] }}-filemanager" class="btn btn-outline-primary btn-sm clear_elfinder_picker"><i class="fa fa-eraser"></i> {{ trans('backpack::crud.clear') }}</button>
			</span>
		</div>
	</div>

	@if (isset($field['hint']))
        <p class="help-block">{!! $field['hint'] !!}</p>
    @endif

</div>

{{-- ########################################## --}}
{{-- Extra CSS and JS for this particular field --}}
{{-- If a field type is shown multiple times on a form, the CSS and JS will only be loaded once --}}
@if ($crud->fieldTypeNotLoaded($field))
	@php
		$crud->markFieldTypeAsLoaded($field);
	@endphp

	{{-- FIELD CSS - will be loaded in the after_styles section --}}
	@push('crud_fields_styles')
		<!-- include browse server css -->
		<link href="{{ asset('packages/jquery-colorbox/example2/colorbox.css') }}" rel="stylesheet" type="text/css" />
		<style>
			#cboxContent, #cboxLoadedContent, .cboxIframe {
				background: transparent;
			}
		</style>
	@endpush

	@push('crud_fields_scripts')
		<!-- include browse server js -->
		<script src="{{ asset('packages/jquery-colorbox/jquery.colorbox-min.js') }}"></script>
		<script type="text/javascript">
			// this global variable is used to remember what input to update with the file path
			// because elfinder is actually loaded in an iframe by colorbox
			var elfinderTarget = false;

			// function to update the file selected by elfinder
			function processSelectedFile(filePath, requestingField) {
				elfinderTarget.val(filePath.replace(/\\/g,"/"));
				elfinderTarget = false;
			}

			function bpFieldInitBrowseElement(element) {
				var triggerUrl = element.data('elfinder-trigger-url')
				var name = element.attr('name');

				element.siblings('.input-group-append').children('button.popup_selector').click(function (event) {
				    event.preventDefault();

				    elfinderTarget = element;

				    // trigger the reveal modal with elfinder inside
				    $.colorbox({
				        href: triggerUrl + '/' + name,
				        fastIframe: true,
				        iframe: true,
				        width: '80%',
				        height: '80%'
				    });
				});

				element.siblings('.input-group-append').children('button.clear_elfinder_picker').click(function (event) {
				    event.preventDefault();
				    element.val("");
				});
			}
		</script>
	@endpush

@endif

{{-- End of Extra CSS and JS --}}
{{-- ########################################## --}}
