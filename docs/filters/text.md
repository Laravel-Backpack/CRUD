### Text

Shows a text input. Most useful for letting the user filter through information that's not shown as a column in the CRUD table - otherwise they could just use the DataTables search field.

```php
CRUD::filter('description')
    ->type('text')
    ->whenActive(function($value) {
      // CRUD::addClause('where', 'description', 'LIKE', "%$value%");
    });
```
