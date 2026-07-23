@php
    $field['wrapper'] = $field['wrapper'] ?? $field['wrapperAttributes'] ?? [];
    $field['wrapper']['data-init-function'] = $field['wrapper']['data-init-function'] ?? 'bpFieldInitUploadMultipleElement';
    $field['wrapper']['data-field-name'] = $field['wrapper']['data-field-name'] ?? $field['name'];

	if(isset($field['parentFieldName'])) {
		if(!empty(old())) {
			$field['value'] = array_merge(
								explode(',',Arr::get(old(), '_order_'.square_brackets_to_dots($field['name'])) ?? ''),
								Arr::get(old(), 'clear_'.square_brackets_to_dots($field['name'])) ?? [],
							);
			$field['value'] = is_array($field['value']) ? array_filter($field['value'] ?? []) : [];
			$field['value'] = $field['value'] === [null] || $field['value'] === [""] ? null : $field['value'];
		}
	}
@endphp

{{-- upload multiple input --}}
@include('crud::fields.inc.wrapper_start')
    <label>{!! $field['label'] !!}</label>
    @include('crud::fields.inc.translatable_icon')

	{{-- Show the file name and a "Clear" button on EDIT form. --}}
	@if (isset($field['value']))
	@php
		if (is_string($field['value'])) {
			$values = json_decode($field['value'], true) ?? [];
		} else {
			$values = $field['value'];
		}
	@endphp
	@if (count($values))
    <div class="well well-sm existing-file mb-2">
    	@foreach($values as $key => $file_path)
    		<div class="file-preview">
    			@if (isset($field['temporary']))
		            <a target="_blank" href="{{ isset($field['disk'])?asset(\Storage::disk($field['disk'])->temporaryUrl($file_path, Carbon\Carbon::now()->addMinutes($field['expiration']))):asset($file_path) }}">{{ $file_path }}</a>
		        @else
		            <a target="_blank" href="{{ isset($field['disk'])?asset(\Storage::disk($field['disk'])->url($file_path)):asset($file_path) }}">{{ $file_path }}</a>
		        @endif
		    	<a href="#" class="btn btn-light btn-sm float-right file-clear-button" title="Clear file" data-filename="{{ $file_path }}"><i class="la la-remove"></i></a>
		    	<div class="clearfix"></div>
	    	</div>
    	@endforeach
    </div>
    @endif
    @endif
	{{-- Show the file picker on CREATE form. --}}
	<input name="{{ $field['name'] }}[]" type="hidden" value="">
	<div class="backstrap-file">
		<input
	        type="file"
	        name="{{ $field['name'] }}[]"
	        @include('crud::fields.inc.attributes', ['default_class' => 'file_input backstrap-file-input'])
	        multiple
	    >
        <label class="backstrap-file-label" for="customFile"></label>
    </div>

    {{-- HINT --}}
    @if (isset($field['hint']))
        <p class="help-block">{!! $field['hint'] !!}</p>
    @endif

	@include('crud::fields.inc.wrapper_end')

