## Miscellaneous

### Use the Media Library (File Manager)

The default Backpack installation doesn't come with a file management component. Because most projects don't need it. But we've created a first-party add-on that brings the power of [elFinder](http://elfinder.org/) to your Laravel projects. To install it, [follow the instructions on the add-ons page](https://github.com/Laravel-Backpack/FileManager). It's as easy as running:

```bash
# require the package
composer require backpack/filemanager

# then run the installation process
php artisan backpack:filemanager:install
```

If you've chosen to install [backpack/filemanager](https://github.com/Laravel-Backpack/FileManager), you'll have elFinder integrated into:
- TinyMCE (as "tinymce" field type)
- CKEditor (as "ckeditor" field type)
- CRUD (as "browse" and "browse_multiple" field types)
- stand-alone, at the */admin/elfinder* route;

For the integration, we use [barryvdh/laravel-elfinder](https://github.com/barryvdh/laravel-elfinder).

### How to manually install Backpack

If the automatic installation doesn't work for you and you need to manually install CRUD, here are all the commands it is running:

1) In your terminal:

``` bash
composer require backpack/crud
```

2) Instead of running ```php artisan backpack:install``` you can run:
```bash
php artisan vendor:publish --provider="Backpack\CRUD\BackpackServiceProvider" --tag="minimum"
php artisan migrate
php artisan backpack:publish-middleware
composer require --dev backpack/generators
php artisan basset:install --no-check --no-interaction

# then install ONE of the first-party themes:
php artisan backpack:require:theme-tabler
php artisan backpack:require:theme-coreuiv4
php artisan backpack:require:theme-coreuiv2

# then check assets can be correctly used
php artisan basset:check
```

### Overwrite a Method on the CrudPanel Object

Starting with Backpack v4, you can use a custom CrudPanel object instead of the one in the package. In your custom CrudPanel object, you can overwrite any method you want, but please note that this means that you're overwriting core components, and will be making it more difficult to upgrade to newer versions of Backpack.

You can do this in any of your service providers (ex: ```app/Providers/AppServiceProvider.php```) to load your class instead of the one in the package:

```php
$this->app->extend('crud', function () {
    return new \App\MyExtendedCrudPanel;
});
```

Details and implementation [here](https://github.com/Laravel-Backpack/CRUD/pull/1990).

### Error: Failed to Download Backpack PRO

When trying to install Backpack\PRO (or any of our closed-source add-ons, really), you might run into the following error message:

```bash
Downloading backpack/pro (1.1.1)
Failed to download backpack/pro from dist: The "https://backpackforlaravel.com/satis/download/dist/backpack/pro/backpack-pro-xxx-zip-zzz.zip" file could not be downloaded (HTTP/2 402 )
```

Or maybe:

```bash
Syncing backpack/pro (1.1.1) into cache
Cloning failed using an ssh key for authentication, enter your GitHub credentials to access private repos
Head to https://github.com/settings/tokens/new?scopes=repo&description=Composer+on+DESKTOP-BLABLA+2022-07-14+1559
to retrieve a token.
```

What's happening there? That is a general Composer error - "file could not be downloaded". The error itself doesn't give too much information, but we can make an educated guess.

**99% of the people who report this error have the same problem - they do not have access to that package version.** They bought updates until 1.0.13 (for example), so they DO NOT have access to the latest version (1.1.1 in this example). What you can do, in that case, is **lock the installation to the latest you have access to**, for example

```bash
composer require backpack/pro:"1.0.13"
```

Alternatively, you can purchase more access on the [Backpack website](https://backpackforlaravel.com/pricing). Or contact the team if there's a mistake.

--

How do you find out what's the last version you have access to?

(1) **Whenever the error above happens, Backpack will send you an email**, with details and instructions. **Check your email**, it will also include the latest version you have access to.

(2) [Your Tokens page](https://backpackforlaravel.com/user/tokens) will show more details. For each token you have, it will say when it stops giving you access to updates. If it doesn't say the last version directly, you can corroborate that last day with [the changelog](https://backpackforlaravel.com/products/pro-for-unlimited-projects/CHANGELOG.md ), to determine what's the last version that _you_ have access to.

--

Why the ugly, general error? Because Composer doesn't allow vendors to customize the error, unfortunately. Backpack's server returns a better error message, but Composer doesn't show it.

### Enable database transactions for create and update

In v6.6 we introduced the ability to enable database transactions for create and update operations. This is useful if you have a lot of relationships and you want to make sure that all of them are saved or none of them are saved. 
You can enable this feature globaly at `config/backpack/base.php` by enabling `useDatabaseTransactions`. 

> **Note:** This feature will be enable by default starting `v7`
