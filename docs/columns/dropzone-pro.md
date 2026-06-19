### dropzone [PRO]

The ```dropzone``` column will output a list of files and links, when used on an attribute that stores a JSON array of file paths. It is meant to be used inside the show functionality (not list, though it also works there), to preview files uploaded with the ```dropzone``` field type.

Its definition is very similar to the [dropzone *field type*](/docs/{{version}}/crud-fields#dropzone-pro).

```php
[
    'name'  => 'dropzone', // The db column name
    'label' => 'Images', // Table column heading
    'type'  => 'dropzone',
    // 'disk'  => 'public', specify disk name
]
```
