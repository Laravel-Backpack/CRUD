{{-- Text Backpack CRUD filter --}}

<li filter-name="{{ $filter->name }}"
    filter-type="{{ $filter->type }}"
    filter-key="{{ $filter->key }}"
	class="nav-item dropdown {{ Request::get($filter->name) ? 'active' : '' }}">
	<a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">{{ $filter->label }} <span class="caret"></span></a>
	<div class="dropdown-menu p-0">
		<div class="form-group backpack-filter mb-0">
			<div class="input-group">
		        <input class="form-control pull-right"
		        		id="text-filter-{{ $filter->key }}"
		        		type="text"
						@if ($filter->currentValue)
							value="{{ $filter->currentValue }}"
						@endif
		        		>
		        <div class="input-group-append text-filter-{{ $filter->key }}-clear-button">
		          <a class="input-group-text" href=""><i class="la la-times"></i></a>
		        </div>
		    </div>
		</div>
	</div>
</li>

{{-- ########################################### --}}
{{-- Extra CSS and JS for this particular filter --}}


{{-- FILTERS EXTRA JS --}}
{{-- push things in the after_scripts section --}}

@push('crud_list_scripts')
	<!-- include select2 js-->
  <script>
		jQuery(document).ready(function($) {
            var shouldUpdateUrl = false;
			$('#text-filter-{{ $filter->key }}').on('change', function(e) {

				var parameter = '{{ $filter->name }}';
				var value = $(this).val();

				var new_url = updateDatatablesOnFilterChange(parameter, value, value || shouldUpdateUrl);
				shouldUpdateUrl = false;

				// mark this filter as active in the navbar-filters
				if (URI(new_url).hasQuery('{{ $filter->name }}', true)) {
					$('li[filter-key={{ $filter->key }}]').removeClass('active').addClass('active');
				} else {
					$('li[filter-key={{ $filter->key }}]').trigger('filter:clear');
				}
			});

			$('li[filter-key={{ $filter->key }}]').on('filter:clear', function(e) {
				$('li[filter-key={{ $filter->key }}]').removeClass('active');
				$('#text-filter-{{ $filter->key }}').val('');
			});

			// clear button for text filter
			$(".text-filter-{{ $filter->key }}-clear-button").click(function(e) {
				e.preventDefault();
                // when clicking this button this is the only removed filter, so we should update the url in this specific scenario.
                shouldUpdateUrl = true;
				$('li[filter-key={{ $filter->key }}]').trigger('filter:clear');
				$('#text-filter-{{ $filter->key }}').val('');
				$('#text-filter-{{ $filter->key }}').trigger('change');
			})
		});
  </script>
@endpush
{{-- End of Extra CSS and JS --}}
{{-- ########################################## --}}
