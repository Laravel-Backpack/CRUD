### closure

Show custom HTML based on a closure you specify in your EntityCrudController.

```php
[
    'name'     => 'created_at',
    'label'    => 'Created At',
    'type'     => 'closure',
    'function' => function($entry) {
        return 'Created on '.$entry->created_at;
    }
],
```

> **DEPRECATED**: closure column will be removed in a future version of Backpack, since the same thing can now be achieved using any column (including the `text` column) and the `value` attribute - just pass the same closure to the `value` attribute of any column type.
