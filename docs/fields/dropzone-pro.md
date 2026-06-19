### dropzone [PRO]

Show a [Dropzone JS Input](https://docs.dropzone.dev/).

**Step 0.** Make sure the model attribute can hold all the information needed. Ideally, your model should cast this attribute as `array` and your migration should make the db column either `TEXT` or `JSON`. Other db column types such as `VARCHAR(255)` might not be enough all the time (for 3+ files).

**Step 1:** Add the `AjaxUploadOperation` to your `EntityCrudController` [read more about the ajax upload operation](https://backpackforlaravel.com/docs/crud-operation-ajax-upload).

```php
class EntityCrudController extends CrudController
{
    // ... other operations
    use \Backpack\Pro\Http\Controllers\Operations\AjaxUploadOperation;
}
```

**Step 2:** Add the field in EntityCrudController

```php
CRUD::field([
    'name'  => 'photos',
    'label' => 'Photos',
    'type'  => 'dropzone',
    'withFiles' => true,
    // optional configuration.
    // check available options in https://docs.dropzone.dev/configuration/basics/configuration-options
    // 'configuration' => [
    //     'parallelUploads' => 2,
    // ]
]);
```

Alternatively, you can manually implement the saving process yourself using model events, mutators or any other solution that suits you. To know more about the `withFiles`, how it works and how to configure it, [read its documentation](https://backpackforlaravel.com/docs/crud-uploaders).

**Step 4:** **VALIDATE YOUR INPUT**. Yes you can do some basic validation in Javascript, but we highly advise you to prioritize server-side validation. To make validation easy we created `ValidDropzone` validation rule. It allows you to define two set of rules:
- `::field()` - the field rules (independent of the file content);
- `->file()` - rules that apply to the sent files;

```php
use Backpack\Pro\Uploads\Validation\ValidDropzone;

'photos' => ValidDropzone::field('required|min:2|max:5')
                ->file('file|mimes:jpeg,png,jpg,gif,svg|max:2048'),
```
