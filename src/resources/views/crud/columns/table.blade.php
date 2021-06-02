@php
    $value = data_get($entry, $column['name']);

    // make sure columns are defined
    if (!isset($column['columns'])) {
        $column['columns'] = ['value' => "Value"];
    }

	$columns = $column['columns'];

	// if this attribute isn't using attribute casting, decode it
	if (is_string($value)) {
	    $value = json_decode($value, true);
    }

    // check if it is a multidimensional array, if not we turn $value into one
    if (is_array($value) && count($value) == count($value, COUNT_RECURSIVE)) {
        $value = array($value);
    }
@endphp

<span>
    @if ($value && count($columns))

    @includeWhen(!empty($column['wrapper']), 'crud::columns.inc.wrapper_start')

    <table class="table table-bordered table-condensed table-striped m-b-0">
		<thead>
			<tr>
				@foreach($columns as $tableColumnKey => $tableColumnLabel)
				<th>{{ $tableColumnLabel }}</th>
				@endforeach
			</tr>
		</thead>
		<tbody>
			@foreach ($value as $tableRow)
			<tr>
				@foreach($columns as $tableColumnKey => $tableColumnLabel)
					<td>

						@if( is_array($tableRow) && isset($tableRow[$tableColumnKey]) )

                            {{ $tableRow[$tableColumnKey] }}

                        @elseif( is_object($tableRow) && property_exists($tableRow, $tableColumnKey) )

                            {{ $tableRow->{$tableColumnKey} }}

                        @endif

					</td>
				@endforeach
			</tr>
			@endforeach
		</tbody>
    </table>

    @includeWhen(!empty($column['wrapper']), 'crud::columns.inc.wrapper_end')

	@endif
</span>
