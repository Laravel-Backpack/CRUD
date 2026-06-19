### Select2

Shows a select2 and allows the user to select one item from the list or search for an item. Useful when the values list is long (over 10 elements).

```php
CRUD::filter('status')
    ->type('select2')
    ->values(function () {
      return [
        1 => 'In stock',
        2 => 'In provider stock',
        3 => 'Available upon ordering',
        4 => 'Not available',
      ];
    })
    ->whenActive(function($value) {
      // CRUD::addClause('where', 'status', $value);
    });
```

**Note:** If you want to pass all entries of a Laravel model to your filter, you can do it in the closure with something like `return \App\Models\Category::all()->keyBy('id')->pluck('name', 'id')->toArray();`
