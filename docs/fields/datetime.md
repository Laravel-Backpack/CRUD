### datetime

```php
CRUD::field([   // DateTime
    'name'  => 'start',
    'label' => 'Event start',
    'type'  => 'datetime'
]);
```

**Please note:** if you're using datetime [attribute casting](https://laravel.com/docs/5.3/eloquent-mutators#attribute-casting) on your model, you also need to place this mutator inside your model:
```php
	public function setDatetimeAttribute($value) {
		$this->attributes['datetime'] = \Carbon\Carbon::parse($value);
	}
```
Otherwise the input's datetime-local format will cause some errors.
