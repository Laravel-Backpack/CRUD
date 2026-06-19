### checklist

Show a list of checkboxes, for the user to check one or more of them.

```php
CRUD::field([   // Checklist
    'label'     => 'Roles',
    'type'      => 'checklist',
    'name'      => 'roles',
    'entity'    => 'roles',
    'attribute' => 'name',
    'model'     => "Backpack\PermissionManager\app\Models\Role",
    'pivot'     => true,
    'show_select_all' => true, // default false
    // 'number_of_columns' => 3,
    
]);
```

**Note: If you don't use a pivot table (pivot = false), you need to cast your db column as `array` in your model,by adding your column to your model's `$casts`. **
