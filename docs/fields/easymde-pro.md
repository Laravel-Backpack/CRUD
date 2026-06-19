### easymde [PRO]

Show an [EasyMDE - Markdown Editor](https://github.com/Ionaru/easy-markdown-editor) to the user. EasyMDE is a well-maintained fork of SimpleMDE.

```php
CRUD::field([   // easymde
    'name'  => 'description',
    'label' => 'Description',
    'type'  => 'easymde',
    // optional
    // 'easymdeAttributes' => [
    //   'promptURLs'   => true,
    //   'status'       => false,
    //   'spellChecker' => false,
    //   'forceSync'    => true,
    // ],
    // 'easymdeAttributesRaw' => $some_json
]);
```

> NOTE: The contents displayed in this editor are NOT stripped, sanitized or escaped by default. Whenever you store Markdown or HTML inside your database, it's HIGHLY recommended that you sanitize the input or output. Laravel makes it super-easy to do that on the model using [accessors](https://laravel.com/docs/8.x/eloquent-mutators#accessors-and-mutators). If you do NOT trust the admins who have access to this field (or end-users can also store information to this db column), please make sure this attribute is always escaped, before it's shown. You can do that by running the value through `strip_tags()` in an accessor on the model (here's [an example](https://github.com/Laravel-Backpack/demo/commit/509c0bf0d8b9ee6a52c50f0d2caed65f1f986385)) or better yet, using an [HTML Purifier package](https://github.com/mewebstudio/Purifier) (here's [an example](https://github.com/Laravel-Backpack/demo/commit/7342cffb418bb568b9e4ee279859685ddc0456c1)).

#### Uploading images using EasyMDE drag & drop

Starting Backpack 6.7 you can now upload images using drag & drop directly into the EasyMDE editor. To enable this feature you need to follow these steps:

**Step 1:** Add the `AjaxUploadOperation` to your `EntityCrudController` where you defined your easyMDE field.
**Step 2:** Add the `withFiles => true` attribute to your field definition. You can check other available options in the [uploaders documentation](https://backpackforlaravel.com/docs/crud-uploaders).

**Note:** EasyMDE provides some basic javascript file validation. By default only `jpg, jpeg, png, gif, svg, webp` are allowed and files up to 2MB. You can change this by setting the `imageMaxSize` and `imageAccept` options in the `easymdeAttributes` attribute. Eg:

```php
'easymdeAttributes' => [
    'imageMaxSize' =>  1024 * 5, // up to 5MB
    'imageAccept' => ['image/gif'], // to only accept gifs 
]
```
