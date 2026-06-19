### upload_multiple

The ```upload_multiple``` column will output a list of files and links, when used on an attribute that stores a JSON array of file paths. It is meant to be used inside the show functionality (not list, though it also works there), to preview files uploaded with the ```upload_multiple``` field type.

Its definition is very similar to the [upload_multiple *field type*](/docs/{{version}}/crud-fields#upload_multiple).

```php
[
    'name'    => 'photos',
    'label'   => 'Photos',
    'type'    => 'upload_multiple',
    // 'disk' => 'public', // filesystem disk if you're using S3 or something custom
],
```
