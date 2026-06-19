### upload_multiple

Shows a multiple file input to the user and stores the values as a JSON array in the database.

**Step 0.** Make sure the db column can hold the amount of text this field will have. For example, for MySQL, `VARCHAR(255)` might not be enough all the time (for 3+ files), so it's better to go with `TEXT`. Make sure you're using a big column type in your migration or db.

**Step 1.** Show a multiple file input to the user:
```php
CRUD::field([
    'name'      => 'photos',
    'label'     => 'Photos',
    'type'      => 'upload_multiple',
]);
```

**Step 2.** Choose how to handle the file upload process. Starting v6, you have two options:
- **Option 1.** Let Backpack handle the upload process for you. This is by far the most convenient option, because it's the easiest to implement and fully customizable. All you have to do is add the `withFiles => true` attribute to your field definition:
```php
CRUD::field([
    'name'      => 'photos',
    'label'     => 'Photos',
    'type'      => 'upload_multiple',
    'withFiles' => true
]);
```
To know more about the `withFiles`, how it works and how to configure it, [ click here to read the documentation ](https://backpackforlaravel.com/docs/6.x/crud-uploaders).

- **Option 2.** Handle the upload process yourself. This is what happened in v5, so if you want to handle the upload by yourself you can [read the v5 upload docs here](https://backpackforlaravel.com/docs/5.x/crud-fields#upload_multiple).

**Validation**

You can use standard Laravel validation rules. But we've also made it easy for you to validate the `upload` fields, using a [Custom Validation Rule](/docs/{{version}}/custom-validation-rules). The `ValidUploadMultiple` validation rule allows you to define two sets of rules:
- `::field()` - the input rules, independant of the content;
- `file()` - rules that apply to each file that gets sent;

This will help you avoid most quirks of using Laravel's standard validation rules alone.

```php
use Backpack\CRUD\app\Library\Validation\Rules\ValidUploadMultiple;

'photos' => ValidUploadMultiple::field('required|min:2|max:5')
                ->file('file|mimes:jpeg,png,jpg,gif,svg|max:2048'),
```

**NOTE**: This field uses a `clear_{fieldName}` input to send the deleted files from the frontend to the backend. In case you are using `$guarded` add it there.
Eg: `protected $guarded = ['id', 'clear_photos'];`
