# Backpack CRUD — Uploaders

Uploaders handle file upload, storage, retrieval, and deletion automatically — no manual mutators or custom upload code needed. Triggered by `withFiles()` on any upload-capable field.

## Quick Start

```php
// Minimum setup
CRUD::field('avatar')->type('upload')->withFiles();

// With configuration
CRUD::field('avatar')->type('upload')->withFiles([
    'disk' => 'public',    // default: public
    'path' => 'avatars',   // default: /
]);
```

**Always run `php artisan storage:link` first** — Backpack uses the `public` disk by default.

## Architecture

`withFiles()` registers model event listeners (`saving`, `retrieved`, `deleting`) via `RegisterUploadEvents`. On form submit:

| Event | What happens |
|-------|-------------|
| `saving` | `storeUploadedFiles()` → uploads file to disk, stores path in DB column |
| `retrieved` | `retrieveUploadedFiles()` → may strip path prefix for display |
| `deleting` | `deleteUploadedFiles()` → removes files from disk |

The model MUST use `CrudTrait` (which includes `HasUploadFields`) for the events to fire.

### Ajax Uploaders (PRO only)

Ajax uploaders upload files **before** form submit via a dedicated AJAX endpoint. Requires `AjaxUploadOperation` trait on the controller. Flow: file uploaded → stored in temp folder → on save → moved to final disk/path.

Temp folder cleanup: schedule `php artisan backpack:purge-temporary-folder` (e.g., daily).

## Available Uploaders

### Built-in (CRUD)

| Uploader Class | Field Type | Description |
|---------------|------------|-------------|
| `SingleFile` | `upload` | Single file upload |
| `MultipleFiles` | `upload_multiple` | Multiple files upload |
| `SingleBase64Image` | `image` | Base64-encoded image (crop support) |

### PRO (requires backpack/pro)

| Uploader Class | Field Type | Ajax? | Notes |
|---------------|------------|-------|-------|
| `DropzoneUploader` | `dropzone` | Yes | Drag-and-drop multi-file |
| `EasyMDEUploader` | `easymde` | Yes | Markdown editor file paste/drag |
| `SummernoteUploader` | `summernote` | Yes | WYSIWYG editor file paste/drag |

PRO uploaders require `use AjaxUploadOperation` on the controller.

### Add-on

| Package | Provides |
|---------|----------|
| `backpack/medialibrary-uploaders` | Spatie MediaLibrary integration via `->withMedia()` (replaces `->withFiles()`) |

### Config Mapping

In `config/backpack/crud.php`:

```php
'uploaders' => [
    'withFiles' => [
        'image'           => \Backpack\CRUD\app\Library\Uploaders\SingleBase64Image::class,
        'upload'          => \Backpack\CRUD\app\Library\Uploaders\SingleFile::class,
        'upload_multiple' => \Backpack\CRUD\app\Library\Uploaders\MultipleFiles::class,
    ],
],
```

PRO adds entries here automatically via its service provider.

## withFiles() Configuration

```php
CRUD::field('document')->type('upload')->withFiles([
    'disk'                          => 's3',        // default: 'public'
    'path'                          => 'contracts',  // default: '/'
    'deleteWhenEntryIsDeleted'      => true,         // default: true (see File Deletion)
    'temporaryUrl'                  => false,        // for S3/cloud disks
    'temporaryUrlExpirationTime'    => 1,            // minutes, default: 1
    'uploader'                      => \App\Uploaders\CustomUploader::class,  // override uploader class
    'fileNamer'                     => \App\Namers\MyNamer::class,            // or a Closure
]);
```

- `disk` — any disk defined in `config/filesystems.php`. Falls back to the field's `disk` attribute if set.
- `path` — maps to the field's `prefix` attribute. Falls back to field's `prefix` if set.
- `deleteWhenEntryIsDeleted` — auto-deletes files when entry is deleted (requires DeleteOperation field setup, see below).
- `temporaryUrl` — for cloud disks (S3), generates temporary URLs instead of permanent ones.
- `fileNamer` — accepts `FileNameGeneratorInterface` or a closure: `fn($file, $uploader) => 'my-name.png'`.

## Validation Rules

Backpack provides custom validation rules for upload fields. Use in your FormRequest:

```php
use Backpack\CRUD\app\Library\Validation\Rules\ValidUpload;
use Backpack\CRUD\app\Library\Validation\Rules\ValidUploadMultiple;
use Backpack\Pro\Uploads\Validation\ValidDropzone;          // PRO

// Single upload
'avatar' => ['required', new ValidUpload('avatar')],

// Multiple uploads
'photos' => ['required', new ValidUploadMultiple('photos')],

// Dropzone (PRO)
'images' => ['required', new ValidDropzone('images')],
```

These rules validate the upload is valid, checks for previous values on update, and handles special cases like empty arrays from `upload_multiple`.

## File Naming

Default naming strategy:
- `upload` / `upload_multiple` / `dropzone`: original filename sluggified + 4 random chars (e.g., `my-file-aY5x.pdf`)
- `image`: unique generated name, preserves extension (e.g., `5f4a5b6c7d8e9f0a1b2c3d4e5f6a7b8c.jpg`)

The naming is handled by `FileNameGenerator` (configurable via `config/backpack/crud.php` → `file_name_generator`).

Custom namer via closure:

