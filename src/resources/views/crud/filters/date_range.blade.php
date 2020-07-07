{{-- Date Range Backpack CRUD filter --}}

<li filter-name="{{ $filter->name }}"
	filter-type="{{ $filter->type }}"
	class="nav-item dropdown {{ Request::get($filter->name)?'active':'' }}">
	<a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">{{ $filter->label }} <span class="caret"></span></a>
	<div class="dropdown-menu p-0">
		<div class="form-group backpack-filter mb-0">
			<div class="input-group date">
		        <div class="input-group-prepend">
		          <span class="input-group-text"><i class="la la-calendar"></i></span>
		        </div>
		        <input class="form-control pull-right"
		        		id="daterangepicker-{{ $filter->name }}"
		        		type="text"
		        		@if ($filter->currentValue)
							@php
								$dates = (array)json_decode($filter->currentValue);
								$start_date = $dates['from'];
								$end_date = $dates['to'];
					        	$date_range = implode(' ~ ', $dates);
					        	$date_range = str_replace('-', '/', $date_range);
					        	$date_range = str_replace('~', '-', $date_range);

					        @endphp
					        placeholder="{{ $date_range }}"
						@endif
		        		>
		        <div class="input-group-append daterangepicker-{{ $filter->name }}-clear-button">
		          <a class="input-group-text" href=""><i class="la la-times"></i></a>
		        </div>
		    </div>
		</div>
	</div>
</li>

{{-- ########################################### --}}
{{-- Extra CSS and JS for this particular filter --}}

{{-- FILTERS EXTRA CSS  --}}
{{-- push things in the after_styles section --}}

@push('crud_list_styles')
    <!-- include select2 css-->
	<link rel="stylesheet" type="text/css" href="{{ asset('packages/bootstrap-daterangepicker/daterangepicker.css') }}" />
	<style>
		.input-group.date {
			width: 320px;
			max-width: 100%; }
		.daterangepicker.dropdown-menu {
			z-index: 3001!important;
		}
	</style>
@endpush


{{-- FILTERS EXTRA JS --}}
{{-- push things in the after_scripts section --}}

@push('crud_list_scripts')
	<script type="text/javascript" src="{{ asset('packages/moment/min/moment.min.js') }}"></script>
	<script type="text/javascript" src="{{ asset('packages/bootstrap-daterangepicker/daterangepicker.js') }}"></script>
  <script>

  		function applyDateRangeFilter{{$filter->name}}(start, end) {
  			if (start && end) {
  				var dates = {
					'from': start.format('YYYY-MM-DD'),
					'to': end.format('YYYY-MM-DD')
				};
				var value = JSON.stringify(dates);
  			} else {
  				//this change to empty string,because addOrUpdateUriParameter method just judgment string
  				var value = '';
  			}
			var parameter = '{{ $filter->name }}';

	    	// behaviour for ajax table
			var ajax_table = $('#crudTable').DataTable();
			var current_url = ajax_table.ajax.url();
			var new_url = addOrUpdateUriParameter(current_url, parameter, value);

			// replace the datatables ajax url with new_url and reload it
			new_url = normalizeAmpersand(new_url.toString());
			ajax_table.ajax.url(new_url).load();

			// add filter to URL
			crud.updateUrl(new_url);

			// mark this filter as active in the navbar-filters
			if (URI(new_url).hasQuery('{{ $filter->name }}', true)) {
				$('li[filter-name={{ $filter->name }}]').removeClass('active').addClass('active');
			}
			else
			{
				$('li[filter-name={{ $filter->name }}]').trigger('filter:clear');
			}
  		}

		jQuery(document).ready(function($) {
			var dateRangeInput = $('#daterangepicker-{{ $filter->name }}').daterangepicker({
				timePicker: false,
		        ranges: {
		            'Today': [moment().startOf('day'), moment().endOf('day')],
		            'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
		            'Last 7 Days': [moment().subtract(6, 'days'), moment()],
		            'Last 30 Days': [moment().subtract(29, 'days'), moment()],
		            'This Month': [moment().startOf('month'), moment().endOf('month')],
		            'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
		        },
				@if ($filter->currentValue)
		        startDate: moment("{{ $start_date }}"),
		        endDate: moment("{{ $end_date }}"),
				@endif
				alwaysShowCalendars: true,
				autoUpdateInput: true
			});

			dateRangeInput.on('apply.daterangepicker', function(ev, picker) {
				applyDateRangeFilter{{$filter->name}}(picker.startDate, picker.endDate);
			});

			$('li[filter-name={{ $filter->name }}]').on('hide.bs.dropdown', function () {
				if($('.daterangepicker').is(':visible'))
			    return false;
			});

			$('li[filter-name={{ $filter->name }}]').on('filter:clear', function(e) {
				// console.log('daterangepicker filter cleared');
				//if triggered by remove filters click just remove active class,no need to send ajax
				$('li[filter-name={{ $filter->name }}]').removeClass('active');
			});

			// datepicker clear button
			$(".daterangepicker-{{ $filter->name }}-clear-button").click(function(e) {
				e.preventDefault();
				applyDateRangeFilter{{$filter->name}}(null, null);
			})
		});
  </script>
@endpush
{{-- End of Extra CSS and JS --}}
{{-- ########################################## --}}
