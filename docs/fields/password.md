### password

```php
CRUD::field([   // Password
    'name'  => 'password',
    'label' => 'Password',
    'type'  => 'password'
]);
```

Please note that this will NOT hash/encrypt the string before it stores it to the database. You need to hash the password manually. The most popular way to do that are:

1. Using [a mutator on your Model](https://laravel.com/docs/7.x/eloquent-mutators#defining-a-mutator). For example:

```php
public function setPasswordAttribute($value) {
    $this->attributes['password'] = Hash::make($value);
}
```

2. By overwriting the Create/Update operation methods, inside the Controller. There's a working example [in our PermissionManager package](https://github.com/Laravel-Backpack/PermissionManager/blob/master/src/app/Http/Controllers/UserCrudController.php#L103-L124) but the gist of it is this:

```php
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation { store as traitStore; }

    public function store()
    {
        CRUD::setRequest(CRUD::validateRequest());

        /** @var \Illuminate\Http\Request $request */
        $request = CRUD::getRequest();

        // Encrypt password if specified.
        if ($request->input('password')) {
            $request->request->set('password', Hash::make($request->input('password')));
        } else {
            $request->request->remove('password');
        }

        CRUD::setRequest($request);
        CRUD::unsetValidation(); // Validation has already been run

        return $this->traitStore();
    }
```
