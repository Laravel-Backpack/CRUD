## How to Create Uploaders

Do you want to create your own Uploader class, for your custom field? Here's how you can do that, and how Uploader classes work behind the scenes.

First thing you need to decide if you are creating a _non-ajax_ or _ajax_ uploader:
- _non-ajax_ uploaders process the file upload when you submit your form; 
- _ajax_ uploaders process the file upload before the form is submitted, by submitting an AJAX request using Javascript; 

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
