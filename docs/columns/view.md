### view

Display any custom column type you want. Usually used by Backpack package developers, to use views from within their packages, instead of having to publish the views.

```php
[
   'name'  => 'name', // The db column name
   'label' => 'Tag Name', // Table column heading
   'type'  => 'view',
   'view'  => 'package::columns.column_type_name', // or path to blade file
],
```
