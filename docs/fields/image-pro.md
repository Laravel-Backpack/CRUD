### image [PRO]

Upload an image and store it on the disk.

**Step 1.** Show the field.
```php
// image
CRUD::field([
    'label' => 'Profile Image',
    'name' => 'image',
    'type' => 'image',
    'crop' => true, // set to true to allow cropping, false to disable
    'aspect_ratio' => 1, // omit or set to 0 to allow any aspect ratio
]);
```
**NOTE:** `aspect_ratio` is a float that represents the ratio of the cropping rectangle height and width. Eg: Square = 1, Landscape = 2, Portrait = 0.5. You can, of course, use any value for more extreme rectangles.

**Step 2.** Choose how to handle the file upload process. Starting v6, you have two options:
- **Option 1.** Let Backpack handle the upload process for you. This is by far the most convenient option, because it's the easiest to implement and fully customizable. All you have to do is add the `withFiles => true` attribute to your field definition:
```php
CRUD::field([
    'name' => 'image',
    'label' => 'Profile Image',
    'type' => 'image',
    'withFiles' => true
]);
```
To know more about the `withFiles`, how it works and how to configure it, [ click here to read the documentation ](https://backpackforlaravel.com/docs/6.x/crud-uploaders).

- **Option 2.** Handle the upload process yourself. This is what happened in v5, so if you want to handle the upload by yourself you can [read the v5 upload docs here](https://backpackforlaravel.com/docs/5.x/crud-fields#image-pro).

> NOTE: if you are having trouble uploading big images, please check your php extensions **apcu** and/or **opcache**, users have reported some issues with these extensions when trying to upload very big images. REFS: https://github.com/Laravel-Backpack/CRUD/issues/3457
