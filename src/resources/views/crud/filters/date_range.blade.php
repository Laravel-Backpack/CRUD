{{-- Date Range Backpack CRUD filter --}}

@php
    $filter->options['date_range_options'] = array_replace_recursive([
		'timePicker' => false,
    	'alwaysShowCalendars' => true,
        'autoUpdateInput' => true,
        'startDate' => \Carbon\Carbon::now()->toDateTimeString(),
        'endDate' => \Carbon\Carbon::now()->toDateTimeString(),
        'ranges' => [
            trans('backpack::crud.today') =>  [\Carbon\Carbon::now()->startOfDay()->toDateTimeString(), \Carbon\Carbon::now()->endOfDay()->toDateTimeString()],
            trans('backpack::crud.yesterday') => [\Carbon\Carbon::now()->subDay()->startOfDay()->toDateTimeString(), \Carbon\Carbon::now()->subDay()->endOfDay()->toDateTimeString()],
            trans('backpack::crud.last_7_days') => [\Carbon\Carbon::now()->subDays(6)->startOfDay()->toDateTimeString(), \Carbon\Carbon::now()->toDateTimeString()],
            trans('backpack::crud.last_30_days') => [\Carbon\Carbon::now()->subDays(29)->startOfDay()->toDateTimeString(), \Carbon\Carbon::now()->toDateTimeString()],
            trans('backpack::crud.this_month') => [\Carbon\Carbon::now()->startOfMonth()->toDateTimeString(), \Carbon\Carbon::now()->endOfMonth()->toDateTimeString()],
            trans('backpack::crud.last_month') => [\Carbon\Carbon::now()->subMonth()->startOfMonth()->toDateTimeString(), \Carbon\Carbon::now()->subMonth()->endOfMonth()->toDateTimeString()]
        ],
        'locale' => [
            'firstDay' => 0,
            'format' => config('backpack.base.default_date_format'),
            'applyLabel'=> trans('backpack::crud.apply'),
            'cancelLabel'=> trans('backpack::crud.cancel'),
            'customRangeLabel' => trans('backpack::crud.custom_range')
        ],


    ], $filter->options['date_range_options'] ?? []);

    //if filter is active we override developer init values
    if($filter->currentValue) {
	    $dates = (array)json_decode($filter->currentValue);
        $filter->options['date_range_options']['startDate'] = $dates['from'];
        $filter->options['date_range_options']['endDate'] = $dates['to'];
    }

@endphp


<li filter-name="{{ $filter->name }}"
    filter-type="{{ $filter->type }}"
    filter-key="{{ $filter->key }}"
	class="nav-item dropdown {{ Request::get($filter->name)?'active':'' }}">
	<a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">{{ $filter->label }} <span class="caret"></span></a>
	<div class="dropdown-menu p-0">
		<div class="form-group backpack-filter mb-0">
			<div class="input-group date">
		        <div class="input-group-prepend">
		          <span class="input-group-text"><i class="la la-calendar"></i></span>
		        </div>
		        <input class="form-control pull-right"
		        		id="daterangepicker-{{ $filter->key }}"
		        		type="text"
                        data-bs-daterangepicker="{{ json_encode($filter->options['date_range_options'] ?? []) }}"
		        		>
		        <div class="input-group-append daterangepicker-{{ $filter->key }}-clear-button">
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
    <script type="text/javascript" src="{{ asset('packages/moment/min/moment-with-locales.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('packages/bootstrap-daterangepicker/daterangepicker.js') }}"></script>

  <script>

  		function applyDateRangeFilter{{$filter->key}}(start, end) {

  			if (start && end) {
  				var dates = {
					'from': start.format('YYYY-MM-DD HH:mm:ss'),
					'to': end.format('YYYY-MM-DD HH:mm:ss')
                };

                var value = JSON.stringify(dates);
  			} else {
  				var value = '';
  			}

            var parameter = '{{ $filter->name }}';

			var new_url = updateDatatablesOnFilterChange(parameter, value, true);

			// mark this filter as active in the navbar-filters
			if (URI(new_url).hasQuery('{{ $filter->name }}', true)) {
				$('li[filter-key={{ $filter->key }}]').removeClass('active').addClass('active');
			} else {
				$('li[filter-key={{ $filter->key }}]').trigger('filter:clear');
			}
  		}

		jQuery(document).ready(function($) {

            var dateRangeShouldUpdateFilterUrl = false;

            moment.locale('{{app()->getLocale()}}');

			var dateRangeInput = $('#daterangepicker-{{ $filter->key }}');

            $config = dateRangeInput.data('bs-daterangepicker');

            $ranges = $config.ranges;
            $config.ranges = {};

            //if developer configured ranges we convert it to moment() dates.
            for (var key in $ranges) {
                if ($ranges.hasOwnProperty(key)) {
                    $config.ranges[key] = $.map($ranges[key], function($val) {
                        return moment($val);
                    });
                }
            }

            $config.startDate = moment($config.startDate);

            $config.endDate = moment($config.endDate);


            dateRangeInput.daterangepicker($config);


            dateRangeInput.on('apply.daterangepicker', function(ev, picker) {
				applyDateRangeFilter{{$filter->key}}(picker.startDate, picker.endDate);
			});
			$('li[filter-key={{ $filter->key }}]').on('hide.bs.dropdown', function () {
				if($('.daterangepicker').is(':visible'))
			    return false;
			});
			$('li[filter-key={{ $filter->key }}]').on('filter:clear', function(e) {
				//if triggered by remove filters click just remove active class,no need to send ajax
				$('li[filter-key={{ $filter->key }}]').removeClass('active');
			});
			// datepicker clear button
			$(".daterangepicker-{{ $filter->key }}-clear-button").click(function(e) {
				e.preventDefault();
				applyDateRangeFilter{{$filter->key}}(null, null);
			});
		});
  </script>
@endpush
{{-- End of Extra CSS and JS --}}
{{-- ########################################## --}}