{{-- ########################################## --}}
{{-- Extra CSS and JS for this particular field --}}
	@push('crud_fields_styles')
	@bassetBlock('backpack/crud/fields/upload-multiple-field.css')
	<style type="text/css">
		.existing-file {
			border: 1px solid rgba(0,40,100,.12);
			border-radius: 5px;
			padding-left: 10px;
			vertical-align: middle;
		}
		.existing-file a {
			padding-top: 5px;
			display: inline-block;
			font-size: 0.9em;
		}
		.backstrap-file {
			position: relative;
			display: inline-block;
			width: 100%;
			height: calc(1.5em + 0.75rem + 2px);
			margin-bottom: 0;
		}

		.backstrap-file-input {
			position: relative;
			z-index: 2;
			width: 100%;
			height: calc(1.5em + 0.75rem + 2px);
			margin: 0;
			opacity: 0;
		}

		.backstrap-file-input:focus ~ .backstrap-file-label {
			border-color: #acc5ea;
			box-shadow: 0 0 0 0rem rgba(70, 127, 208, 0.25);
		}

		.backstrap-file-input:disabled ~ .backstrap-file-label {
			background-color: #e4e7ea;
		}

		.backstrap-file-input:lang(en) ~ .backstrap-file-label::after {
			content: "Browse";
		}

		.backstrap-file-input ~ .backstrap-file-label[data-browse]::after {
			content: attr(data-browse);
		}

		.backstrap-file-label {
			position: absolute;
			top: 0;
			right: 0;
			left: 0;
			z-index: 1;
			height: calc(1.5em + 0.75rem + 2px);
			padding: 0.375rem 0.75rem;
			font-weight: 400;
			line-height: 1.5;
			color: #5c6873;
			background-color: #fff;
			border: 1px solid #e4e7ea;
			border-radius: 0.25rem;
			font-weight: 400!important;
		}

		.backstrap-file-label[has-selected-files=true] {
			display: inline-table;
			width: 100%;
		}

		.backstrap-file-label[has-selected-files=true] .badge {
			margin-right: 5px;
			margin-bottom: 5px;
		}

		.backstrap-file-label::after {
			position: absolute;
			top: 0;
			right: 0;
			bottom: 0;
			z-index: 3;
			display: block;
			height: calc(1.5em + 0.75rem);
			padding: 0.375rem 0.75rem;
			line-height: 1.5;
			color: #5c6873;
			content: "Browse";
			background-color: #f0f3f9;
			border-left: inherit;
			border-radius: 0 0.25rem 0.25rem 0;
		}
	</style>
	@endBassetBlock
	@endpush

    @push('crud_fields_scripts')
    	@bassetBlock('backpack/crud/fields/upload-multiple-field.js')
        <script>
        	function bpFieldInitUploadMultipleElement(element) {
        		var wrapper = element[0];
        		var fieldName = wrapper.getAttribute('data-field-name');
        		var clearFileButtons = wrapper.querySelectorAll('.file-clear-button');
        		var fileInput = wrapper.querySelector('input[type=file]');
        		var inputLabel = wrapper.querySelector('label.backstrap-file-label');
				var existingFiles = fileInput.parentElement.querySelector('.existing-file') || 
				                     Array.from(fileInput.parentElement.children).find(function(el) { return el.matches('.existing-file'); });
				var isFieldDisabled = false;

				// Helper: get existing files container (may be dynamically created)
				var getExistingFiles = function() {
					return wrapper.querySelector('.existing-file');
				};

				if(fileInput.getAttribute('data-row-number')) {
					let selectedFiles = [];
					if (existingFiles) {
						existingFiles.querySelectorAll('a.file-clear-button').forEach(function(btn) {
							selectedFiles.push(btn.dataset.filename);
						});
					}

					var orderInput = document.createElement('input');
					orderInput.type = 'hidden';
					orderInput.className = 'order-uploads';
					orderInput.name = '_order_'+fieldName;
					orderInput.value = selectedFiles.join(',');
					fileInput.insertAdjacentElement('afterend', orderInput);

					var observer = new MutationObserver(function(mutations) {
						mutations.forEach(function(mutation) {
							if(mutation.attributeName == 'data-row-number') {
								let target = mutation.target;
								var baseName = target.getAttribute('name').slice(0, -2);

								// Find the order input sibling
								var fieldOrder = null;
								Array.from(target.parentElement.children).forEach(function(child) {
									if (child.name === baseName && child !== target) {
										fieldOrder = child;
									}
								});
								if (fieldOrder) {
									fieldOrder.setAttribute('name', '_order_'+baseName);
									let selectedFiles = [];
									var ef = getExistingFiles();
									if (ef) {
										ef.querySelectorAll('a.file-clear-button').forEach(function(btn) {
											selectedFiles.push(btn.dataset.filename);
										});
									}
									fieldOrder.value = selectedFiles.join(',');
								}

								// Find the clear-files sibling
								var fieldClear = target.parentElement.querySelector('.clear-files');
								if (fieldClear) {
									fieldClear.setAttribute('name', 'clear_'+target.getAttribute('name'));
								}
							}
						});
					});

					observer.observe(fileInput, {
						attributes: true,
					});
				}

				// Wire up clear buttons
		        clearFileButtons.forEach(function(btn) {
		        	btn.addEventListener('click', function(e) {
		        		if (isFieldDisabled) return;
		        		e.preventDefault();
		        		var filePreview = this.closest('.file-preview');
		        		var container = filePreview ? filePreview.parentElement : this.parentElement.parentElement;
		        		// remove the filename and button
		        		if (filePreview) {
		        			filePreview.remove();
		        		} else {
		        			this.parentElement.remove();
		        		}

						if(fileInput.getAttribute('data-row-number')) {
							let selectedFiles = [];
							var ef = getExistingFiles();
							if (ef) {
								ef.querySelectorAll('a.file-clear-button').forEach(function(b) {
									selectedFiles.push(b.dataset.filename);
								});
							}
							var orderUploads = fileInput.parentElement.querySelector('.order-uploads');
							if (orderUploads) {
								if(selectedFiles.length > 0) {
									orderUploads.value = selectedFiles.join(',');
								} else {
									orderUploads.remove();
								}
							}
						}
		        		// if the file container is empty, remove it
		        		if (container && container.innerHTML.trim() === '') {
		        			container.remove();
		        		}
		        		var clearInput = document.createElement('input');
		        		clearInput.type = 'hidden';
		        		clearInput.className = 'clear-files';
		        		clearInput.name = 'clear_'+fieldName+'[]';
		        		clearInput.value = this.dataset.filename;
		        		fileInput.insertAdjacentElement('afterend', clearInput);
		        	});
		        });

		        // accumulate files across multiple picks (browser replaces FileList before change fires)
		        var accumulatedDt = new DataTransfer();

		        fileInput.addEventListener('change', function() {
					let existingFilesEl = getExistingFiles();

					// capture newly picked files first (fileInput.files is already replaced by browser)
					let newlyPicked = Array.from(this.files);

					// add to accumulated DataTransfer, skip duplicates by name
					newlyPicked.forEach(function(file) {
						var alreadyAdded = Array.from(accumulatedDt.files).some(function(f) { return f.name === file.name; });
						if (!alreadyAdded) {
							accumulatedDt.items.add(file);
						}
					});

					// assign the full accumulated list back to the input
					fileInput.files = accumulatedDt.files;

					let allFiles = Array.from(accumulatedDt.files).map(function(file) {
						return {name: file.name, type: file.type};
					});

					// update the first hidden input
					var firstHidden = wrapper.querySelector('input[type=hidden]');
					if (firstHidden) {
						firstHidden.value = JSON.stringify(allFiles);
						firstHidden.dispatchEvent(new Event('change'));
					}

					// create badges only for the newly picked files
					var filesHtml = '';
					newlyPicked.forEach(function(file) {
						filesHtml += '<span class="badge mt-1 mb-1 text-bg-secondary badge-primary new-file-badge" data-filename="'+file.name+'">'
						       + file.name
						       + ' <a href="#" class="new-file-remove" data-filename="'+file.name+'" style="color:inherit;margin-left:4px;text-decoration:none;">&times;</a>'
						       + '</span> ';
					});

					// if existing files container is not on the page, create it
					if(!existingFilesEl) {
						existingFilesEl = document.createElement('div');
						existingFilesEl.className = 'well well-sm existing-file mb-2';
						var firstInput = wrapper.querySelector('input[type=hidden]');
						if (firstInput) {
							firstInput.insertAdjacentElement('beforebegin', existingFilesEl);
						} else {
							wrapper.insertBefore(existingFilesEl, wrapper.firstChild);
						}
						existingFilesEl.innerHTML = filesHtml;
					} else {
						existingFilesEl.insertAdjacentHTML('beforeend', filesHtml);
					}

					// Wire up remove buttons on newly created badges
					existingFilesEl.querySelectorAll('.new-file-remove').forEach(function(removeBtn) {
						removeBtn.addEventListener('click', function(e) {
							e.preventDefault();
							var filenameToRemove = this.dataset.filename;

							// rebuild both accumulatedDt and FileList without the removed file
							var dt = new DataTransfer();
							Array.from(accumulatedDt.files).forEach(function(file) {
								if (file.name !== filenameToRemove) {
									dt.items.add(file);
								}
							});
							accumulatedDt = dt;
							fileInput.files = accumulatedDt.files;

							// remove the badge from the DOM
							this.closest('.new-file-badge').remove();

							// remove the existing-file container if now empty
							var efEl = getExistingFiles();
							if (efEl && efEl.innerHTML.trim() === '') {
								efEl.remove();
							}

							// update the hidden input with remaining files
							var remainingFiles = Array.from(fileInput.files).map(function(file) {
								return {name: file.name, type: file.type};
							});
							var fh = wrapper.querySelector('input[type=hidden]');
							if (fh) {
								fh.value = JSON.stringify(remainingFiles);
								fh.dispatchEvent(new Event('change'));
							}
						});
					});

		        	// remove the hidden input, so that the setXAttribute method is no longer triggered
					var nextHidden = this.nextElementSibling;
					while (nextHidden) {
						if (nextHidden.type === 'hidden' && 
						    nextHidden.name !== 'clear_'+fieldName+'[]' && 
						    nextHidden.name !== '_order_'+fieldName) {
							var toRemove = nextHidden;
							nextHidden = nextHidden.nextElementSibling;
							toRemove.remove();
						} else {
							nextHidden = nextHidden.nextElementSibling;
						}
					}
		        });

				// CrudField disable/enable — listen on the first hidden input (where CrudField dispatches events)
				var primaryInput = wrapper.querySelector('input[type=hidden]');
				if (primaryInput) {
					primaryInput.addEventListener('CrudField:disable', function(e) {
						isFieldDisabled = true;
						var bpInput = wrapper.querySelector('.backstrap-file input');
						if (bpInput) bpInput.disabled = true;
					});

					primaryInput.addEventListener('CrudField:enable', function(e) {
						isFieldDisabled = false;
						var bpInput = wrapper.querySelector('.backstrap-file input');
						if (bpInput) bpInput.removeAttribute('disabled');
					});
				}
        	}
        </script>
        @endBassetBlock
    @endpush
