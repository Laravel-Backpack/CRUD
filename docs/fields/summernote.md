### summernote

Show a [Summernote WYSIWYG editor](http://summernote.org/) to the user.

```php
CRUD::field([   // Summernote
    'name'  => 'description',
    'label' => 'Description',
    'type'  => 'summernote',
    'options' => [],
]);

// the summernote field works with the default configuration options but allow developer to configure to his needs
// optional configuration check https://summernote.org/deep-dive/ for a list of available configs
CRUD::field([
    'name'  => 'description',
    'label' => 'Description',
    'type'  => 'summernote',
    'options' => [
        'toolbar' => [
            ['font', ['bold', 'underline', 'italic']]
        ]
    ],
]);
```

> NOTE: Summernote does NOT sanitize the input. If you do not trust the users of this field, you should sanitize the input or output using something like HTML Purifier. Personally we like to use install [mewebstudio/Purifier](https://github.com/mewebstudio/Purifier) and add an [accessor or mutator](https://laravel.com/docs/8.x/eloquent-mutators#accessors-and-mutators) on the Model, so that wherever the model is created from (admin panel or app), the output will always be clean. [Example here](https://github.com/Laravel-Backpack/demo/commit/7342cffb418bb568b9e4ee279859685ddc0456c1).

#### Uploading files with summernote

Summernote saves images as base64 encoded strings in the database. If you want to save them as files on the server, you can use the [Summernote Uploader](https://backpackforlaravel.com/docs/7.x/crud-uploaders). Please note that the Summernote Uploader is part of the `backpack/pro` package.
