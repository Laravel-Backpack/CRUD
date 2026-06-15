# Upgrade Guide

---

This will guide you to upgrade from Backpack v6 to v7. The steps are color-coded by the probability that you will need it for your application: <span class="badge badge-danger text-white" style="text-decoration: none;">High</span>, <span class="badge badge-warning text-white" style="text-decoration: none;">Medium</span> and <span class="badge badge-secondary-soft" style="text-decoration: none;">Low</span>.  **At the very least, please read what's in bold**.

<a name="requirements"></a>
## Requirements

Please make sure your project respects the requirements below, before you start the upgrade process. You can check with ```php artisan backpack:version```:

- PHP 8.2+
- Laravel 12.x
- Backpack\CRUD 6.x
- 5-10 minutes (for most projects)

**If you're running Backpack version 3.x-5.x, please follow ALL other upgrade guides first, to incrementally get to use Backpack v6**. Test that your app works well with each version, after each upgrade. Only _afterwards_ can you follow this guide, to upgrade from v6 to v7. Previous upgrade guides:
- [upgrade from 5.x to 6.x](https://backpackforlaravel.com/docs/6.x/upgrade-guide);
- [upgrade from 4.1 to 5.x](https://backpackforlaravel.com/docs/5.x/upgrade-guide);
- [upgrade from 4.0 to 4.1](https://backpackforlaravel.com/docs/4.1/upgrade-guide);
- [upgrade from 3.6 to 4.0](https://backpackforlaravel.com/docs/4.0/upgrade-guide);
- [upgrade from 3.5 to 3.6](https://backpackforlaravel.com/docs/3.6/upgrade-guide);
- [upgrade from 3.4 to 3.5](https://backpackforlaravel.com/docs/3.5/upgrade-guide);
- [upgrade from 3.3 to 3.4](https://backpackforlaravel.com/docs/3.4/upgrade-guide);

<a name="upgraade-steps"></a>
## Upgrade Steps

<a name="step-0" href="#step-0" class="badge badge-danger" style="text-decoration: none;">Step 0.</a> **[Upgrade to Laravel 12](https://laravel.com/docs/12.x/upgrade) if you don't use it yet, then test to confirm your app is working fine.**

<a name="upgrade-command"></a>
### Upgrade Command

<a name="step-1" href="#step-1" class="badge badge-danger text-white" style="text-decoration: none;">Step 1.</a> We've created a command to help you run a lot of the steps in this upgrade guide, automatically. To run it:

```bash
# update your existing v6 installation, to get the upgrade command
composer update

# before calling the next command make sure you have no pending migrations with `php artisan migrate:status`
# and that your webserver is running and accessible in the URL you defined in .env `APP_URL`
php artisan backpack:upgrade

# follow the prompts in that command, then run
composer update
```

Now you should be running Backpack v7, with a lot of the steps below already finished. But don't stop here. Please read the upgrade steps below. Either double-check that the command has already done them or do them manually.

<a name="composer"></a>
### Composer

<a name="step-2" href="#step-2" class="badge badge-danger text-white" style="text-decoration: none;">Step 2.</a> Your `composer.json` should require the new version of Backpack CRUD:

```
        "backpack/crud": "^7.0",
```

<a name="step-3" href="#step-3" class="badge badge-danger text-white" style="text-decoration: none;">Step 3.</a> Bump the version of any first-party Backpack add-ons you have installed (eg. `backpack/pro`, `backpack/editable-columns` etc.) to the versions that support Backpack v6. For 3rd-party add-ons, please check each add-on's Github page. Here's a quick list of 1st party packages and versions:

```js
    'backpack/crud' => '^7.0',
    'backpack/theme-coreuiv2' => '^2.0',
    'backpack/theme-coreuiv4' => '^1.2',
    'backpack/theme-tabler' => '^2.0',
    'backpack/logmanager' => '^5.1',
    'backpack/settings' => '^3.2',
    'backpack/newscrud' => '^5.2',
    'backpack/permissionmanager' => '^7.3',
    'backpack/pagemanager' => '^3.4',
    'backpack/menucrud' => '^4.1',
    'backpack/backupmanager' => '^5.1',
    'backpack/editable-columns' => '^3.1',
    'backpack/revise-operation' => '^2.1',
    'backpack/medialibrary-uploaders' => '^2.0',
    'backpack/devtools' => '^4.0',
    'backpack/generators' => '^4.1',
    'backpack/activity-log' => '^2.1',
    'backpack/calendar-operation' => '^1.1',
    'backpack/language-switcher' => '^2.1',
    'backpack/pan-panel' => '^1.1',
    'backpack/pro' => '^3.0.0-alpha',
    'backpack/translation-manager' => '^1.1',
    'backpack/ckeditor-field' => '^1.0',
    'backpack/tinymce-field' => '^1.0',
```

<a name="models"></a>
### Models

No changes needed.

<a name="form-requests"></a>
### Form Requests

No changes needed.

<a name="routes"></a>
### Routes

No changes needed.

<a name="config"></a>
### Config

<a name="step-4" href="#step-4" class="badge badge-danger text-white" style="text-decoration: none;">Step 4.</a> **Operation Config Files** - We have added new configuration options in the files inside `config/backpack/operations/`. If you have those files published, it is recommended that you copy the new options in your files too.

<a name="step-5" href="#step-5" class="badge badge-danger text-white" style="text-decoration: none;">Step 5.</a> **Show Operation New Default** - inside `config/backpack/operations/show.php` you'll find a new option, to choose which component will be used on the show operation. By default it's the new component `bp-datagrid`, but you can switch to `bp-datalist` if you want to keep the same look as before:

```php
    // Which component to use for displaying the Show page?
    'component' => 'bp-datalist', // options: bp-datagrid, bp-datalist, or a custom component alias
```

<a name="step-6" href="#step-6" class="badge badge-danger text-white" style="text-decoration: none;">Step 6.</a> **Theme Tabler** - The default layout for theme tabler has changed. If you had the tabler config published you are good to go. **In case you don't have the tabler theme config published** and want to keep the old layout, you should publish it by running `php artisan vendor:publish --tag="theme-tabler-config"` and changing:
```diff
- 'layout' => 'horizontal',
+ 'layout' => 'horizontal_overlap',
```
You should also remove the glass skin and fuzzy background from the theme styles:
```diff
'styles' => [
        base_path('vendor/backpack/theme-tabler/resources/assets/css/skins/backpack-color-palette.css'),
-        base_path('vendor/backpack/theme-tabler/resources/assets/css/skins/glass.css'),
-        base_path('vendor/backpack/theme-tabler/resources/assets/css/skins/fuzzy-background.css'),
    ],
```

<a name="controllers"></a>
### CrudControllers

<a name="step-7" href="#step-7" class="badge badge-danger text-white" style="text-decoration: none;">Step 7.</a> The `wysiwyg` field and column have been removed, because they were hiding functionality. But don't worry, they were just alias columns. Behind the scenes, they were just loading the `ckeditor` field and `text` column respectively. If you're using the `wysiwyg` column of field in your CrudController, replace them with their originals.

<a name="step-8" href="#step-8" class="badge badge-danger text-white" style="text-decoration: none;">Step 8.</a> The `ckeditor` field and column have been moved from the PRO package to an open-source addon. If you are using either of them and want to keep doing so, just run `composer require backpack/ckeditor-field`. Please note that CKEditor is a 3rd party JS library that requires _payment_ when used for commercial purposes (GPL licensed). See their [pricing page](https://ckeditor.com/pricing/) for options. We can give away a few sub-licenses every year - [contact us](https://backpackforlaravel.com/contact) to see if any are still available. If you would rather move away from CKEditor, you can just change your field/column to use `summernote` instead, they are both HTML editors so your content should be compatible. Summernote has more limited editing options, but it's MIT-licensed.

<a name="step-9" href="#step-9" class="badge badge-danger text-white" style="text-decoration: none;">Step 9.</a> Similarly, the `tinymce` field and column have been moved from the PRO package to an open-source addon. If you are using either of them and want to keep doing so, just run `composer require backpack/tinymce-field`. Please note that TinyMCE is a 3rd party JS library that normally requires _payment_ when used for commercial purposes (GLP licensed). See their [pricing page](https://www.tiny.cloud/pricing/) for options. You can sign-up for [free commercial licenses on their website here](https://www.tiny.cloud/get-tiny/). If you would rather move away from TinyMCE, you can just change your field/column to use `summernote` instead, they are both HTML editors so your content should be compatible. Summernote has more limited editing options, but it's MIT-licensed.

<a name="step-9a" href="#step-9a" class="badge badge-danger text-white" style="text-decoration: none;">Step 9.A.</a> **Dropzone Field** - To standardize the implementation of the `dropzone` field, in v7 it uses a dedicated operation to handle uploads `AjaxUploadOperation` and a validation rule `ValidDropzone` to validate the uploaded files. If you are using the `dropzone` field and want to help upgrading it, you can run the following command:

```bash
php artisan backpack:upgrade-dropzone
```

<a name="step-9b" href="#step-9b" class="badge badge-danger text-white" style="text-decoration: none;">Step 9.B.</a> **EasyMDE Field** - The `easymde` field now also uses the `AjaxUploadOperation` to handle uploads. To secure the endpoint, we've introduced a new validation rule `ValidEasyMDE`. By default the endpoint will only allow images up to 1MB. If you want to use other file types or sizes, you MUST use the `ValidEasyMDE` rule in your FormRequest to validate the field.

<a href="assets"></a>
### CSS & JS Assets

All 3rd party dependencies have been updated to their latest versions. A lot of CSS & JS assets have suffered changes. Fortunately, thanks to Basset, we can easily clear the old files and get the new ones.


<a name="views"></a>
### Views

<a name="step-10" href="#step-10" class="badge badge-warning text-white" style="text-decoration: none;">Step 10.</a>  Many **views** have suffered tiny changes, for various reasons. They should still work in your context, but you should consider updating them to get the latest features. Here's a quick way to see if you have overidden any core files from Backpack packages, and those files have had changes:

```
# run this in your terminal, to register these 2 custom functions
# the functions will only be available in the current terminal session
# (if you close the terminal, the functions need to be pasted again)

# anychange()
# - receives 2 directories as parameters
# - the result is a list of files that are present in both directories, but have differences

anychange() { src=${1%/}; dst=${2%/}; find "$src" -type f -print0 | while IFS= read -r -d '' f; do rel=${f#"$src/"}; g="$dst/$rel"; if [ -f "$g" ] && ! cmp -s "$f" "$g"; then echo "$rel"; fi; done; }

# anydiff()
# - receives 2 directories as parameters
# - the result is the actual diffs of files that are present in both directories, but have differences

anydiff() { src=${1%/}; dst=${2%/}; find "$src" -type f -print0 | while IFS= read -r -d '' f; do rel=${f#"$src/"}; g="$dst/$rel"; [ -f "$g" ] && echo ">>> Diff for $rel" && diff -u "$f" "$g" && echo; done; }
```

Here are the commands we recommend you run, at a minimum:
```
anychange resources/views/vendor/backpack/crud vendor/backpack/crud/src/resources/views/crud
anychange resources/views/vendor/backpack/ui vendor/backpack/crud/src/resources/views/ui
anychange resources/views/vendor/backpack/crud vendor/backpack/pro/resources/views
```

If _you do_ have differences, you can then do a diff between your files and ours, to see the difference. You can do that using a diff app like [Kaleidoscope](https://kaleidoscope.app/) or using the `diff` command in your terminal. For example:

```
# compare the file from the project with the new file from the package
diff resources/views/vendor/backpack/crud/fields/datetime_picker.blade.php vendor/backpack/pro/resources/views/fields/datetime_picker.blade.php
```

<a name="step-10a" href="#step-10a" class="badge badge-danger text-white" style="text-decoration: none;">Step 10.A.</a> If the `anychange` command has told you there are differences in the list operation. Yes, the **List Operation view** has received significant changes. We decoupled the datatable from the view, so that you can use the table anywhere you would like. Most of the code is still identical but moved to `datatable.blade.php`. The `list.blade.php` view now only includes the mentioned datatable component. If you had customized the `list.blade.php` you should move your customizations to `datatable.blade.php`. If you've overidden it, you should do a diff and implement the changes. You can do that using a diff app like [Kaleidoscope](https://kaleidoscope.app/) or using the `diff` command in your terminal. For example:

```
# compare the file from the project with the new file from the package
diff resources/views/vendor/backpack/crud/list.blade.php vendor/backpack/crud/src/resources/views/crud/list.blade.php
```

<a name="step-10b" href="#step-10b" class="badge badge-danger text-white" style="text-decoration: none;">Step 10.B.</a> If the `anychange` command has told you there are differences in the filters. Yes,  **Filters Views** from Backpack/PRO have all suffered considerable changes, mostly to their JS. That's because we've liberated them - and [filters can now we used in custom pages as well](/docs/{{version}}/crud-filters#use-filters-on-custom-admin-panel-pages). If you have overridden the default filters, or created custom filters - they should still work. But to make them work on custom pages, you should do the same changes we did.  If you've overidden it, you should do a diff and implement the changes. You can do that using a diff app like [Kaleidoscope](https://kaleidoscope.app/) or using the `diff` command in your terminal. For example:

```
# compare the file from the project with the new file from the package
diff resources/views/vendor/backpack/crud/filters/simple.blade.php vendor/backpack/pro/resources/views/filters/simple.blade.php
```

<a name="step-10c" href="#step-10c" class="badge badge-warning text-white" style="text-decoration: none;">Step 10.C.</a> If the `anychange` command has told you there are differences in the select2 fields. Yes, **Select2 Fields** from Backpack/PRO have all suffered tiny changes, to make them work inside the DataForm Modal. If you have overridden `select2_multiple`, `select2_nested`, `select2_json_from_api`, `select2_grouped`, `select2_from_array`, `select2_from_ajax`, `select2_from_ajax_multiple`, `select2` or `relationship`... or created any custom select2 fields - they should still work in your context. But to make them work inside the DataForm modal, you should do the same tiny changes we did. Do a diff between your files and ours, to see the difference. You can do that using a diff app like [Kaleidoscope](https://kaleidoscope.app/) or using the `diff` command in your terminal. For example:

```
# compare the file from the project with the new file from the package
diff resources/views/vendor/backpack/crud/fields/select2_multiple.blade.php vendor/backpack/pro/resources/views/fields/select2_multiple.blade.php
```

<a name="security"></a>
### Security

No changes needed.

<a name="cache"></a>
### Cache

<a name="step-11" href="#step-11" class="badge badge-danger text-white" style="text-decoration: none;">Step 11.</a> Clear your app's cache:
```
php artisan basset:clear
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

If the table view still looks wonky (search bar out of place, big + instead of ellipsis), then do a hard-reload in your browser (Cmd+Shift+R or Ctrl+Shift+F5) to purge the browser cache too.

---

<a name="step-12" href="#step-12" class="badge badge-danger text-white" style="text-decoration: none;">Step 12.</a> If your pages are slow to load, that's because Basset caching the assets as you load the pages, so your first pageload will be quite slow. If you find that annoying, run `php artisan basset:cache` to cache all CSS and JS assets. Alternatively, if you want Basset NOT to run because you're making changes to CSS and JS files, you can add `BASSET_DEV_MODE=true` to your `.ENV` file.

---

<a name="addons"></a>
### Upgrade Add-ons


#### Backpack/FileManager

Using the File Manager package? Most of the views that weren't in use  were removed, and the dependencies were bumped. If you didn't do any customization you should delete the `resources/views/vendor/elfinder` (`rm -rf resources/views/vendor/elfinder`) folder. No need to publish any views anymore if you are not customizing them. If you were, publish the new view files (`php artisan vendor:publish --provider="Backpack\FileManager\FileManagerServiceProvider" --tag="elfinder-views"`). Then apply your customization on the new files, now located at: `resources/views/vendor/backpack/filemanager/`

Additional the `browse` and `browse_multiple` **fields/columns** are now part of this package. If you previously made any modifications to this fields/columns you should publish the new views (`php artisan vendor:publish --provider="Backpack\FileManager\FileManagerServiceProvider" --tag="filemanager-fields"` and `php artisan vendor:publish --provider="Backpack\FileManager\FileManagerServiceProvider" --tag="filemanager-columns"`), and carry over the modifications from the old files to this new files.

#### Backpack/Basset

By upgrading Backpack from v6 to v7, you've automatically upgraded from Basset v1 to v2. This new version changes some default behaviour, so please [read more about it here](https://github.com/Laravel-Backpack/basset). Most projects will not be affected.

---

**You're done! Good job.** Thank you for taking the time to upgrade. Now you can:
- thoroughly test your application and your admin panel;
- start using the [new features in Backpack v7](/docs/{{version}}/release-notes);
