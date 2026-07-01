### date_picker [PRO]

> ⚠️ **DEPRECATED** — Will be removed in the next major version. Migrate to [`air-datepicker`](air-datepicker-pro.md) instead (air-datepicker replaces bootstrap-datepicker + moment.js with a single ~13KB dependency-free widget).

Show a pretty [Bootstrap Datepicker](http://bootstrap-datepicker.readthedocs.io/en/latest/).

```php
CRUD::field([   // date_picker
   'name'  => 'date',
   'type'  => 'date_picker',
   'label' => 'Date',

   // optional:
   'date_picker_options' => [
      'todayBtn' => 'linked',
      'format'   => 'dd-mm-yyyy',
      'language' => 'fr'
   ],
]);
```

Please note it is recommended that you use [attribute casting](https://laravel.com/docs/5.3/eloquent-mutators#attribute-casting) on your model (cast to date).
