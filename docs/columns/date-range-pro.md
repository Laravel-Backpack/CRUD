### date_range [PRO]

Show two date columns in a single column as a date range. Example: `18 Mar 2000 - 30 Nov 1985`

Its definition is very similar to the [date_range *field type*](/docs/{{version}}/crud-fields#date_range-pro).

```php
[ // Date_range
    'name'       => 'start_date,end_date', // two columns with a comma
    'label'      => 'Date Range',
    'type'       => 'date_range',
]
```
