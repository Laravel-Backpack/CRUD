### checklist_dependency

Show connected items selected via checklist_dependency field. It's definition is totally similar to the [checklist_dependency *field type*](/docs/{{version}}/crud-fields#checklist_dependency).

```php
[
    'label'             => 'User Role Permissions',
    'type'              => 'checklist_dependency',
    'name'              => 'roles,permissions',
    'subfields'         => [
        'primary' => [
            'name'             => 'roles', // the method that defines the relationship in your Model
            'entity'           => 'roles', // the method that defines the relationship in your Model
            'attribute'        => 'name', // foreign key attribute that is shown to user
        ],
        'secondary' => [
            'name'           => 'permissions', // the method that defines the relationship in your Model
            'entity'         => 'permissions', // the method that defines the relationship in your Model
            'attribute'      => 'name', // foreign key attribute that is shown to user
        ],
    ],
]
```
