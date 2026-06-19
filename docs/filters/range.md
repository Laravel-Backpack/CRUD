### Range

Shows two number inputs, for min and max.

```php
CRUD::filter('number')
    ->type('range')
    ->whenActive(function($value) {
      $range = json_decode($value);
      // if ($range->from) {
      //     CRUD::addClause('where', 'number', '>=', (float) $range->from);
      // }
      // if ($range->to) {
      //     CRUD::addClause('where', 'number', '<=', (float) $range->to);
      // }
    });

    // other methods
    // label_from('min value')
    // label_to('max value)
```
