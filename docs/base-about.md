# About Backpack's User Interface

---

Backpack provides an admin interface that includes:
- HTML components and layouts, provided by Backpack themes;
- [sweetalert](https://sweetalert.js.org/) for triggering pretty confirmation modals;
- [noty](https://ned.im/noty/#/) to show notification bubbles upon error/success/warning/info - triggered from JavaScript;
- [prologue/alerts](https://github.com/prologuephp/alerts) for triggering notification bubbles from PHP (both on the same page and using flashdata);
- a separate authentication system for your admins;
- pretty error pages for most common errors;
- a menu you can customize;
- a few helpers you can use throughout your admin panel;

<a name="layout-and-design"></a>
## Layout & Design

<a name="general"></a>
### General

Depending on what theme you are using, Backpack pulls in a different Bootstrap HTML Template. You can use its HTML components for any custom pages or sections you create, by copy-pasting the HTML from their website:
- [`theme-tabler`](https://github.com/Laravel-Backpack/theme-tabler) uses [Tabler](https://tabler.io/preview) (the default) ;
- [`theme-coreuiv4`](https://github.com/Laravel-Backpack/theme-coreuiv4) uses [CoreUI v4](https://coreui.io/demos/bootstrap/4.2/free/);
- [`theme-coreuiv2`](https://github.com/Laravel-Backpack/theme-coreuiv2) uses [Backstrap](https://backstrap.net/) which is a fork of CoreUI v2 (not recommended - only use if you absolutely need support for IE);

<a name="published-views"></a>
### New Files in Your App

After installation, you'll notice Backpack has added a few files:

**1) View to ```resources/views/vendor/backpack/ui/inc/menu_items.blade.php```**

This  file is used to show the contents of the menu. It's been published there so that you can easily modify its contents, by editing its HTML.

**2) Middleware to ```app/Http/Middleware/CheckIfAdmin.php```**

This middleware is used to test if users have access to admin panel pages. You can (and should customize it) if you have both users and admins in your app.

**3) Route file to ```routes/backpack/custom.php```**

This route file is for convenience and convention. We recommend you place all your admin panel routes here.


<a name="published-views"></a>
### Published Views

After installation, you'll notice Backpack has added a new blade file in ```resources/views/vendor/backpack/ui/```:
    - ```inc/menu_items.blade.php```;

That file is used to show the contents of the menu. It's been published there so that you can easily modify its contents, by editing its HTML or adding dynamic content through [widgets](/docs/{{version}}/base-widgets).

<a name="unpublished-views"></a>
### Unpublished Views

You can change any blade file to your own needs. Determine what file you'd need to modify if you were to edit directly in the project's vendor folder (eg. `vendor/backpack/theme-tabler`), then go to ```resources/views/vendor/backpack/theme-tabler``` and create a file with the exact same name. Backpack will use this new file, instead of the one in the package.

For example:
- when using the CoreUI v2 theme, if you want to add an item to the top menu, you could just create a file called ```resources/views/vendor/backpack/theme-coreuiv2/inc/topbar_left_content.blade.php```; Backpack will now use this file's contents, instead of  ```vendor/backpack/theme-coreuiv2/resources/views/inc/topbar_left_content.php```;
- if you want to change the contents of the dashboard page, you can just create a file called `resources/views/vendor/backpack/ui/dashboard.blade.php` and Backpack will use that one, instead of the one in the package;

You can create blade views from scratch, or you can use our command to publish the view from the package and edit it to your liking:
```
php artisan backpack:publish ui/dashboard
```

Then inside the blade files, you can use either plain-old HTML or add dynamic content through [Backpack widgets](/docs/{{version}}/base-widgets).

Please note that it is NOT recommended to publish and override too many theme files. If you discover you're creating many of these files, you're basically creating a new theme. So we recommend you do just that. Please follow the docs to create a new "child theme".

<a name="folder-structure"></a>
### Folder Structure

If you'll take a look inside any Backpack package, you'll notice the packages are organised like a standard Laravel app. This is intentional. It should help you easily understand how the package works, and how you can overwrite/customize its functionality.

- ```app```
  - ```Console```
    - ```Commands```
  - ```Http```
    - ```Controllers```
    - ```Middleware```
    - ```Requests```
  - ```Notifications```
- ```config```
- ```resources```
  - ```lang```
  - ```views```
- ```routes```

<a name="authentication"></a>
## Authentication

When installed, Backpack provides a way for admins to login, recover password and register (don't worry, register is only enabled on ```localhost```). It does so with its own authentication controllers, models and middleware. If you have regular end-users (not admins), you can keep the _user_ authentication completely separate from _admin_ authentication. You can change which model, middleware classes Backpack uses, inside the ```config/backpack/base.php``` config file.

> **Backpack uses Laravel's default ```App\Models\User``` model**. This assumes you weren't already using this model, or the ```users``` table, for anything else. Or that you plan to use it for both users & admins. Otherwise, please read below.

<a name="using-a-different-user-model"></a>
### Using a Different User Model

If you want to use a different User model than ```App\Models\User``` or you've changed its location, you can tell Backpack to use _a different_ model in ```config/backpack/base.php```. Look for ```user_model_fqn```.


<a name="having-both-regular-users-and-admins"></a>
### Having Both Regular Users and Admins

If you already use the ```users``` table to store end-users (not admins), you will need a way to differentiate admins from regular users. Backpack does not force one method on you. Here are two methods we recommend, below:
- (A) adding an ```is_admin``` column to your ```users``` table, then changing the ```app/Http/Middleware/CheckIfAdmin::checkIfUserIsAdmin()``` method to test that attribute exists, and is true;
- (B) using the [PermissionManager](https://github.com/Laravel-Backpack/PermissionManager) extension - this will also add groups and permissions; Then tell Backpack to use _your new permission middleware_ to check if a logged in user is an admin, inside ```config/backpack/base.php```;

<a name="routes"></a>
### Routes

By default, all administration panel routes will be behind an ```/admin/``` prefix, and under an ```CheckIfAdmin``` middleware. You can change that inside ```config/backpack/base.php```.

Inside your _admin controllers or views_, please:
- use ```backpack_auth()``` instead of ```auth()```;
- use ```backpack_user()``` instead of ```auth()->user```;
- use ```backpack_url()``` instead of ```url()```;

This will make sure you're using the model, prefix & middleware that you've defined in ```config/backpack/base.php```. In case you decide to make changes there later, you won't need to change anything else. There are also [other backpack helpers you can use](#helpers).

<a name="admin-account"></a>
## Admin Account

When logged in, the admin can click on their name to go to their "account" page. There, they will be able to do a few basic operations: change name, email or password.

<a name="change-name-and-email"></a>
### Change Name and Email

Changing name and email is done inside ```Backpack\CRUD\app\Http\Controllers\Auth\MyAccountController```, using the ```getAccountInfoForm()``` and ```postAccountInfoForm()``` methods. If you want to change how this works, we recommend you create a ```routes/backpack/base.php``` file, copy-paste all Backpack\CRUD routes there and change whatever routes you need, to point to _your own controller_, where you can do whatever you want.

If you only want to add a few new inputs, you can do that by creating a file in ```resources/views/vendor/backpack/theme-xxx/my_account.blade.php``` that uses code from the same file in the theme, but adds the inputs you need. Remember to also make these fields ```$fillable``` in your User model.

<a name="change-password"></a>
### Change Password

Password changing is done inside ```Backpack\CRUD\app\Http\Controllers\Auth\MyAccountController```. If you want to change how this works, we recommend you create a ```routes/backpack/base.php``` file, copy-paste all Backpack\CRUD routes there and change whatever you need. You can then point the route to your own controller, where you can do whatever you want.


<a name="helpers"></a>
## Helpers

You can use these helpers anywhere in your app (models, views, controllers, requests, etc), except the config files, since the config files are loaded _before_ the helpers.

- **```backpack_url($path)```** - Use this helper instead of ```url()``` to generate paths with the admin prefix prepended.
- **```backpack_auth()```** - Returns the Auth facade, using the current Backpack guard. Basically a shorthand for ```\Auth::guard(backpack_guard_name())```. Use this instead of ```auth()``` inside your admin panel pages.
- **```backpack_middleware()```** - Returns the key for the admin middleware. Default is ```admin```.
- ```backpack_authentication_column()``` - Returns the username column. The Laravel and Backpack default is ```email```.
- ```backpack_users_have_email()``` - Tests that the ```email``` column exists on the users table and returns true/false.
- ```backpack_avatar($user)``` - Receives a user object and returns a path to an avatar image, according to the preferences in the config file (gravatar, placeholder or custom).
- ```backpack_guard_name()``` - Returns the guard used for Backpack authentication.
- ```backpack_user()``` - Returns the current Backpack user, if logged in. Basically a shorthand for ```\Auth::guard(backpack_guard_name())->user()```. Use this instead of ```auth()->user()``` inside your admin pages.

<a name="error-pages"></a>
## Error Pages

When installing Backpack, a few error views are published into ```resources/views/errors```, if you don't already have other files there. This is because Laravel does not provide error pages for all HTTP error codes. Having these files in your project will make sure that, if a user gets an HTTP error, at least it will look decent. Error pages are provided for the following error codes: ```400```, ```401```, ```403```, ```404```, ```405```, ```408```, ```429```, ```500```, ```503```.


<a name="custom-pages"></a>
## Custom Pages

To create a new page for your admin panel, you can follow the same process you would if you created a normal Laravel page (a Route, View and maybe a Controller). Just make sure that:
- the route file is under the `admin` middleware;
- the view extends one of our layout files (so that you get the design and the topbar+sidebar layout;

You can do exactly that by running `php artisan backpack:page PageName`, or manually by following the steps below:

### Add a custom page to your admin panel (dynamic page)

```php

# Step 1. Create the controller (we recommend you place it in your `app/Http/Controllers/Admin`)

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class PageController extends Controller
{
    public function example()
    {
        $data['something'] = 'Something';

        return view('admin.example_page', $data);
    }
}

# Step 2. Create a route for it (we recommend you place it in your `routes/backpack/custom.php` for simplicity)

Route::get('example-page', 'PageController@example');

# Step 3. Create the view (we recommend you place it in your `resources/views/admin`:

@extends(backpack_view('blank'))

@section('content')
    <h1>Example page</h1>
@endsection
```

### Add a custom page to your admin panel (static page)

Alternatively, if you are not getting any information from the database, and are just creating a quick static page, here's a quicker way:


```php

# Step 1. Create a route for it (we recommend you place it in your `routes/backpack/custom.php` for simplicity)

Route::get('example-page', function () { return view('admin.example_page'); });

# Step 2. Create that view (we recommend you place it in your `resources/views/admin`:

@extends(backpack_view('blank'))

@section('content')
    <h1>Example page</h1>
@endsection

```

