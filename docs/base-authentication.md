## Authentication

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

To completely customize the auth routes, you can change both ```setup_auth_routes``` and ```setup_dashboard_routes``` to ```false```. This means Backpack\Base won't register any routes any more, so you'll have to manually register them in your route file. Here's what you can use to get started:
```php
Route::group(['middleware' => 'web', 'prefix' => config('backpack.base.route_prefix'), 'namespace' => 'Backpack\Base\app\Http\Controllers'], function () {
    Route::auth();
    Route::get('logout', 'Auth\LoginController@logout');
    Route::get('dashboard', 'AdminController@dashboard');
    Route::get('/', 'AdminController@redirect');
});
```

### Use separate login/register forms for users and admins

This is a default in Backpack v4.

Backpack's authentication uses a completely separate authentication driver, provider, guard and password broker. They're all named ```backpack```, and registered in the vendor folder, invisible to you.

If you need a separate login for user, just go ahead and create it. [Add the Laravel authentication, like instructed in the Laravel documentation](https://laravel.com/docs/authentication#authentication-quickstart): ```php artisan make:auth```. You'll then have:
- the user login at ```/login``` -> using the AuthenticationController Laravel provides
- the admin login at ```/admin/login``` -> using the AuthenticationControllers Backpack provides

The user login will be using Laravel's default authentication driver, provider, guard and password broker, from ```config/auth.php```.

Backpack's authentication driver, provider, guard and password broker can be overwritten by creating a driver/provider/guard/broker with the ```backpack``` name inside your ```config/auth.php```. If one named ```backpack``` exists there, Backpack will use that instead.

### Overwrite Backpack authentication driver, provider, guard or password broker

Backpack's authentication uses a completely separate authentication driver, provider, guard and password broker. Backpack adds them to what's defined in ```config/auth.php``` on runtime, and they're all named ```backpack```.

To change a setting in how Backpack's driver/provider/guard or password broker works, create a driver/provider/guard/broker with the ```backpack``` name inside your ```config/auth.php```. If one named ```backpack``` exists there, Backpack will use that instead.

### Use separate sessions for admin&user authentication

This is a default in Backpack v4.

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

### Use your own User model instead of App\User

By default, authentication and everything else inside Backpack is done using the ```App\User``` model. If you change the location of ```App\User```, or want to use a different User model for whatever other reason, you can do so by changing ```user_model_fqn``` in ```config/backpack/base.php``` to your new class.

### Use your own profile image (avatar)

By default, Backpack will use Gravatar to show the profile image for the currently logged in backpack user. To change this, you can use the option in ```config/backpack/base.php```:
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

**Step 3.** Add the actual inputs to your HTML. You can overwrite the register view by adding this method to the same RegisterController:

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

You can create a new migration using `php artisan make:migration add_email_verified_at_column_to_users --table=users`, then use the code below:

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
