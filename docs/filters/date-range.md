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
