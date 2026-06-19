### enum

The enum column will output the value of your database ENUM column or your PHP enum attribute.
```php
[
   'name'  => 'status',
   'label' => 'Status',
   'type'  => 'enum',
],
```

By default, in case it's a `BackedEnum` it will show the `value` of the enum (when casted), in `database` or `UnitEnum` it will show the the enum value without parsing the value.

If you want to output something different than what your enum stores you have two options:
- For `database enums` you need to provide the `options` that translates the enums you store in database.
- For PHP enums you can provide the same `options` or provide a `enum_function` from the enum to gather the final result.

```php
// for database enums
[
   'name'  => 'status',
   'label' => 'Status',
   'type'  => 'enum',
   'options' => [
       'DRAFT' => 'Is draft',
       'PUBLISHED' => 'Is published'
   ]
],

// for PHP enums, given the following enum example

enum StatusEnum
{
    case DRAFT;
    case PUBLISHED;

    public function readableText(): string
    {
        return match ($this) {
            StatusEnum::DRAFT => 'Is draft',
            StatusEnum::PUBLISHED => 'Is published',
        };
    }
}

[
   'name'  => 'status',
   'label' => 'Status',
   'type'  => 'enum',
   'enum_function' => 'readableText',
   'enum_class' => 'App\Enums\StatusEnum'
],
```
