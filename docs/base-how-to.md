# FAQs for the admin UI

---


<a name="look-and-feel"></a>
## Look and feel

<a name="text-direction-ltr-or-rtl"></a>
### Text direction: LTR or RTL

By default, the text direction is set to left-to-right. If your UI is in Arabic, Hebrew or any other language that needs to show right-to-left, you can easily enable that - just go to `config/backpack/ui.php` and change the `html_direction` variable to `rtl`:

```
    // Direction, according to language
    // (left-to-right vs right-to-left)
    'html_direction' => 'ltr',
```

<a name="customize-menu-or-sidebar"></a>
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

<a name="customize-dashboard"></a>
### Customize the dashboard

The dashboard is shown from ```Backpack\Base\app\Http\Controller\AdminController.php::dashboard()```. If you take a look at that method, you'll see that the only thing it does is to set a title, breadcrumbs, and return a view: ```backpack::dashboard```.

In order to place something else inside that view, like [widgets](/docs/{{version}}/base-widgets), simply publish that view in your project, and Backpack will pick it up, instead of the one in the package. Create a ```resources/views/vendor/backpack/ui/dashboard.blade.php``` file:

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

Take a look at the [widgets](/docs/{{version}}/base-widgets) we have - you can easily use those in your dashboard. You can also add whatever HTML you want inside the content block - check the [Backstrap HTML Template](https://backstrap.net/widgets.html) for design components you can copy-paste to speed up your custom HTML.

<a name="customize-general-layout-design"></a>
### Customizing the design of the menu / sidebar / footer

Starting with Backpack v6, we have multiple themes. Each theme provides some configuration options, for you to change CSS classes in the header, body, footer, tabler etc.

Please take a look at your theme's config file or README on Github, to see what you can change and how.

<a name="publish-mobile-and-favicon-headers-and-assets"></a>
### Publish mobile and favicon headers and assets

A very common use case is that your users bookmark or add your admin panel to their home screen on their mobile devices. In order to make that experience better, you can publish the mobile and favicon headers and assets. You can do that by running:

```bash
php artisan backpack:publish-header-metas
```

This will ask you a few questions and then publish the necessary files. You can then customize them as you please to fit your branding.
Files that already exist will not be replaced, so if you want to re-publish Backpack files you need to delete the already published first. 


<a name="create-a-new-theme"></a>
### Create a new theme / child theme

You can create a theme with your own HTML. Create a folder with all the views you want to overwrite, then change ```view_namespace``` inside your ```config/backpack/ui.php``` to point to that folder. All views will be loaded from _that_ folder if they exist, then from ```resources/views/vendor/backpack/base```, then from the Base package.

You can use child themes to create packages for your Backpack admin panels to look different (and re-use across projects). For more info on how to create a theme, see [this guide](/docs/{{version}}/add-ons-tutorial-how-to-create-a-theme).


<a name="add-custom-javascript"></a>
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

<a name="add-custom-css"></a>
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

<a name="add-custom-icons"></a>
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

<a name="customize-overlays-css"></a>
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

<a name="how-to-add-vue-js-on-all-Backpack-pages"></a>
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


<a name="customize-translations"></a>
### Customize the translated strings (aka overwrite the language files)

Backpack uses the default Laravel lang configuration, to choose the admin panel language. So it will use whatever you set in `config/app.php` inside the `locale` key. By default it's `en` (english). We provide translations in more than 20 languages including RTL (arabic).

Backpack uses Laravel translations across the admin panel, to easily translate strings (ex: `{{ trans('backpack::base.already_have_an_account') }}`).
If you don't like a translation, you're welcome to submit a PR to [Backpack CRUD repository](https://github.com/Laravel-Backpack/CRUD) to correct it for all users of your language. If you only want to correct it inside your app, or need to add a new translation string, you can *create a new file in your `resources/lang/vendor/backpack/en/base.php`* (similarly, `crud.php` or any other file). Any language strings that are inside your app, in the right folder, will be preferred over the ones in the package.

Alternatively, if you need to customize A LOT of strings, you can use:
```bash
php artisan vendor:publish --provider="Backpack\CRUD\BackpackServiceProvider" --tag="lang"
```
which will publish ALL lang files, for ALL languages, inside `resources/lang/vendor/backpack`. But it's highly unlikely you need to modify all of them. In case you do publish all languages, please delete the ones you didn't change. That way, you only keep what's custom in your custom files, and it'll be easier to upgrade those files in the future.

#### Translate the Laravel Framework strings

Please note that **Backpack does NOT provide** translation strings for validation errors and other internal Laravel messages like in email templates. Those are provided by Laravel itself, and Laravel only provides the English versions.
To get validation error messages in all languages you want, we **highly recommend** installing and using https://github.com/Laravel-Lang/lang which provides exactly that.


<a name="use-the-same-html-and-css-for-front-end"></a>
### Use the HTML & CSS for the front-end (Backstrap for front-facing website)

If you like how Backpack looks and feels you can use the same interface to power your front-end, simply by making sure your blade view extend Backpack's layout file, instead of a layout file you'd create. Make sure your blade views extend `backpack_view('blank')` or create a layout file similar to our `layouts/top_left.blade.php` that better fits your needs. Then use it across your app:

```php
@extends(backpack_view('blank'))

<div>Something</div>
```

It's a good idea to go through our main layout file - [`layouts/top_left.blade.php`](https://github.com/Laravel-Backpack/CRUD/blob/master/src/resources/views/base/layouts/top_left.blade.php) - to understand how it works and how you can use it to your advantage. Most notably, you can:
- use our `before_styles` and `after_styles` sections to easily _include_ CSS there - `@section('after_styles')`;
- use our `before_styles` and `after_styles` stacks to easily _push_ CSS there - `@push('after_styles')`;
- use our `before_scripts` and `after_scripts` sections to easily _include_ JS there - `@section('after_scripts')`;
- use our `before_scripts` and `after_scripts` stacks to easily _push_ JS there - `@push('after_scripts')`;



<a name="authentication"></a>
## Authentication

<a name="customize-auth-controllers"></a>
### Customizing the Auth controllers

In ```config/backpack/base.php``` you'll find these configuration options:

```php
    // Set this to false if you would like to use your own AuthController and PasswordController
    // (you then need to setup your auth routes manually in your routes.php file)
    'setup_auth_routes' => true,
```

You can change both ```setup_auth_routes``` to ```false```. This means Backpack\Base won't register the Auth routes any more, so you'll have to manually register them in your route file, to point to the Auth controllers you want. If you're going to use the Auth controllers that Laravel generates, these are the routes you can use:
```php
Route::group(['middleware' => 'web', 'prefix' => config('backpack.base.route_prefix')], function () {
    Route::auth();
    Route::get('logout', 'Auth\LoginController@logout');
});
```

<a name="customize-routes"></a>
### Customize the routes

#### Custom routes - option 1

You can place a new routes file in your ```app/routes/backpack/base.php```. If a file is present there, no default Backpack\Base routes will be loaded, only what's present in that file. You can use the routes file ```vendor/backpack/base/src/resources/views/base.php``` as an example, and customize whatever you want.

#### Custom routes - option 2

In ```config/backpack/base.php``` you'll find these configuration options:

```php

    /*
    |--------------------------------------------------------------------------
    | Routing
    |--------------------------------------------------------------------------
    */

    // The prefix used in all base routes (the 'admin' in admin/dashboard)
    'route_prefix' => 'admin',

    // Set this to false if you would like to use your own AuthController and PasswordController
    // (you then need to setup your auth routes manually in your routes.php file)
    'setup_auth_routes' => true,

    // Set this to false if you would like to skip adding the dashboard routes
    // (you then need to overwrite the login route on your AuthController)
    'setup_dashboard_routes' => true,
```

In order to completely customize the auth routes, you can change both ```setup_auth_routes``` and ```setup_dashboard_routes``` to ```false```. This means Backpack\Base won't register any routes any more, so you'll have to manually register them in your route file. Here's what you can use to get started:
```php
Route::group(['middleware' => 'web', 'prefix' => config('backpack.base.route_prefix'), 'namespace' => 'Backpack\Base\app\Http\Controllers'], function () {
    Route::auth();
    Route::get('logout', 'Auth\LoginController@logout');
    Route::get('dashboard', 'AdminController@dashboard');
    Route::get('/', 'AdminController@redirect');
});
```

<a name="use-sparate-login-for-users-and-admins"></a>
### Use separate login/register forms for users and admins

This is a default in Backpack v4.

Backpack's authentication uses a completely separate authentication driver, provider, guard and password broker. They're all named ```backpack```, and registered in the vendor folder, invisible to you.

If you need a separate login for user, just go ahead and create it. [Add the Laravel authentication, like instructed in the Laravel documentation](https://laravel.com/docs/authentication#authentication-quickstart): ```php artisan make:auth```. You'll then have:
- the user login at ```/login``` -> using the AuthenticationController Laravel provides
- the admin login at ```/admin/login``` -> using the AuthenticationControllers Backpack provides

The user login will be using Laravel's default authentication driver, provider, guard and password broker, from ```config/auth.php```.

Backpack's authentication driver, provider, guard and password broker can easily be overwritten by creating a driver/provider/guard/broker with the ```backpack``` name inside your ```config/auth.php```. If one named ```backpack``` exists there, Backpack will use that instead.

<a name="overwrite-authentication-driver-provider-guard-or-password-broker"></a>
### Overwrite Backpack authentication driver, provider, guard or password broker

Backpack's authentication uses a completely separate authentication driver, provider, guard and password broker. Backpack adds them to what's defined in ```config/auth.php``` on runtime, and they're all named ```backpack```.

To change a setting in how Backpack's driver/provider/guard or password broker works, create a driver/provider/guard/broker with the ```backpack``` name inside your ```config/auth.php```. If one named ```backpack``` exists there, Backpack will use that instead.

<a name="use-separate-sessions-for-admins-and-users"></a>
### Use separate sessions for admin&user authentication

This is a default in Backpack v4.

<a name="login-with-username-instead-of-email"></a>
### Login with username instead of email

1. Create a ```username``` column in your users table and add it in ```$fillable``` on your ```User``` model. Best to do this with a migration.
2. Remove the UNIQUE and NOT NULL constraints from ```email``` on your table. Best to do this with a migration. Alternatively, delete your ```email``` column and remove it from ```$fillable``` on your ```User``` model. If you already have a CRUD for users, you might also need to delete it from the Request, and from your UserCrudController.
3. Change your ```config/backpack/base.php``` config options:
```php
    // Username column for authentication
    // The Backpack default is the same as the Laravel default (email)
    // If you need to switch to username, you also need to create that column in your db
    'authentication_column' => 'username',
    'authentication_column_name' => 'Username',
```
That's it. This will:
- use ```username``` for login;
- use ```username``` for registration;
- use ```username``` in My Account, when a user wants to change his info;
- completely disable the password recovery (if you've deleted the ```email``` db column);


<a name="use-your-own-user-model-instead-of-backpack-user"></a>
### Use your own User model instead of App\User

By default, authentication and everything else inside Backpack is done using the ```App\User``` model. If you change the location of ```App\User```, or want to use a different User model for whatever other reason, you can do so by changing ```user_model_fqn``` in ```config/backpack/base.php``` to your new class.


<a name="use-your-own-profile-image-avatar"></a>
### Use your own profile image (avatar)

By default, Backpack will use Gravatar to show the profile image for the currently logged in backpack user. In order to change this, you can use the option in ```config/backpack/base.php```:
```php
// What kind of avatar will you like to show to the user?
// Default: gravatar (automatically use the gravatar for his email)
//
// Other options:
// - placehold (generic image with his first letter)
// - example_method_name (specify the method on the User model that returns the URL)
'avatar_type' => 'gravatar',
```

Please note that this does not allow the user to change his profile image.


<a name="add-one-or-more-fields-to-the-register-form"></a>
### Add one or more fields to the Register form

To add a new field to the Registration page, you should:

**Step 1.** Overwrite the registration route, so it leads to _your_ controller, instead of the one in the package. We recommend you add it your ```routes/backpack/custom.php```, BEFORE the route group where you define your CRUDs:

```php
Route::get('admin/register', 'App\Http\Controllers\Admin\Auth\RegisterController@showRegistrationForm')->name('backpack.auth.register');
```

**Step 2.** Create the new RegisterController somewhere in your project, that extends the RegisterController in the package, and overwrites the validation & user creation methods. For example:

```php
<?php
namespace App\Http\Controllers\Admin\Auth;

use Backpack\CRUD\app\Http\Controllers\Auth\RegisterController as BackpackRegisterController;

class RegisterController extends BackpackRegisterController
{
    /**
     * Get a validator for an incoming registration request.
     *
     * @param array $data
     *
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        $user_model_fqn = config('backpack.base.user_model_fqn');
        $user = new $user_model_fqn();
        $users_table = $user->getTable();
        $email_validation = backpack_authentication_column() == 'email' ? 'email|' : '';

        return Validator::make($data, [
            'name'                             => 'required|max:255',
            backpack_authentication_column()   => 'required|'.$email_validation.'max:255|unique:'.$users_table,
            'password'                         => 'required|min:6|confirmed',
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param array $data
     *
     * @return User
     */
    protected function create(array $data)
    {
        $user_model_fqn = config('backpack.base.user_model_fqn');
        $user = new $user_model_fqn();

        return $user->create([
            'name'                             => $data['name'],
            backpack_authentication_column()   => $data[backpack_authentication_column()],
            'password'                         => bcrypt($data['password']),
        ]);
    }
}
```
Add whatever validation rules & inputs you want, in addition to name and password.

**Step 3.** Add the actual inputs to your HTML. You can easily overwrite the register view by adding this method to the same RegisterController:

```php
    public function showRegistrationForm()
    {

        // if registration is closed, deny access
        if (! config('backpack.base.registration_open')) {
            abort(403, trans('backpack::base.registration_closed'));
        }

        $this->data['title'] = trans('backpack::base.register'); // set the page title

        return view(backpack_view('auth.register'), $this->data);
    }
```
This will make the registration process pick up a view you can create, in ```resources/views/vendor/backpack/{theme}/auth/register.blade.php```. You can copy-paste the original view, and modify as you please. Including adding your own custom inputs. (replace {theme} with the theme you are using, by default is `theme-tabler`)

<a name="enable-email-verification-in-backpack-routes"></a>
### Enable email verification in Backpack routes

In Backpack CRUD 6.2 we introduced the ability to require email verification when accessing Backpack routes. To enable this feature please do the following:

**Step 1** - Make sure your user model (usually `App\Models\User`) implements the `Illuminate\Contracts\Auth\MustVerifyEmail` contract. [More info](https://laravel.com/docs/verification#model-preparation).

```php
<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    use Notifiable;

    // ...
}
```

**Step 2** - Make sure your user model table has a `email_verified_at` column (timestamp). New Laravel installations already have it, but if you upgraded from L8/L9 it's possible that the `email_verified_at` column is missing.

You can quickly create a new migration using `php artisan make:migration add_email_verified_at_column_to_users --table=users`, then use the code below:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->addColumn('timestamp', 'email_verified_at', ['nullable' => true])->after('email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['email_verified_at']);
        });
    }
};

```
Then run `php artisan migrate`. [More info](https://laravel.com/docs/verification#database-preparation).

**Step 3** - New Laravel 10/11 installations already have them in place so you can skip this step. If you came from earlier versions it's possible that they are missing in your app, in that case you can add them manually.

```php
// for Laravel 10:
protected $middlewareAliases = [
        // ... other middleware aliases
        'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
        // if you don't have the VaidateSignature middleware you can copy it from here:
        // https://github.com/laravel/laravel/blob/10.x/app/Http/Middleware/ValidateSignature.php
        'signed' => \App\Http\Middleware\ValidateSignature::class,
    ];
```

**Step 4** - Enable the functionality in `config/backpack/base.php` by changing `setup_email_validation_routes` to `true`. If you don't have this config key there, now is a good time to add it.



