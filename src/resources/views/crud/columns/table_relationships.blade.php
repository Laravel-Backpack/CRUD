@php
      $value = data_get($entry, $column['name']);
      $columns = $column['columns'];
@endphp

<span>

    @if ($value && count($columns))

        @includeWhen(!empty($column['wrapper']), 'crud::columns.inc.wrapper_start')

        <table class="table table-bordered table-condensed table-responsive-sm table-sm table-striped m-b-0">
		<thead>
			<tr>
				@foreach($columns as $tableColumnKey => $tableColumnDefinition)
                    <th>{{ is_array($tableColumnDefinition) ? $tableColumnDefinition['label'] : $tableColumnDefinition }}</th>
                @endforeach
			</tr>
		</thead>
		<tbody>
			@foreach ($value as $tableRow)
                <tr>
				@foreach($columns as $tableColumnKey => $tableColumnDefinition)
                    <td>
                        @if (isset($tableColumnDefinition['callback']) && is_callable($tableColumnDefinition['callback']))
                            {{ $tableColumnDefinition['callback'](Arr::get($tableRow, $tableColumnDefinition['attribute'] ?? $tableColumnKey), $tableRow) }}
                        @else
                            {{ Arr::get($tableRow, $tableColumnDefinition['attribute'] ?? $tableColumnKey) }}
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
