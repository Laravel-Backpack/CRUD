{{-- Dropdown Backpack CRUD filter --}}
<li filter-name="{{ $filter->name }}"
    filter-type="{{ $filter->type }}"
    filter-key="{{ $filter->key }}"
	class="nav-item dropdown {{ Request::get($filter->name)?'active':'' }}">
    <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">{{ $filter->label }} <span class="caret"></span></a>
    <ul class="dropdown-menu">
		<a class="dropdown-item" parameter="{{ $filter->name }}" dropdownkey="" href="">-</a>
		<div role="separator" class="dropdown-divider"></div>
		@if (is_array($filter->values) && count($filter->values))
			@foreach($filter->values as $key => $value)
				@if ($key === 'dropdown-separator')
					<div role="separator" class="dropdown-divider"></div>
				@else
					<a  class="dropdown-item {{ ($filter->isActive() && $filter->currentValue == $key)?'active':'' }}"
						parameter="{{ $filter->name }}"
						href=""
						dropdownkey="{{ $key }}"
						>{{ $value }}</a>
				@endif
			@endforeach
		@endif
    </ul>
  </li>


{{-- ########################################### --}}
{{-- Extra CSS and JS for this particular filter --}}

{{-- FILTERS EXTRA CSS  --}}
{{-- push things in the after_styles section --}}

    {{-- @push('crud_list_styles')
        <!-- no css -->
    @endpush --}}


{{-- FILTERS EXTRA JS --}}
{{-- push things in the after_scripts section --}}

@push('crud_list_scripts')
    <script>
		jQuery(document).ready(function($) {
			$("li.dropdown[filter-key={{ $filter->key }}] .dropdown-menu a").click(function(e) {
				e.preventDefault();

				var value = $(this).attr('dropdownkey');
				var parameter = $(this).attr('parameter');

                var new_url = updateDatatablesOnFilterChange(parameter, value, true);

				// mark this filter as active in the navbar-filters
				// mark dropdown items active accordingly
				if (URI(new_url).hasQuery('{{ $filter->name }}', true)) {
					$("li[filter-key={{ $filter->key }}]").removeClass('active').addClass('active');
					$("li[filter-key={{ $filter->key }}] .dropdown-menu a").removeClass('active');
					$(this).addClass('active');
				}
				else
				{
					$("li[filter-key={{ $filter->key }}]").trigger("filter:clear");
				}
			});

			// clear filter event (used here and by the Remove all filters button)
			$("li[filter-key={{ $filter->key }}]").on('filter:clear', function(e) {
				$("li[filter-key={{ $filter->key }}]").removeClass('active');
				$("li[filter-key={{ $filter->key }}] .dropdown-menu a").removeClass('active');
			});
		});
	</script>
@endpush
{{-- End of Extra CSS and JS --}}
{{-- ########################################## --}}
