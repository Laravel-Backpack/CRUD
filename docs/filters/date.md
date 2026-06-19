### Date

Show a datepicker. The user can select one day.

```php
CRUD::filter('birthday')
    ->type('date')
    ->whenActive(function($value) {
      // CRUD::addClause('where', 'date', $value);
    });
```
