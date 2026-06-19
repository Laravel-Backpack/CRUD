# Uploaders

## About

Uploading and managing files is a common task in Admin Panels. In Backpack v7, your field definition can include uploading logic, thanks to some classes we call Uploaders. You don't need to create mutators, manual validation of input or custom code to handle file upload - though you can still do that, if you want.

## How to Use Uploaders

When adding an upload field (`upload`, `upload_multiple`, `image`, `dropzone`, `easymde`, `summernote`) to your operation, tell Backpack that you want to use the appropriate Uploader, by using `withFiles()`:

```php
CRUD::field('avatar')->type('upload')->withFiles();
```

That's it. Backpack will now handle the upload, storage and deletion of the files for you. By default it will use `public` disk, and will delete the files when the entry is deleted(*).

> **IMPORTANT**:
> - Make sure you've linked the `storage` folder to your `public` folder. You can do that by running `php artisan storage:link` in your terminal.
> - (*) If you want your files to be deleted when the entry is deleted, please [Configure File Deletion](#deleting-files-when-entry-is-deleted)

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

### Upload Validation

We can't stress enough how **IMPORTANT** is to properly validate and autenticate the file uploads and the upload endpoints. We have created a set of custom validation rules that will make validation of upload fields dead-simple. Please see the [Custom Validation Rules](https://backpackforlaravel/docs/custom-validation-rules) section for more information.

## Available Uploaders

We've already created Uploaders for the most common scenarios:
- CRUD comes with `SingleFile`, `MultipleFiles`, `SingleBas64Image`
- PRO comes with `DropzoneUploader`, `EasyMDEUploader`, `SummernoteUploader`
- if you want to use spatie/medialibrary you can just install [medialibrary-uploaders](https://github.com/Laravel-Backpack/medialibrary-uploaders) to get `MediaAjaxUploader`, `MediaMultipleFiles`, `MediaSingleBase64Image`, `MediaSingleFile`
