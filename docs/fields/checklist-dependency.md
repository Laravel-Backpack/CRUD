### checklist_dependency

```php
CRUD::field([   // two interconnected entities
    'label'             => 'User Role Permissions',
    'field_unique_name' => 'user_role_permission',
    'type'              => 'checklist_dependency',
    'name'              => 'roles,permissions', // the methods that define the relationship in your Models
    'subfields'         => [
        'primary' => [
            'label'            => 'Roles',
            'name'             => 'roles', // the method that defines the relationship in your Model
            'entity'           => 'roles', // the method that defines the relationship in your Model
            'entity_secondary' => 'permissions', // the method that defines the relationship in your Model
            'attribute'        => 'name', // foreign key attribute that is shown to user
            'model'            => "Backpack\PermissionManager\app\Models\Role", // foreign key model
            'pivot'            => true, // on create&update, do you need to add/delete pivot table entries?]
            'number_columns'   => 3, //can be 1,2,3,4,6
            'options' => (function ($query) {
                return $query->where('name', '!=', 'admin');
            }), // force the related options to be a custom query, instead of all(); you can use this to filter the available options
        ],
        'secondary' => [
            'label'          => 'Permission',
            'name'           => 'permissions', // the method that defines the relationship in your Model
            'entity'         => 'permissions', // the method that defines the relationship in your Model
            'entity_primary' => 'roles', // the method that defines the relationship in your Model
            'attribute'      => 'name', // foreign key attribute that is shown to user
            'model'          => "Backpack\PermissionManager\app\Models\Permission", // foreign key model
            'pivot'          => true, // on create&update, do you need to add/delete pivot table entries?]
            'number_columns' => 3, //can be 1,2,3,4,6
            'options' => (function ($query) {
                return $query->where('name', '!=', 'admin');
            }), // force the related options to be a custom query, instead of all(); you can use this to filter the available options
        ],
    ],
]);
```
