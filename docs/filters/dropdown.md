### Dropdown

Shows a list of elements (that you provide) in a dropdown. The user can only select one of these elements.

```php
CRUD::filter('status')
    ->type('dropdown')
    ->values([
      1 => 'In stock',
      2 => 'In provider stock',
      3 => 'Available upon ordering',
      4 => 'Not available',
    ])
    ->whenActive(function($value) {
      // CRUD::addClause('where', 'status', $value);
    });
```
