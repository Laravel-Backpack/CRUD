## FAQ about Uploaders

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

## Deleting temporary files

When using ajax uploaders, the files are uploaded to a temporary disk and path before being moved to the final disk and path. If by some reason the user does not finish the operation, those files may lay around in your server temporary folder. 
To delete them, we have created a `backpack:purge-temporary-folder` command that you can schedule to run every day, or in the time frame that better suits your needs.

```php
// in your routes/console
use Illuminate\Console\Scheduling\Schedule;

Schedule::command('backpack:purge-temporary-folder')->daily();

```

For additional configuration check the `config/backpack/operations/ajax-uploads.php` file. Those configurations can also be passed on a "per-command" basis, eg: `backpack:purge-temporary-folder --disk=public --path=temp --older-than=5`.

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

### Uploaders for Spatie MediaLibrary

The 3rd party package [`spatie/laravel-medialibrary`](https://spatie.be/docs/laravel-medialibrary/) gives you the power to associate files with Eloquent models. The package is incredibly popular, time-tested and well maintained.

To have Backpack upload and retrieve files using this package, we've created special Uploaders. Then it will be as easy as doing `CRUD::field('avatar')->type('image')->withMedia();`. For more information and installation instructions please see the docs on Github for [`backpack/medialibrary-uploaders`](https://github.com/Laravel-Backpack/medialibrary-uploaders).
