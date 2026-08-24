### Date

Show a datepicker. The user can select one day.

```php
CRUD::filter('birthday')
    ->type('date')
    ->whenActive(function($value) {
      // CRUD::addClause('where', 'date', $value);
    });
```

#### Format

By default, both the datepicker input and the filter badge use the global `backpack.ui.default_date_format` config value (`D MMM YYYY` by default). To use a different format, chain `format()` with [Moment.js format tokens](https://momentjs.com/docs/#/displaying/format/):

```php
CRUD::filter('birthday')
    ->type('date')
    ->format('DD/MM/YYYY') // datepicker input + badge format
    ->whenActive(function($value) {
        // even with a custom format, the value is always in raw format: Y-m-d
        CRUD::addClause('where', 'date', $value);
    });
```

With the example above, picking `2026-01-05` would show `05/01/2026` in the datepicker input and in the [filter badge](#filter-value-badges), while the URL parameter and the `$value` received by `whenActive()` stay `2026-01-05`. If you enable badges, you might also want a custom badge label:

```php
CRUD::filter('birthday')
    ->type('date')
    ->format('DD/MM/YYYY')
    ->showFilterValues()
    ->filterValuesLabel('Born on :value') // "Born on 05/01/2026"
    ->whenActive(function($value) {
        CRUD::addClause('where', 'date', $value);
    });
```
