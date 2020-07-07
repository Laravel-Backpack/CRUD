{{-- Select2 Ajax Backpack CRUD filter --}}

<li filter-name="{{ $filter->name }}"
	filter-type="{{ $filter->type }}"
	class="nav-item dropdown {{ Request::get($filter->name)?'active':'' }}">
    <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">{{ $filter->label }} <span class="caret"></span></a>
    <div class="dropdown-menu p-0 ajax-select">
	    <div class="form-group mb-0">
	    	<select id="filter_{{ $filter->name }}" name="filter_{{ $filter->name }}" class="form-control input-sm select2" data-filter-type="select2_ajax" data-filter-name="{{ $filter->name }}" placeholder="{{ $filter->placeholder }}">
				@if (Request::get($filter->name))
					<option value="{{ Request::get($filter->name) }}" selected="selected"> {{ Request::get($filter->name.'_text') ?? 'Previous selection' }} </option>
				@endif
			</select>
	    </div>
    </div>
  </li>

{{-- ########################################### --}}
{{-- Extra CSS and JS for this particular filter --}}

{{-- FILTERS EXTRA CSS  --}}
{{-- push things in the after_styles section --}}

@push('crud_list_styles')
    <!-- include select2 css-->
    <link href="{{ asset('packages/select2/dist/css/select2.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('packages/select2-bootstrap-theme/dist/select2-bootstrap.min.css') }}" rel="stylesheet" type="text/css" />
    <style>
	  .form-inline .select2-container {
	    display: inline-block;
	  }
	  .select2-drop-active {
	  	border:none;
	  }
	  .select2-container .select2-choices .select2-search-field input, .select2-container .select2-choice, .select2-container .select2-choices {
	  	border: none;
	  }
	  .select2-container-active .select2-choice {
	  	border: none;
	  	box-shadow: none;
	  }
	  .select2-container--bootstrap .select2-dropdown {
	  	margin-top: -2px;
	  	margin-left: -1px;
	  }
	  .select2-container--bootstrap {
	  	position: relative!important;
	  	top: 0px!important;
	  }
    </style>
@endpush


{{-- FILTERS EXTRA JS --}}
{{-- push things in the after_scripts section --}}

@push('crud_list_scripts')
	<!-- include select2 js-->
    <script src="{{ asset('packages/select2/dist/js/select2.full.min.js') }}"></script>
    @if (app()->getLocale() !== 'en')
    <script src="{{ asset('packages/select2/dist/js/i18n/' . app()->getLocale() . '.js') }}"></script>
    @endif

    <script>
        jQuery(document).ready(function($) {
            // trigger select2 for each untriggered select2 box
            $('#filter_{{ $filter->name }}').each(function () {

            	// if the filter has already been initialised, do nothing
            	if ($(this).attr('data-initialised')) {
            		return;
            	} else {
	            	$(this).attr('data-initialised', 'true');
            	}

            	var filterName = $(this).attr('data-filter-name');

            	$(this).select2({
				    theme: "bootstrap",
				    minimumInputLength: 2,
	            	allowClear: true,
	        	    placeholder: $(this).attr('placeholder'),
					closeOnSelect: false,
					dropdownParent: $(this).parent('.form-group'),
				    // tags: [],
				    ajax: {
				        url: '{{ $filter->values }}',
				        dataType: 'json',
				        type: 'GET',
				        quietMillis: 50,
				        // data: function (term) {
				        //     return {
				        //         term: term
				        //     };
				        // },
				        processResults: function (data) {
				            return {
				                results: $.map(data, function (item, i) {
				                    return {
				                        text: item,
				                        id: i
				                    }
				                })
				            };
				        }
				    }
				}).on('change', function (evt) {
					var val = $(this).val();
					var val_text = $(this).select2('data')[0]?$(this).select2('data')[0].text:null;
					var parameter = filterName;

			    	// behaviour for ajax table
					var ajax_table = $('#crudTable').DataTable();
					var current_url = ajax_table.ajax.url();
					var new_url = addOrUpdateUriParameter(current_url, parameter, val);
					if (val_text) {
	                    new_url = addOrUpdateUriParameter(new_url, parameter + '_text', val_text);
					}
					new_url = normalizeAmpersand(new_url.toString());


					// replace the datatables ajax url with new_url and reload it
					ajax_table.ajax.url(new_url).load();

					// add filter to URL
					crud.updateUrl(new_url);

					// mark this filter as active in the navbar-filters
					if (URI(new_url).hasQuery(filterName, true)) {
						$('li[filter-name='+filterName+']').removeClass('active').addClass('active');
					}
					else
					{
						$("li[filter-name="+filterName+"]").removeClass("active");
						$("li[filter-name="+filterName+"]").find('.dropdown-menu').removeClass("show");
					}
				});

				// when the dropdown is opened, autofocus on the select2
				$('li[filter-name='+filterName+']').on('shown.bs.dropdown', function () {
					$('#filter_'+filterName).select2('open');
				});

				// clear filter event (used here and by the Remove all filters button)
				$('li[filter-name='+filterName+']').on('filter:clear', function(e) {
					// console.log('select2 filter cleared');
					$('li[filter-name='+filterName+']').removeClass('active');
	                $('#filter_'+filterName).val(null).trigger('change');
				});
            });
        });
    </script>
@endpush
{{-- End of Extra CSS and JS --}}
{{-- ########################################## --}}

