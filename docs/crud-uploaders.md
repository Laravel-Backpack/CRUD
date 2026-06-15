# Uploaders

---

<a name="upload-about"></a>
## About

Uploading and managing files is a common task in Admin Panels. In Backpack v7, your field definition can include uploading logic, thanks to some classes we call Uploaders. You don't need to create mutators, manual validation of input or custom code to handle file upload - though you can still do that, if you want.

<a name="how-to-use-uploaders"></a>
## How to Use Uploaders

When adding an upload field (`upload`, `upload_multiple`, `image`, `dropzone`, `easymde`, `summernote`) to your operation, tell Backpack that you want to use the appropriate Uploader, by using `withFiles()`:

```php
CRUD::field('avatar')->type('upload')->withFiles();
```

That's it. Backpack will now handle the upload, storage and deletion of the files for you. By default it will use `public` disk, and will delete the files when the entry is deleted(*).

> **IMPORTANT**:
> - Make sure you've linked the `storage` folder to your `public` folder. You can do that by running `php artisan storage:link` in your terminal.
> - (*) If you want your files to be deleted when the entry is deleted, please [Configure File Deletion](#deleting-files-when-entry-is-deleted)


<a name="how-to-configure-uploaders"></a>
## How to Configure Uploaders

The `withFiles()` method accepts an array of options that you can use to customize the upload.

```php
CRUD::field('avatar')
    ->type('upload')
    ->withFiles([
        'disk' => 'public', // the disk where file will be stored
        'path' => 'uploads', // the path inside the disk where file will be stored
]);
```
**Note**: If you've defined `disk` or `prefix` on the field, you no longer need to define `disk` or `path` within `withFiles()` - it will pick those up. Make sure you are not defining both.


**Configuration options:**

- **`disk`** - default: **`public`**
The disk where the file will be stored. You can use any disk defined in your `config/filesystems.php` file.
- **`path`** - default: **`/`**
The path inside the disk where the file will be stored. It maps to `prefix` in field definition.
- **`deleteWhenEntryIsDeleted`** - default: **`true`** (**NEED ADDITIONAL CONFIGURATION**!! See: [Configure File Deletion](#deleting-files-when-entry-is-deleted))
The files will be deleted when the entry is deleted. Please take into consideration that `soft deleted models` don't delete the files.
- **`temporaryUrl`** - default: **`false`**
Some cloud disks like `s3` support the usage of temporary urls for display. Set this option to true if you want to use them.
- **`temporaryUrlExpirationTime`** - default: **`1`**
When `temporaryUrl` is set to `true`, this configures the amount of time in minutes the temporary url will be valid for.
- **`uploader`** - default: **null**
This allows you to overwrite or set the uploader class for this field. You can use any class that implements `UploaderInterface`.
- **`fileNamer`** - default: **null**
It accepts a `FileNameGeneratorInterface` instance or a closure. As the name implies, this will be used to generate the file name. Read more about in the [Naming uploaded files](#upload-name-files) section.

<a name="upload-validation"></a>
### Upload Validation

We can't stress enough how **IMPORTANT** is to properly validate and autenticate the file uploads and the upload endpoints. We have created a set of custom validation rules that will make validation of upload fields dead-simple. Please see the [Custom Validation Rules](https://backpackforlaravel/docs/custom-validation-rules) section for more information.

<a name="available-uploaders"></a>
## Available Uploaders

We've already created Uploaders for the most common scenarios:
- CRUD comes with `SingleFile`, `MultipleFiles`, `SingleBas64Image`
- PRO comes with `DropzoneUploader`, `EasyMDEUploader`, `SummernoteUploader`
- if you want to use spatie/medialibrary you can just install [medialibrary-uploaders](https://github.com/Laravel-Backpack/medialibrary-uploaders) to get `MediaAjaxUploader`, `MediaMultipleFiles`, `MediaSingleBase64Image`, `MediaSingleFile`


<a name="how-to-create-uploaders"></a>
## How to Create Uploaders

Do you want to create your own Uploader class, for your custom field? Here's how you can do that, and how Uploader classes work behind the scenes.

First thing you need to decide if you are creating a _non-ajax_ or _ajax_ uploader:
- _non-ajax_ uploaders process the file upload when you submit your form; 
- _ajax_ uploaders process the file upload before the form is submitted, by submitting an AJAX request using Javascript; 

<a name="how-to-create-a-custom-non-ajax-uploader"></a>
### How to Create a Custom Non-Ajax Uploader

First let's see how to create a non-ajax uploader, for that we will create a `CustomUploader` class that extends the abstract class `Uploader`. 

```php
namespace App\Uploaders\CustomUploader;

use Backpack\CRUD\app\Library\Uploaders\Uploader;

class CustomUploader extends Uploader
{
    // the function we need to implement
    public function uploadFiles(Model $entry, $values)
    {
        // $entry is the model instance we are working with
        // $values is the sent files from request.

        // do your upload logic here

        return $valueToBeStoredInTheDatabaseEntry;
    }

    // this is called when your uploader field is a subfield of a repeatable field. In here you receive 
    // the sent values in the current request and the previous repeatable values (only the uploads values).
    protected function uploadRepeatableFiles($values, $previousValues)
    {
        // you should return an array of arrays (each sub array is a repeatable row) where the array key is the field name.
        // backpack will merge this values along the other repeatable fields and save them in the database.
        return [
            [
                'custom_upload' => 'path/file.jpg'
            ],
            [
                'custom_upload' => 'path/file.jpg'
            ]
        ];
    }
}
```

You can now use this uploader in your field definition:
    
```php
CRUD::field('avatar')->type('upload')->withFiles([
    'uploader' => \App\Uploaders\CustomUploader::class,
]);
```

If you custom uploader was created to work for a custom field (say it's called `custom_upload`), you can tell Backpack to always use this uploader for that field type - that way you don't have to specify it every time you use the field. You can do that in your Service Provider `boot()` method, by adding it to the `UploadersRepository`:

```php
// in your App\Providers\AppServiceProvider.php

protected function boot()
{
    app('UploadersRepository')->addUploaderClasses(['custom_upload' => \App\Uploaders\CustomUploader::class], 'withFiles');
}
```

You can now use `CRUD::field('avatar')->type('custom_upload')->withFiles();` and it will use your custom uploader. What happens behind the scenes is that Backpack will register your uploader to run on 3 different model events: `saving`, `retrieved` and `deleting`.

The `Uploader` class has 3 "entry points" for the mentioned events: **`storeUploadedFiles()`**, **`retrieveUploadedFiles()`** and **`deleteUploadedFiles()`**. You can override these methods in your custom uploader, but typically you will not need to do that. The methods already delegate what will happen to the relevant methods (eg. if it's not a repeatable, call ```uploadFiles()```, othewise call ```uploadRepeatableFiles()```).

Notice this custom class you're creating is extending `Backpack\CRUD\app\Library\Uploaders\Uploader`. That base uploader class has most of the functionality implemented and uses **"strategy methods"** to configure the underlying behavior. 

**`shouldUploadFiles`** - a method that returns a boolean to determine if the files should be uploaded. By default it returns true, but you can overwrite it to add your custom logic.

**`shouldKeepPreviousValuesUnchanged`** - a method that returns a boolean to determine if the previous values should be kept unchanged and not perform the upload. 

**`hasDeletedFiles`** - a method that returns a boolean to determine if the files were deleted from the field.

**`getUploadedFilesFromRequest`** - this is the method that will be called to get the values sent in the request. Some uploaders require you get the `->files()` others the `->input()`. By default it returns the `->files()`.

This is the implementation of those methods in `SingleFile` uploader:
```php
protected function shouldKeepPreviousValueUnchanged(Model $entry, $entryValue): bool
{
    // if a string is sent as the value, it means the file was not changed so we should keep
    // previous value unchanged
    return is_string($entryValue);
}

protected function hasDeletedFiles($entryValue): bool
{
    // if the value is null, it means the file was deleted from the field
    return $entryValue === null;
}

protected function shouldUploadFiles($value): bool
{
    // when the value is an instance of UploadedFile, it means the file was uploaded and we should upload it
    return is_a($value, 'Illuminate\Http\UploadedFile', true);
}

<a name="how-to-create-a-custom-ajax-uploader"></a>
### How to Create a Custom Ajax Uploader

For the ajax uploaders, the process is similar, but your custom uploader class should extend `BackpackAjaxUploader` instead of `Uploader` (**note that this requires backpack/pro**).

```php

namespace App\Uploaders\CustomUploader;

use Backpack\Pro\Uploaders\BackpackAjaxUploader;

class CustomUploader extends BackpackAjaxUploader
{
    // this is called on `saving` event of the main entry, at this point you already performed the upload 
    // of the files in the ajax endpoint. By default they are in a temp folder, so here is the place 
    // where you should move them to the final disk and path and setup what will be saved in the database.
    public function uploadFiles(Model $entry, $values)
    {
        return $valueToBeStoredInTheDatabaseEntry;
    }

    // this is called when your uploader field is a subfield of a repeatable field. In here you receive 
    // the sent values in the current request and the previous repeatable values (only the uploads values).
    protected function uploadRepeatableFiles($values, $previousValues)
    {
        // you should return an array of arrays (each sub array is a repeatable row) where the array key is the field name.
        // backpack will merge this values along the other repeatable fields and save them in the database.
        return [
            [
                'custom_upload' => 'path/file.jpg'
            ],
            [
                'custom_upload' => 'path/file.jpg'
            ]
        ];
    }
}
```

The process to register the uploader in the `UploadersRepositoy` is the same as the non-ajax uploader. `app('UploadersRepository')->addUploaderClasses(['custom_upload' => \App\Uploaders\CustomUploader::class], 'withFiles');` in the boot method of your provider.

In addition to the field configuration, ajax uploaders require that you use the `AjaxUploadOperation` trait in your controller. The operation is responsible to register the ajax route where your files will be sent and the upload process will be handled and the delete route from where you can delete **temporary files**.

Similar to model events, there are two "setup" methods for those endpoints: **`processAjaxEndpointUploads()`** and **`deleteAjaxEndpointUpload()`**. You can overwrite them to add your custom logic but most of the time you will not need to do that and just implement the `uploadFiles()` and `uploadRepeatableFiles()` methods.

The ajax uploader also has the same "strategy methods" as the non-ajax uploader (see above), but adds a few more:
- **`ajaxEndpointSuccessResponse($files = null)`** - This should return a `JsonResponse` with the needed information when the upload is successful. By default it returns a json response with the file path.
- **`ajaxEndpointErrorResponse($message)`** - Use this method to change the endpoint response in case the upload failed. Similar to the success it should return a `JsonResponse`.
- **`getAjaxEndpointDisk()`** - By default a `temporaryDisk` is used to store the files before they are moved to the final disk (when uploadFiles() is called). You can overwrite this method to change the disk used.
- **`getAjaxEndpointPath()`** - By default the path is `/temp` but you can override this method to change the path used.
- **`getDefaultAjaxEndpointValidation()`** - Should return the default validation rules (in the format of `BackpackCustomRule`) for the ajax endpoint. By default it returns a `ValidGenericAjaxEndpoint` rule.


For any other customization you would like to perform, please check the source code of the `Uploader` and `BackpackAjaxUploader` classes.

<a name="faq-uploaders"></a>
## FAQ about Uploaders

<a name="handling-uploaders-in-relationship-fields"></a>
### Handling uploads in relationship fields

**IMPORTANT**: Please make sure you are **NOT** casting the uploaders attributes in your model. If you need a casted attribute to work with the values somewhere else, please create a different attribute that copies the uploader attribute value and manually cast it how you need it.

Some relationships require additional configuration to properly work with the Uploaders, here are some examples:

- **`BelongsToMany`** 

In this relationships, you should add the upload fields to the `withPivot()` method and create a Pivot model where Uploaders register their events. [Laravel Docs - Pivot Models](https://laravel.com/docs/10.x/eloquent-relationships#defining-custom-intermediate-table-models)

Take for example an `Article` model has a `BelongsToMany` relationship defined with `Categories` model:

```php
// Article model
public function categories() {
    $this->belongsToMany(Category::class);
}
```

To use an Uploader in this relation, you should create the `ArticleCategory` pivot model, and tell Laravel to use it. 

```php
use Illuminate\Database\Eloquent\Relations\Pivot;

class ArticleCategory extends Pivot
{

}


// and in your article/category models, update the relationship to:
public function categories() {
    $this->belongsToMany(Category::class)->withPivot('picture')->using(ArticleCategory::class); //assuming picture is the pivot field where you store the uploaded file path.
}
```

- **`MorphToMany`** 

Everything like the previous `belongsToMany`, but the pivot model needs to extend `MorphPivot`.

```php
use Illuminate\Database\Eloquent\Relations\MorphPivot;

class ArticleCategory extends MorphPivot
{

}


//in your model
public function categories() {
    $this->morphToMany(Category::class)->withPivot('picture')->using(ArticleCategory::class); //assuming picture is the pivot field where you store the uploaded file path.
}
```

<a name="naming-files-when-using-uploaders"></a>
### Naming files when using Uploaders

Backpack provides a naming strategy for uploaded files that works well for most scenarios:
- For `upload`, `upload_multiple` and `dropzone` fields, the file name will be the original file name slugged and with a random 4 character string appended to it, to avoid name collisions. Eg: `my file.pdf` becomes `my-file-aY5x.pdf`.
- For `image` it will generate a unique name for the file, and will keep the original extension. Eg: `my file.jpg` becomes `5f4a5b6c7d8e9f0a1b2c3d4e5f6a7b8c.jpg`.

You can customize the naming strategy by creating a class that implements `FileNameGeneratorInterface` and pass it to the upload configuration (the default used by Backpack).

```php
CRUD::field('avatar')->type('upload')->withFiles([
        'fileNamer' => \Backpack\CRUD\app\Library\Uploaders\Support\FileNameGenerator::class,
]);

// alternativelly you can pass a closure:
->withFiles([
    'fileNamer' => function($file, $uploader) { return 'the_file_name.png'; },
])
```
<a name="subfields-in-uploaders"></a>
### Subfields in Uploaders

You can also use uploaders in subfields. The configuration is the same as for regular fields, just use the same `withFiles` key and pass it `true` if no further configuration is required.

```php
// subfields array
[
    [
        'name' => 'avatar',
        'type' => 'upload',
        'withFiles' => true
    ],
    [
        'name' => 'attachments',
        'type' => 'upload_multiple',
        'withFiles' => [
            'path' => 'attachments',
        ],
    ],
]
```

<a name="deleting-files-when-entry-is-deleted"></a>
### Configure uploaded files to be automatically deteled

To automatically delete the uploaded files when the entry is deleted _in the admin panel_, we need to setup the upload fields in the `DeleteOperation` too:

```php
protected function setupDeleteOperation()
{
    CRUD::field('photo')->type('upload')->withFiles();

    // Alternatively, if you are not doing much more than defining fields in your create operation:
    // $this->setupCreateOperation();
}
```

Alternatively, you can manually delete the file in your Model, using the `deleted` Eloquent model event. That would ensure the file gets deleted _even if_ the entry was deleted from outside the admin panel.

```php
class SomeModel extends Model
{
    protected static function booted()
    {
        static::deleted(function ($model) {
            // delete the file
            Storage::disk('my_disk')->delete($model->photo);
        });
    }
}
```

<a name="deleting-temporary-files"></a>
## Deleting temporary files

When using ajax uploaders, the files are uploaded to a temporary disk and path before being moved to the final disk and path. If by some reason the user does not finish the operation, those files may lay around in your server temporary folder. 
To delete them, we have created a `backpack:purge-temporary-folder` command that you can schedule to run every day, or in the time frame that better suits your needs.

```php
// in your routes/console
use Illuminate\Console\Scheduling\Schedule;

Schedule::command('backpack:purge-temporary-folder')->daily();

```

For additional configuration check the `config/backpack/operations/ajax-uploads.php` file. Those configurations can also be passed on a "per-command" basis, eg: `backpack:purge-temporary-folder --disk=public --path=temp --older-than=5`.

<a name="custom-upload-fields"></a>
### Configuring uploaders in custom fields

When using uploads in custom fields, you need to tell Backpack what Uploader to use for that custom field type. 

Imagine that you created a custom upload field starting from backpack `upload` field type with: `php artisan backpack:field custom_upload --from=upload`.

You can tell Backpack what Uploader to use in 2 ways:

- In the custom field defininiton inside the uploader configuration:
```php
CRUD::field('custom_upload')->withFiles([
    'uploader' => \Backpack\CRUD\app\Library\Uploaders\SingleFile::class,
]);
```
- Or you can add it globally for that field type by adding in your Service Provider `boot()` method: 
```php
app('UploadersRepository')->addUploaderClasses(['custom_upload' => \Backpack\CRUD\app\Library\Uploaders\SingleFile::class], 'withFiles');
```

<a name="spatie-media-library"></a>
### Uploaders for Spatie MediaLibrary

The 3rd party package [`spatie/laravel-medialibrary`](https://spatie.be/docs/laravel-medialibrary/) gives you the power to easily associate files with Eloquent models. The package is incredibly popular, time-tested and well maintained.

To have Backpack upload and retrieve files using this package, we've created special Uploaders. Then it will be as easy as doing `CRUD::field('avatar')->type('image')->withMedia();`. For more information and installation instructions please see the docs on Github for [`backpack/medialibrary-uploaders`](https://github.com/Laravel-Backpack/medialibrary-uploaders).
