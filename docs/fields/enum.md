### enum

Show a select with the values for an ENUM database column, or an PHP enum (introduced in PHP 8.1).

##### Database ENUM
When used with a database enum it requires that the database column type is `enum`. In case it's nullable it will also show `-` (empty) option.

PLEASE NOTE the `enum` field using database enums only works for MySQL.

```php
CRUD::field([
    'name'  => 'status',
    'label' => 'Status',
    'type'  => 'enum',
    // optional, specify the enum options with custom display values
    'options' => [
        'DRAFT' => 'Is Draft',
        'PUBLISHED' => 'Is Published'
    ]
]);
```

##### PHP enum

If you are using a `BackedEnum` your best option is to cast it in your model, and Backpack know how to handle it without aditional configuration.

```php
// in your model (eg. Article)

protected $casts = ['status' => \App\Enums\StatusEnum::class]; //assumes you have this enum created

// and in your controller
CRUD::field([
    'name'  => 'status',
    'label' => 'Status',
    'type'  => 'enum'
    // optional
    //'enum_class' => 'App\Enums\StatusEnum',
    //'enum_function' => 'readableStatus',
]);
```

In case it's not a `BackedEnum` or you don't want to cast it in your Model, you should provide the enum class to the field:

```php
CRUD::field([
    'name'  => 'status',
    'label' => 'Status',
    'type'  => 'enum',
    'enum_class' => \App\Enums\StatusEnum::class
]);
```
