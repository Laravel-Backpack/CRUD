# FAQs for the admin UI

## Look and feel

### Preload assets for basset:cache

When using `@basset()` inside conditional Blade directives (`@if`), the `basset:cache` command cannot discover those assets (it scans source, doesn't evaluate conditions). Use `basset_preload` in `config/backpack/ui.php` to register them for caching:

```php
'basset_preload' => [
    'scripts' => [
        // Conditionally loaded locale scripts, dynamic dependencies, etc.
        'https://cdn.example.com/locale-es.js',
        'https://cdn.example.com/locale-fr.js',
    ],
    'styles' => [
        'https://cdn.example.com/theme-dark.css',
    ],
],
```

Assets listed here are **not** loaded on every page — they are only registered so `php artisan basset:cache` can internalize them. They are still loaded conditionally via `@basset()` in blade files.

### Text direction: LTR or RTL

By default, the text direction is set to left-to-right. If your UI is in Arabic, Hebrew or any other language that needs to show right-to-left, you can enable that - just go to `config/backpack/ui.php` and change the `html_direction` variable to `rtl`:

```
    // Direction, according to language
    // (left-to-right vs right-to-left)
    'html_direction' => 'ltr',
```

### Customize the menu or sidebar

During installation, Backpack publishes `resources/views/vendor/backpack/ui/inc/menu_items.blade.php`. That file is meant to contain all menu items, using [menu item components](/docs/{{version}}/base-components#available-components) for example:

```
<x-backpack::menu-item title="Tags" icon="la la-tag" :link="backpack_url('tags')" />

<x-backpack::menu-separator title="Some text for separation" />

<x-backpack::menu-dropdown title="Authentication" icon="la la-group">
    <x-backpack::menu-dropdown-item title="Users" icon="la la-user" :link="backpack_url('user')" />
    <x-backpack::menu-dropdown-item title="Roles" icon="la la-group" :link="backpack_url('role')" />
    <x-backpack::menu-dropdown-item title="Permissions" icon="la la-key" :link="backpack_url('permission')" />
</x-backpack::menu-dropdown>
```

Change that file as you please. You can also add custom HTML there, but please take note that if you change the theme, your custom HTML might not look good in that new theme.

### Customize the dashboard

The dashboard is shown from ```Backpack\Base\app\Http\Controller\AdminController.php::dashboard()```. If you take a look at that method, you'll see that the only thing it does is to set a title, breadcrumbs, and return a view: ```backpack::dashboard```.

To place something else inside that view, like [widgets](/docs/{{version}}/base-widgets), simply publish that view in your project, and Backpack will pick it up, instead of the one in the package. Create a ```resources/views/vendor/backpack/ui/dashboard.blade.php``` file:

```html
@extends(backpack_view('blank'))

@php
    $widgets['before_content'][] = [
        'type'        => 'jumbotron',
        'heading'     => trans('backpack::base.welcome'),
        'content'     => trans('backpack::base.use_sidebar'),
        'button_link' => backpack_url('logout'),
        'button_text' => trans('backpack::base.logout'),
    ];
@endphp

@section('content')
  <p>Your custom HTML can live here</p>
@endsection
```

To use information from the database, you can:
- [use view composers](https://laravel.com/docs/views#view-composers) to push variables inside this view, when it's loaded;
- load all your dashboard information using AJAX calls, if you're loading charts, reports, etc, and the DB queries might take a long time;
- use the full namespace for your models, like ```\App\Models\Product::count()```;

Take a look at the [widgets](/docs/{{version}}/base-widgets) we have - you can use those in your dashboard. You can also add whatever HTML you want inside the content block - check the [Backstrap HTML Template](https://backstrap.net/widgets.html) for design components you can copy-paste to speed up your custom HTML.

### Customizing the design of the menu / sidebar / footer

Starting with Backpack v6, we have multiple themes. Each theme provides some configuration options, for you to change CSS classes in the header, body, footer, tabler etc.

Please take a look at your theme's config file or README on Github, to see what you can change and how.

### Publish mobile and favicon headers and assets

A very common use case is that your users bookmark or add your admin panel to their home screen on their mobile devices. To make that experience better, you can publish the mobile and favicon headers and assets. You can do that by running:

```bash
php artisan backpack:publish-header-metas
```

This will ask you a few questions and then publish the necessary files. You can then customize them as you please to fit your branding.
Files that already exist will not be replaced, so if you want to re-publish Backpack files you need to delete the already published first. 

### Create a new theme / child theme

You can create a theme with your own HTML. Create a folder with all the views you want to overwrite, then change ```view_namespace``` inside your ```config/backpack/ui.php``` to point to that folder. All views will be loaded from _that_ folder if they exist, then from ```resources/views/vendor/backpack/base```, then from the Base package.

You can use child themes to create packages for your Backpack admin panels to look different (and re-use across projects). For more info on how to create a theme, see [this guide](/docs/{{version}}/add-ons-tutorial-how-to-create-a-theme).

### Add custom JavaScript to all admin panel pages

In ```config/backpack/ui.php``` you'll notice this config option:

```php
    // JS files that are loaded in all pages, using Laravel's asset() helper
    'scripts' => [
        // Backstrap includes jQuery, Bootstrap, CoreUI, PNotify, Popper
        'packages/backpack/base/js/bundle.js?v='.\PackageVersions\Versions::getVersion('backpack/base'),

        // examples (everything inside the bundle, loaded from CDN)
        // 'https://code.jquery.com/jquery-3.4.1.min.js',
        // 'https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js',
        // 'https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js',
        // 'https://unpkg.com/@coreui/coreui/dist/js/coreui.min.js',
        // 'https://cdnjs.cloudflare.com/ajax/libs/pace/1.0.2/pace.min.js',
        // 'https://unpkg.com/sweetalert/dist/sweetalert.min.js',
        // 'https://cdnjs.cloudflare.com/ajax/libs/noty/3.1.4/noty.min.js'

        // examples (VueJS or React)
        // 'https://unpkg.com/vue@2.4.4/dist/vue.min.js',
        // 'https://unpkg.com/react@16/umd/react.production.min.js',
        // 'https://unpkg.com/react-dom@16/umd/react-dom.production.min.js',
    ],
```

You can add files to this array, and they'll be loaded in all admin panels pages.

### Add custom CSS to all admin panel pages

In ```config/backpack/ui.php``` you'll notice this config option:

```php
    // CSS files that are loaded in all pages, using Laravel's asset() helper
    'styles' => [
        'packages/@digitallyhappy/backstrap/css/style.min.css',

        // Examples (the fonts above, loaded from CDN instead)
        // 'https://maxcdn.icons8.com/fonts/line-awesome/1.1/css/line-awesome-font-awesome.min.css',
        // 'https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic',
    ],
```

You can add files to this array, and they'll be loaded in all admin panels pages.

### Add custom icons in admin panel pages

Backpack uses Line Awesome by default. In you custom pages and features, it's recommended that you [choose icons from the LineAwesome website](https://icons8.com/line-awesome) - and use it the same way Backpack does (eg. `<i class="la la-home">`). One reason we chose Line Awesome is that it provides tons of icons, so you should be able to find icons for whatever you need.

However, if you want to add you own iconset, you can also do that. Since an iconset should only be a CSS file, you use the same procedure to add that iconset to your admin panel. In ```config/backpack/ui.php``` you'll notice this config option:

```php
    // CSS files that are loaded in all pages, using Laravel's asset() helper
    'styles' => [
        // Example file from disk:
        'path/to/file.css',

        // Example file from CDN:
        // 'https://maxcdn.icons8.com/fonts/line-awesome/1.1/css/line-awesome-font-awesome.min.css',
    ],
```

### Customize the look and feel of the admin panel (using CSS)

If you want to change the look and feel of the admin panel, you can create a custom CSS file wherever you want. We recommend you do it inside ```public/packages/myname/mycustomthemename/css/style.css``` folder so that it's easier to turn into a theme, if you decide later to share or re-use your CSS in other projects.

In ```config/backpack/ui.php``` add your file to this config option:

```php
    // CSS files that are loaded in all pages, using Laravel's asset() helper
    'styles' => [
        'packages/@digitallyhappy/backstrap/css/style.min.css',
         // ...
        'packages/myname/mycustomthemename/css/style.css',
    ],
```

This config option allows you to add CSS files that add style _on top_ of Backstrap, to make it look different. You can create a CSS file anywhere inside your ```public``` folder, and add it here.

### How to add VueJS to all Backpack pages

You can add any script you want inside all Backpack's pages by just adding it in your ```config/backpack/ui.php``` file:

```php

    // JS files that are loaded in all pages, using Laravel's asset() helper
    'scripts' => [
        // Backstrap includes jQuery, Bootstrap, CoreUI, PNotify, Popper
        'packages/backpack/base/js/bundle.js',

        // examples (everything inside the bundle, loaded from CDN)
        // 'https://code.jquery.com/jquery-3.4.1.min.js',
        // 'https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js',
        // 'https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js',
        // 'https://unpkg.com/@coreui/coreui/dist/js/coreui.min.js',
        // 'https://cdnjs.cloudflare.com/ajax/libs/pace/1.0.2/pace.min.js',
        // 'https://unpkg.com/sweetalert/dist/sweetalert.min.js',
        // 'https://cdnjs.cloudflare.com/ajax/libs/noty/3.1.4/noty.min.js'

        // examples (VueJS or React)
        // 'https://unpkg.com/vue@2.4.4/dist/vue.min.js',
        // 'https://unpkg.com/react@16/umd/react.production.min.js',
        // 'https://unpkg.com/react-dom@16/umd/react-dom.production.min.js',
    ],
```

You should be able to load Vue.JS by just uncommenting that one line. Or providing a link to a locally stored VueJS file.

### Customize the translated strings (aka overwrite the language files)

Backpack uses the default Laravel lang configuration, to choose the admin panel language. So it will use whatever you set in `config/app.php` inside the `locale` key. By default it's `en` (english). We provide translations in more than 20 languages including RTL (arabic).

Backpack uses Laravel translations across the admin panel, to translate strings (ex: `{{ trans('backpack::base.already_have_an_account') }}`).
If you don't like a translation, you're welcome to submit a PR to [Backpack CRUD repository](https://github.com/Laravel-Backpack/CRUD) to correct it for all users of your language. If you only want to correct it inside your app, or need to add a new translation string, you can *create a new file in your `resources/lang/vendor/backpack/en/base.php`* (similarly, `crud.php` or any other file). Any language strings that are inside your app, in the right folder, will be preferred over the ones in the package.

Alternatively, if you need to customize A LOT of strings, you can use:
```bash
php artisan vendor:publish --provider="Backpack\CRUD\BackpackServiceProvider" --tag="lang"
```
which will publish ALL lang files, for ALL languages, inside `resources/lang/vendor/backpack`. But it's highly unlikely you need to modify all of them. In case you do publish all languages, please delete the ones you didn't change. That way, you only keep what's custom in your custom files, and it'll be easier to upgrade those files in the future.

#### Translate the Laravel Framework strings

Please note that **Backpack does NOT provide** translation strings for validation errors and other internal Laravel messages like in email templates. Those are provided by Laravel itself, and Laravel only provides the English versions.
To get validation error messages in all languages you want, we **highly recommend** installing and using https://github.com/Laravel-Lang/lang which provides exactly that.

### Use the HTML & CSS for the front-end (Backstrap for front-facing website)

If you like how Backpack looks and feels you can use the same interface to power your front-end, simply by making sure your blade view extend Backpack's layout file, instead of a layout file you'd create. Make sure your blade views extend `backpack_view('blank')` or create a layout file similar to our `layouts/top_left.blade.php` that better fits your needs. Then use it across your app:

```php
@extends(backpack_view('blank'))

<div>Something</div>
```

It's a good idea to go through our main layout file - [`layouts/top_left.blade.php`](https://github.com/Laravel-Backpack/CRUD/blob/master/src/resources/views/base/layouts/top_left.blade.php) - to understand how it works and how you can use it to your advantage. Most notably, you can:
- use our `before_styles` and `after_styles` sections to _include_ CSS there - `@section('after_styles')`;
- use our `before_styles` and `after_styles` stacks to _push_ CSS there - `@push('after_styles')`;
- use our `before_scripts` and `after_scripts` sections to _include_ JS there - `@section('after_scripts')`;
- use our `before_scripts` and `after_scripts` stacks to _push_ JS there - `@push('after_scripts')`;
