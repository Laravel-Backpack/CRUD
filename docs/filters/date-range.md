### Date range

Show a daterange picker. The user can select a start date and an end date.

```php
CRUD::filter('from_to')
    ->type('date_range')
    // set options to customize, www.daterangepicker.com/#options
    ->date_range_options([
       'timePicker' => true // example: enable/disable time picker
    ])
    ->whenActive(function($value) {
      // $dates = json_decode($value);
      // CRUD::addClause('where', 'date', '>=', $dates->from);
      // CRUD::addClause('where', 'date', '<=', $dates->to);
    });
```

#### Format

The `locale.format` option (Moment.js tokens, see [daterangepicker options](https://www.daterangepicker.com/#options)) controls both the picker display and the [filter badge](#filter-value-badges):

```php
CRUD::filter('from_to')
    ->type('date_range')
    ->date_range_options([
        'locale' => ['format' => 'DD/MM/YYYY'], // picker + badge format
    ])
    ->showFilterValues()
    ->whenActive(function($value) {
        $dates = json_decode($value); // always in raw format: Y-m-d H:i:s
        CRUD::addClause('where', 'date', '>=', $dates->from);
        CRUD::addClause('where', 'date', '<=', $dates->to);
    });
```

The badge will show something like `From To: 01/01/2026 → 31/01/2026`. If `timePicker` is enabled, times are included in the badge, using the same format:

```php
CRUD::filter('from_to')
    ->type('date_range')
    ->date_range_options([
        'timePicker' => true,
        'locale'     => ['format' => 'DD/MM/YYYY HH:mm'],
    ])
    // badge: "01/01/2026 00:00 → 31/01/2026 23:59"
    ->whenActive(function($value) {
        $dates = json_decode($value);
        CRUD::addClause('where', 'date', '>=', $dates->from);
        CRUD::addClause('where', 'date', '<=', $dates->to);
    });
```

The URL parameter and the `$value` passed to `whenActive()` always stay in the raw `Y-m-d H:i:s` format - the format option only changes how values are displayed.
