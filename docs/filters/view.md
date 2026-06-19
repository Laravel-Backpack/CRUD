### View

Display any custom column filter you want. Usually used by Backpack package developers, to use views from within their packages, instead of having to publish them.

```php
CRUD::filter('category_id')
    ->type('view')
    ->view('package::columns.column_type_name')  // or path to blade file
    ->whenActive(function($value) {
    // CRUD::addClause('where', 'category_id', $value);
    });
```
