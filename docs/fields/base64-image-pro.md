### base64_image [PRO]

Upload an image and store it in the database as Base64. Notes:
- make sure the column type is LONGBLOB;
- detailed [instructions and customisations here](https://github.com/Laravel-Backpack/CRUD/pull/56#issue-164712261);

```php
// base64_image
CRUD::field([
    'label'        => "Profile Image",
    'name'         => "image",
    'filename'     => "image_filename", // set to null if not needed
    'type'         => 'base64_image',
    'aspect_ratio' => 1, // set to 0 to allow any aspect ratio
    'crop'         => true, // set to true to allow cropping, false to disable
    'src'          => NULL, // null to read straight from DB, otherwise set to model accessor function
]);
```
