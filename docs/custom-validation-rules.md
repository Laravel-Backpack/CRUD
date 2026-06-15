# Custom Validation Rules

---

<a name="about"></a>
## About

Some Backpack fields are more difficult to validate using standard Laravel validation rules. So we've created a few custom validation rules, that will make validation them dead-simple.

<a name="valid-upload-validation-rule"></a>
## `ValidUpload` for `upload` field type

The `ValidUpload` rule is used to validate the `upload` field type. Using the custom rule helps developer avoid to setting different validation rules for different operations, (create/update).

```php
// for the field
CRUD::field('avatar')->type('upload');

// you can write the validation rule as

use Backpack\CRUD\app\Library\Validation\Rules\ValidUpload;

'avatar' => ValidUpload::field('required')->file('mimes:jpg,png|max:2048'),
```

The `::field()` constructor accepts the rules for the field, while `->file()` accepts the specific rules for files sent in field. The validation rule handles the `sometimes` case for you.

<a name="valid-upload-multiple-validation-rule"></a>
## `ValidUploadMultiple` for `upload_multiple` field type

You can use this validation rule to handle validation for your `upload_multiple` field - both for the Create and the Update operation in one go:
- use the `::field()` constructor to define the rules for the field;
- use the `->file()` method for rules specific to the files sent in field;

```php
// for the field
CRUD::field('attachments')->type('upload_multiple');

// you can write the validation rule as

use Backpack\CRUD\app\Library\Validation\Rules\ValidUploadMultiple;

'attachments' => ValidUploadMultiple::field(['min:2', 'max:5'])->file('mimes:pdf|max:10000'),    

```

<a name="valid-dropzone-validation-rule"></a>
## `ValidDropzone` for `dropzone` field type

You can use this validation rule to handle validation for your `dropzone` field - both for the Create and the Update operation in one go:
- use the `::field()` constructor to define the rules for the field;
- use the `->file()` method for rules specific to the files sent in field;

```php
// for the field
CRUD::field('photos')->type('dropzone');

// you can write the validation rule as

use Backpack\Pro\Uploads\Validation\ValidDropzone;

'attachments' => ValidDropzone::field('min:2|max:5')->file('file|mimes:jpg,png,gif|max:10000'),    

```

<a name="valid-easymde-validation-rule"></a>
## `ValidEasyMDE` for `easymde` field type

You can use this validation rule to handle validation for your `easymde` field - both for the Create and the Update operation in one go:
- use the `::field()` constructor to define the rules for the field;
- use the `->file()` method for rules specific to the files sent in field;

```php
// for the field
CRUD::field('photos')->type('dropzone');

// you can write the validation rule as

use Backpack\Pro\Uploads\Validation\ValidEasyMDE;

'easymde' => ValidEasyMDE::field('min:50|max:3000')->file('file|mimes:jpg,png,gif|max:2048'),    

```