```php
->withFiles([
    'fileNamer' => function ($file, $uploader) {
        return time() . '_' . $file->getClientOriginalName();
    },
])
```

Custom namer via class implementing `FileNameGeneratorInterface`:

```php
use Backpack\CRUD\app\Library\Uploaders\Support\Interfaces\FileNameGeneratorInterface;

class MyNamer implements FileNameGeneratorInterface
{
    public function generateFileName($file, $uploader): string { ... }
    public function generateFileNameForRepeatable($file, $uploader): string { ... }
}
```

## Custom Uploaders

### Non-Ajax Uploader

Extend `Uploader` and implement `uploadFiles()`:

```php
namespace App\Uploaders;

use Backpack\CRUD\app\Library\Uploaders\Uploader;
use Illuminate\Database\Eloquent\Model;

class CustomUploader extends Uploader
{
    public function uploadFiles(Model $entry, $values)
    {
        // $values is the request value for this field
        // do your upload logic
        return $valueToStore; // stored in the DB column
    }

    protected function uploadRepeatableFiles($values, $previousValues)
    {
        // for repeatable subfields
        return [['custom_upload' => 'path/file.jpg']];
    }
}
```

Use it:

```php
CRUD::field('document')->type('upload')->withFiles([
    'uploader' => \App\Uploaders\CustomUploader::class,
]);
```

### Ajax Uploader (PRO)

Extend `BackpackAjaxUploader` instead. Requires `AjaxUploadOperation` on the controller.

```php
use Backpack\Pro\Uploaders\BackpackAjaxUploader;

class CustomAjaxUploader extends BackpackAjaxUploader
{
    public function uploadFiles(Model $entry, $values) { ... }
    protected function uploadRepeatableFiles($values, $previousValues) { ... }
}
```

Strategy methods you can override:
- `shouldUploadFiles($value)` — return bool; controls whether upload proceeds
- `shouldKeepPreviousValuesUnchanged($entry, $entryValue)` — return bool
- `hasDeletedFiles($entryValue)` — return bool; detects file removal from field
- `getUploadedFilesFromRequest()` — where to read values from (files vs input)
- `getAjaxEndpointDisk()` — temp disk for ajax uploads (default: temporaryDisk)
- `getAjaxEndpointPath()` — temp path (default: /temp)
- `getDefaultAjaxEndpointValidation()` — validation for ajax endpoint

## Registering Uploaders Globally

Map a custom field type to an uploader in a service provider:

```php
// AppServiceProvider::boot()
app('UploadersRepository')->addUploaderClasses([
    'custom_upload' => \App\Uploaders\CustomUploader::class,
], 'withFiles');
```

Now `CRUD::field('file')->type('custom_upload')->withFiles()` auto-uses your uploader.

## Subfields & Repeatable

Uploaders work inside repeatable/table subfields:

```php
CRUD::field([
    'name'   => 'attachments',
    'type'   => 'repeatable',
    'fields' => [
        ['name' => 'file', 'type' => 'upload', 'withFiles' => ['path' => 'attachments']],
        ['name' => 'label', 'type' => 'text'],
    ],
]);
```

The `withFiles` key on subfields accepts `true` (defaults) or an array of options. Backpack handles the repeatable container tracking automatically.

## Relationships (Pivot Uploads)

For `belongsToMany` with a file in the pivot table, you need a custom Pivot model:

```php
// Create a pivot model:
use Illuminate\Database\Eloquent\Relations\Pivot;

class ArticleCategory extends Pivot { /* empty */ }

// Update the relationship:
public function categories()
{
    return $this->belongsToMany(Category::class)
        ->withPivot('picture')
        ->using(ArticleCategory::class);
}
```

For `MorphToMany`, extend `MorphPivot` instead.

**Important:** Do NOT cast uploader attributes in your model. If you need casting, create a separate accessor.

## File Deletion

### When Entry Is Deleted

Uploaders auto-delete on `deleting` event only if the field is defined in `setupDeleteOperation()`:

```php
protected function setupDeleteOperation()
{
    CRUD::field('photo')->type('upload')->withFiles();
    // Or if same fields as Create:
    // $this->setupCreateOperation();
}
```

### When Entry Is Soft-Deleted

Soft-deleted models do NOT trigger file deletion. To handle this, add a `deleted` event on the model:

```php
protected static function booted()
{
    static::deleted(function ($model) {
        Storage::disk('public')->delete($model->photo);
    });
}
```

### Temp File Cleanup (Ajax Uploaders)

```php
// In routes/console.php
Schedule::command('backpack:purge-temporary-folder')->daily();
```

Config in `config/backpack/operations/ajax-uploads.php`.

## Gotchas

- `storage:link` is mandatory — Backpack uses the `public` disk by default.
- `withFiles()` works on both `CrudField` AND `CrudColumn`. Define in both `setupCreateOperation()` and `setupDeleteOperation()` for proper file lifecycle.
- Never cast uploader attributes on your model. Uploaders expect raw strings.
- `disk` and `path` in `withFiles()` take precedence over field-level `disk`/`prefix` — don't set both.
- Ajax uploaders (PRO) require `AjaxUploadOperation` trait on the controller.
- Repeatable subfields with uploads: Backpack automatically handles deduplication of uploader registrations.
- Relationship uploads (pivot tables) need a dedicated Pivot model class.
- Custom uploaders for custom field types must be registered via `UploadersRepository::addUploaderClasses()`.
