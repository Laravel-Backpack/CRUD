### upload

**Step 1.** Show a file input to the user:
```php
CRUD::field([   // Upload
    'name'      => 'image',
    'label'     => 'Image',
    'type'      => 'upload',
]);
```

**Step 2.** Choose how to handle the file upload process. Starting v6, you have two options:
- **Option 1.** Let Backpack handle the upload process for you. This is by far the most convenient option, because it's the easiest to implement and fully customizable. All you have to do is add the `withFiles => true` attribute to your field definition:
```php
CRUD::field([   // Upload
    'name'      => 'image',
    'label'     => 'Image',
    'type'      => 'upload',
    'withFiles' => true
]);
```
To know more about the `withFiles`, how it works and how to configure it, [ click here to read the documentation ](https://backpackforlaravel.com/docs/6.x/crud-uploaders).

- **Option 2.** Handle the upload process yourself. This is what happened in v5, so if you want to handle the upload by yourself you can [read the v5 upload docs here](https://backpackforlaravel.com/docs/5.x/crud-fields#upload-1).

**Upload Field Validation**

You can use standard Laravel validation rules. But we've also made it easy for you to validate the `upload` fields, using a [Custom Validation Rule](/docs/{{version}}/custom-validation-rules). The `ValidUpload` validation rule allows you to define two sets of rules:
- `::field()` - the field rules (independent of the file content);
- `->file()` - rules that apply to the sent file;

This helps you avoid most quirks when validating file uploads using Laravel's validation rules.

```php
use Backpack\CRUD\app\Library\Validation\Rules\ValidUpload;

'image' => ValidUpload::field('required')
                ->file('file|mimes:jpeg,png,jpg,gif,svg|max:2048'),
```
