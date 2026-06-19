### Simple

Only shows a label and can be toggled on/off. Useful for things like active/inactive and paired with [Eloquent Scopes](https://laravel.com/docs/5.3/eloquent#local-scopes). The "Draft" and "Has Video" filters in the screenshot below are simple filters.

```php
CRUD::filter('active')
    ->type('simple')
    ->whenActive(function() {
      // CRUD::addClause('active'); // apply the "active" eloquent scope
    });
```
