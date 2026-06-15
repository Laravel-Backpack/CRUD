# AjaxUpload Operation

---

<a name="about"></a>
## About

This operation setup an endpoint to handle file uploads via AJAX in your CrudController. It's used by fields like [dropzone](https://backpackforlaravel.com/docs/crud-fields#dropzone) and [easymde](https://backpackforlaravel.com/docs/crud-fields#easymde).

<a name="security"></a>
## Security

There is a reason why the security is the first item in this list. It's important to understand that this operation creates an endpoint where authorized users can upload files to your server. Everytime uploads are allowed, you should be aware of the security implications and take the necessary steps to prevent unauthorized uploads to your server.

All Backpack fields that allow the user to upload files have their correspondent [validation rules](https://backpackforlaravel.com/docs/custom-validation-rules) to ensure that only allowed files are uploaded.

This enpoint comes with a default security configuration, only allow the upload of `jpg/jpeg/png/gif` images with a maximum size of 1MB. To overwrite this configuration, you need to validate your field using the previously mentioned rules that apply for your field, for example `ValidDropzone` or `ValidEasyMDE`. 


<a name="how-to-use"></a>
## How to Use

In order to enable this operation, in your CrudController you need to **use the `AjaxUploadOperation` trait**. For example:

```php
    use \Backpack\Pro\Http\Controllers\Operations\AjaxUploadOperation;

    protected function setupAjaxUploadOperation()
    {
        if(! backpack_user()->hasRole('admin')) {
            $this->crud->denyAccess('ajax-upload');
        }
    }
```

This operation will create an endpoint at `/crud/{EntityName}/ajax-upload`. This endpoint is used by the fields to upload files via AJAX. How those fields are stored is the responsability of each of the [field uploaders](https://backpackforlaravel.com/docs/crud-uploaders).

<a name="configuration"></a>
## Configuration

The operation accept has the following options: 
| Option | Description | Default |
| --- | --- | --- |
| `temporary_disk` | The disk where the files will be stored temporarly. | `public` |
| `temporary_folder` | The folder inside the temporary disk where the files will be stored. | `backpack/temp` |
| `purge_temporary_files_older_than` | The number of hours after which temporary files are deleted. | `72` |
| `purge_after_request_dispatch` | If the temporary files should be deleted after the request has been served. | `true` |


To configure the temporary directory for ALL ajax upload operations, call `php artisan vendor:publish --provider="Backpack\Pro\AddonServiceProvider" --tag="ajax-upload-config"` and then edit the `config/backpack/operations/ajax-upload.php` file to fit your needs. 

Alternatively, you can also configure the temporary directory only for the specific CRUDs by using:

```php
public function setupAjaxUploadOperation()
{
    CRUD::setOperationSetting('temporary_disk', 'public');
    CRUD::setOperationSetting('temporary_folder', 'backpack/temp');
    CRUD::setOperationSetting('purge_temporary_files_older_than', 72);
    CRUD::setOperationSetting('purge_after_request_dispatch', true);
}
```

**Delete Old Temp Files**

This enpoint automatically trigger the cleaning of the temporary directory files after the request has been served. Check `puge_after_request_dispatch` option to disable this behavior.
You can also run the `backpack:purge-temporary-files` command, to clean the temporary directory.

```bash
php artisan backpack:purge-temporary-files --older-than=24 --disk=public --path="backpack/temp"
```

It accepts the following optional parameters:
- `--older-than=24`: the number of hours after which temporary files are deleted.
- `--disk=public`: the disk used by the temporary files.
- `--path="backpack/temp"`: the folder inside the disk where files will be stored.


You can use any strategy to run this command periodically - a cron job, a scheduled task or hooking into application termination hooks. Laravel provides a very easy way to setup your scheduled tasks. You can read more about it [here](https://laravel.com/docs/scheduling). For example, you can run the command every hour by adding the following line to your `app/Console/Kernel.php` in the `schedule()` method:

```php
// app/Console/Kernel.php
$schedule->command('backpack:purge-temporary-files')->hourly();
```

After adding this, you need to setup a cron job that will process the Laravel scheduler. You can manually run it in development with `php artisan schedule:run`. For production, you can setup a cron job take care of it for you. You can read more about it [here](https://laravel.com/docs/scheduling#running-the-scheduler).
