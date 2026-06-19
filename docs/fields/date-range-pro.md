### date_range [PRO]

Show a DateRangePicker and let the user choose a start date and end date.

```php
CRUD::field([   // date_range
    'name'  => 'start_date,end_date', // db columns for start_date & end_date
    'label' => 'Event Date Range',
    'type'  => 'date_range',

    // OPTIONALS
    // default values for start_date & end_date
    'default'            => ['2019-03-28 01:01', '2019-04-05 02:00'],
    // options sent to daterangepicker.js
    'date_range_options' => [
        'drops' => 'down', // can be one of [down/up/auto]
        'timePicker' => true,
        'locale' => ['format' => 'DD/MM/YYYY HH:mm']
    ]
]);
```

Please note it is recommended that you use [attribute casting](https://laravel.com/docs/5.3/eloquent-mutators#attribute-casting) on your model (cast to date).
