# Install Optional Packages

---

Each Backpack package has its own installation instructions in its readme file. We duplicate them here for easy access.

Everything else is optional. Your project might use them or it might not. Only do each of the following steps if you need the functionality that package provides.

<a name="backup-manager"></a>
## BackupManager

[>> See screenshots and installation](https://github.com/Laravel-Backpack/BackupManager)

1) In your terminal

```bash
# Install the package
composer require backpack/backupmanager

# Publish the config file and lang files:
php artisan vendor:publish --provider="Backpack\BackupManager\BackupManagerServiceProvider"

# [optional] Add a menu item for it in resources/views/vendor/backpack/ui/inc/menu_items.blade.php:
php artisan backpack:add-menu-content "<x-backpack::menu-item title='Backups' icon='la la-hdd-o' :link=\"backpack_url('backup')\" />"
```

2) Add a new "disk" to config/filesystems.php:

```php
// used for Backpack/BackupManager
'backups' => [
    'driver' => 'local',
    'root'   => storage_path('backups'), // that's where your backups are stored by default: storage/backups
],
```
This is where you choose a different driver if you want your backups to be stored somewhere else (S3, Dropbox, Google Drive, Box, etc).

3) [optional] Modify your backup options in config/backup.php

4) [optional] Instruct Laravel to run the backups automatically in your console kernel:

```php
// app/Console/Kernel.php

protected function schedule(Schedule $schedule)
{
   $schedule->command('backup:clean')->daily()->at('04:00');
   $schedule->command('backup:run')->daily()->at('05:00');
}
```

5) [optional] If you need to change the path to the mysql_dump command, you can do that in your config/database.php file. For MAMP on Mac OS, add these to your mysql connection:
```php
'dump' => [
    'dump_binary_path' => '/Applications/MAMP/Library/bin/', // only the path, so without `mysqldump` or `pg_dump`
    'use_single_transaction',
    'timeout' => 60 * 5, // 5 minute timeout
]
```

<a name="log-manager"></a>
## LogManager

[>> See screenshots and installation](https://github.com/Laravel-Backpack/logmanager)


1) Install via composer:

```bash
composer require backpack/logmanager
```

2) Add a "storage" filesystem disk in config/filesystems.php:

```php
// used for Backpack/LogManager
'storage' => [
    'driver' => 'local',
    'root'   => storage_path(),
],
```

3) Configure Laravel to create a new log file for every day, in your .ENV file, if it's not already. Otherwise there will only be one file at all times.

```
APP_LOG=daily
```

or directly in your config/app.php file:
```php
'log' => env('APP_LOG', 'daily'),
```

4) [optional] Add a menu item for it in resources/views/vendor/backpack/ui/inc/menu_items.blade.php:

```bash
php artisan backpack:add-menu-content "<x-backpack::menu-item title='Logs' icon='la la-terminal' :link=\"backpack_url('log')\" />"
```
<a name="settings-manager"></a>
## Settings

An interface for the administrator to easily change application settings. Uses Laravel Backpack.

[>> See screenshots and installation](https://github.com/Laravel-Backpack/settings)

Installation:

```bash
# install the package
composer require backpack/settings

# run the migration
php artisan vendor:publish --provider="Backpack\Settings\SettingsServiceProvider"
php artisan migrate

# [optional] add a menu item for it in resources/views/vendor/backpack/ui/inc/menu_items.blade.php:
php artisan backpack:add-menu-content "<x-backpack::menu-item title='Settings' icon='la la-cog' :link=\"backpack_url('setting')\" />"

# [optional] insert some example dummy data to the database
php artisan db:seed --class="Backpack\Settings\database\seeds\SettingsTableSeeder"
```

<a name="page-manager"></a>
## PageManager

An admin panel where you, as a developer, can define templates with different fields, and the admin can choose between those templates to create/edit pages with content.

[>> See screenshots and installation](https://github.com/Laravel-Backpack/pagemanager)

<a name="permission-manager"></a>
## PermissionManager

An admin panel for user authentication on Laravel 5, using Backpack\CRUD. Add, edit, delete users, roles and permission.

[>> See screenshots and installation](https://github.com/Laravel-Backpack/PermissionManager)

<a name="menu-crud"></a>
## MenuCrud

An admin panel for menu items on Laravel 5, using Backpack\CRUD. Add, edit, reorder, nest, rename menu items and link them to Backpack\PageManager pages, external link or custom internal link.

[>> GitHub](https://github.com/Laravel-Backpack/MenuCRUD)

<a name="news-crud"></a>
## NewsCrud

Since NewsCRUD does not provide any extra functionality other than Backpack\CRUD, it is not a package. It's just a tutorial to show you how this can be achieved. In the future, CRUD examples like this one will be easily installed from the command line, from a central repository. Until then, you will need to manually create the files.

[>> GitHub](https://github.com/Laravel-Backpack/NewsCRUD)


<a name="file-manager"></a>
## FileManager

Backpack admin interface for files and folders, using [barryvdh/laravel-elfinder](https://github.com/barryvdh/laravel-elfinder).

[>> See screenshots and installation](https://github.com/Laravel-Backpack/FileManager)

Installation:

```bash
composer require backpack/filemanager
```

```bash
php artisan backpack:filemanager:install
```
