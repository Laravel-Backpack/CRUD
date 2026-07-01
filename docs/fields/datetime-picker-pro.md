### datetime_picker [PRO]

> ⚠️ **DEPRECATED** — Will be removed in the next major version. Migrate to [`air-datepicker`](air-datepicker-pro.md) instead (air-datepicker replaces bootstrap-datetimepicker + moment.js with a single ~13KB dependency-free widget).

Show a [Bootstrap Datetime Picker](https://eonasdan.github.io/bootstrap-datetimepicker/).

```php
CRUD::field([   // DateTime
    'name'  => 'start',
    'label' => 'Event start',
    'type'  => 'datetime_picker',

    // optional:
    'datetime_picker_options' => [
        'format' => 'DD/MM/YYYY HH:mm',
        'language' => 'pt',
        'tooltips' => [ //use this to translate the tooltips in the field
                'today' => 'Hoje',
                'selectDate' => 'Selecione a data',
                // available tooltips: today, clear, close, selectMonth, prevMonth, nextMonth, selectYear, prevYear, nextYear, selectDecade, prevDecade, nextDecade, prevCentury, nextCentury, pickHour, incrementHour, decrementHour, pickMinute, incrementMinute, decrementMinute, pickSecond, incrementSecond, decrementSecond, togglePeriod, selectTime, selectDate
        ]
    ],
    'allows_null' => true,
    // 'default' => '2017-05-12 11:59:59',
]);
```

**Please note:** if you're using date [attribute casting](https://laravel.com/docs/5.3/eloquent-mutators#attribute-casting) on your model, you may also need to place this mutator inside your model:
```php
    public function setDatetimeAttribute($value) {
        $this->attributes['datetime'] = \Carbon\Carbon::parse($value);
    }
```
Otherwise the input's datetime-local format will cause some errors. Remember to change "datetime" with the name of your attribute (column name).
