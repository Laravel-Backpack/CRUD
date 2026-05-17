<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Look & feel customizations
    |--------------------------------------------------------------------------
    |
    | Make it yours.
    |
    */

    // Date & Datetime Format Syntax: https://carbon.nesbot.com/docs/#api-localization
    'default_date_format' => 'D MMM YYYY',
    'default_datetime_format' => 'D MMM YYYY, HH:mm',

    // Direction, according to language
    // (left-to-right vs right-to-left)
    'html_direction' => 'ltr',

    // ----
    // HEAD
    // ----

    // Project name. Shown in the window title.
    'project_name' => 'Backpack Admin Panel',

    // When clicking on the admin panel's top-left logo/name,
    // where should the user be redirected?
    // The string below will be passed through the url() helper.
    // - default: '' (project root)
    // - alternative: 'admin' (the admin's dashboard)
    'home_link' => '',

    // Content of the HTML meta robots tag to prevent indexing and link following
    'meta_robots_content' => 'noindex, nofollow',

    // ---------
    // DASHBOARD
    // ---------

    // Show "Getting Started with Backpack" info block?
    'show_getting_started' => env('APP_ENV') == 'local',

    // ------
    // STYLES
    // ------

    // CSS files that are loaded in all pages, using Laravel's asset() helper
    'styles' => [
        'packages/backpack/base/css/bundle.css', // has primary color electric purple (backpack default)
        // 'packages/backpack/base/css/blue-bundle.css', // has primary color blue

        // Here's what's inside the bundle:
        // 'packages/@digitallyhappy/backstrap/css/style.min.css',
        // 'packages/animate.css/animate.min.css',
        // 'packages/noty/noty.css',

        // Load the fonts separately (so that you can replace them at will):
        'packages/source-sans-pro/source-sans-pro.css',
        'packages/line-awesome/css/line-awesome.min.css',

        // Example (the fonts above, loaded from CDN instead)
        // 'https://maxcdn.icons8.com/fonts/line-awesome/1.1/css/line-awesome-font-awesome.min.css',
        // 'https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic',

        // Example (load font-awesome instead of line-awesome):
        // 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.2/css/all.min.css',
    ],

    // CSS files that are loaded in all pages, using Laravel's mix() helper
    'mix_styles' => [ // file_path => manifest_directory_path
        // 'css/app.css' => '',
    ],

    // CSS files that are loaded in all pages, using Laravel's @vite() helper
    // Please note that support for Vite was added in Laravel 9.19. Earlier versions are not able to use this feature.
    'vite_styles' => [ // resource file_path
        // 'resources/css/app.css',
    ],

    // ------
    // HEADER
    // ------

    // Menu logo. You can replace this with an <img> tag if you have a logo.
    'project_logo' => '<b>Back</b>pack',

    // Show / hide breadcrumbs on admin panel pages.
    'breadcrumbs' => true,

    // Horizontal navbar classes. Helps make the admin panel look similar to your project's design.
    'header_class' => 'app-header bg-light border-0 navbar',
    // For background colors use: bg-dark, bg-primary, bg-secondary, bg-danger, bg-warning, bg-success, bg-info, bg-blue, bg-light-blue, bg-indigo, bg-purple, bg-pink, bg-red, bg-orange, bg-yellow, bg-green, bg-teal, bg-cyan, bg-white
    // For links to be visible on different background colors use: "navbar-dark", "navbar-light", "navbar-color"

    // ----
    // BODY
    // ----

    // Body element classes.
    'body_class' => 'app aside-menu-fixed sidebar-lg-show',
    // Try sidebar-hidden, sidebar-fixed, sidebar-compact, sidebar-lg-show

    // Sidebar element classes.
    'sidebar_class' => 'sidebar sidebar-pills bg-light',
    // Remove "sidebar-transparent" for standard sidebar look
    // Try "sidebar-light" or "sidebar-dark" for dark/light links
    // You can also add a background class like bg-dark, bg-primary, bg-secondary, bg-danger, bg-warning, bg-success, bg-info, bg-blue, bg-light-blue, bg-indigo, bg-purple, bg-pink, bg-red, bg-orange, bg-yellow, bg-green, bg-teal, bg-cyan

    // ------
    // FOOTER
    // ------

    // Footer element classes.
    'footer_class' => 'app-footer d-print-none',
    // hide it with d-none
    // change background color with bg-dark, bg-primary, bg-secondary, bg-danger, bg-warning, bg-success, bg-info, bg-blue, bg-light-blue, bg-indigo, bg-purple, bg-pink, bg-red, bg-orange, bg-yellow, bg-green, bg-teal, bg-cyan, bg-white

    // Developer or company name. Shown in footer.
    'developer_name' => 'Cristian Tabacitu',

    // Developer website. Link in footer. Type false if you want to hide it.
    'developer_link' => 'http://tabacitu.ro',

    // Show powered by Laravel Backpack in the footer? true/false
    'show_powered_by' => true,

    // -------
    // SCRIPTS
    // -------

    // JS files that are loaded in all pages, using Laravel's asset() helper
    'scripts' => [
        // Backstrap includes jQuery, Bootstrap, CoreUI, PNotify, Popper
        'packages/backpack/base/js/bundle.js',

        // examples (everything inside the bundle, loaded from CDN)
        // 'https://code.jquery.com/jquery-3.4.1.min.js',
        // 'https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js',
        // 'https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js',
        // 'https://unpkg.com/@coreui/coreui@2.1.16/dist/js/coreui.min.js',
        // 'https://cdnjs.cloudflare.com/ajax/libs/pace/1.0.2/pace.min.js',
        // 'https://unpkg.com/sweetalert/dist/sweetalert.min.js',
        // 'https://cdnjs.cloudflare.com/ajax/libs/noty/3.1.4/noty.min.js'

        // examples (VueJS or React)
        // 'https://unpkg.com/vue@2.4.4/dist/vue.min.js',
        // 'https://unpkg.com/react@16/umd/react.production.min.js',
        // 'https://unpkg.com/react-dom@16/umd/react-dom.production.min.js',
    ],

    // JS files that are loaded in all pages, using Laravel's mix() helper
    'mix_scripts' => [ // file_path => manifest_directory_path
        // 'js/app.js' => '',
    ],

    // JS files that are loaded in all pages, using Laravel's @vite() helper
    'vite_scripts' => [ // resource file_path
        // 'resources/js/app.js',
    ],

    // -------------
    // CACHE-BUSTING
    // -------------

    // All JS and CSS assets defined above have this string appended as query string (?v=string).
    // If you want to manually trigger cachebusting for all styles and scripts,
    // append or prepend something to the string below, so that it's different.
    'cachebusting_string' => \PackageVersions\Versions::getVersion('backpack/crud'),

    /*
    |--------------------------------------------------------------------------
    | Registration Open
    |--------------------------------------------------------------------------
    |
    | Choose whether new users/admins are allowed to register.
    | This will show the Register button on the login page and allow access to the
    | Register functions in AuthController.
    |
    | By default the registration is open only on localhost.
    */

    'registration_open' => env('BACKPACK_REGISTRATION_OPEN', env('APP_ENV') === 'local'),

    /*
    |--------------------------------------------------------------------------
    | Routing
    |--------------------------------------------------------------------------
    */

    // The prefix used in all base routes (the 'admin' in admin/dashboard)
    // You can make sure all your URLs use this prefix by using the backpack_url() helper instead of url()
    'route_prefix' => 'admin',

    // The web middleware (group) used in all base & CRUD routes
    // If you've modified your "web" middleware group (ex: removed sessions), you can use a different
    // route group, that has all the the middleware listed below in the comments.
    'web_middleware' => 'web',
    // Or you can comment the above, and uncomment the complete list below.
    // 'web_middleware' => [
    //     \App\Http\Middleware\EncryptCookies::class,
    //     \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
    //     \Illuminate\Session\Middleware\StartSession::class,
    //     \Illuminate\View\Middleware\ShareErrorsFromSession::class,
    //     \App\Http\Middleware\VerifyCsrfToken::class,
    // ],

    // Set this to false if you would like to use your own AuthController and PasswordController
    // (you then need to setup your auth routes manually in your routes.php file)
    // Warning: if you disable this, the password recovery routes (below) will be disabled too!
    'setup_auth_routes' => true,

    // Set this to false if you would like to skip adding the dashboard routes
    // (you then need to overwrite the login route on your AuthController)
    'setup_dashboard_routes' => true,

    // Set this to false if you would like to skip adding "my account" routes
    // (you then need to manually define the routes in your web.php)
    'setup_my_account_routes' => true,

    // Set this to false if you would like to skip adding the password recovery routes
    // (you then need to manually define the routes in your web.php)
    'setup_password_recovery_routes' => true,

    /*
    |--------------------------------------------------------------------------
    | Security
    |--------------------------------------------------------------------------
    */

    // Backpack will prevent visitors from requesting password recovery too many times
    // for a certain email, to make sure they cannot be spammed that way.
    // How many seconds should a visitor wait, after they've requested a
    // password reset, before they can try again for the same email?
    'password_recovery_throttle_notifications' => 600, // time in seconds

    // How much time should the token sent to the user email be considered valid?
    // After this time expires, user needs to request a new reset token.
    'password_recovery_token_expiration' => 60, // time in minutes

    // Backpack will prevent an IP from trying to reset the password too many times,
    // so that a malicious actor cannot try too many emails, too see if they have
    // accounts or to increase the AWS/SendGrid/etc bill.
    //
    // How many times in any given time period should the user be allowed to
    // attempt a password reset? Take into account that user might wrongly
    // type an email at first, so at least allow one more try.
    // Defaults to 3,10 - 3 times in 10 minutes.
    'password_recovery_throttle_access' => '3,10',

    /*
    |--------------------------------------------------------------------------
    | Authentication
    |--------------------------------------------------------------------------
    */

    // Fully qualified namespace of the User model
    'user_model_fqn' => config('auth.providers.users.model'),
    // 'user_model_fqn' => App\User::class, // works on Laravel <= 7
    // 'user_model_fqn' => App\Models\User::class, // works on Laravel >= 8

    // The classes for the middleware to check if the visitor is an admin
    // Can be a single class or an array of classes
    'middleware_class' => [
        App\Http\Middleware\CheckIfAdmin::class,
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
        \Backpack\CRUD\app\Http\Middleware\AuthenticateSession::class,
        // \Backpack\CRUD\app\Http\Middleware\UseBackpackAuthGuardInsteadOfDefaultAuthGuard::class,
    ],

    // Alias for that middleware
    'middleware_key' => 'admin',
    // Note: It's recommended to use the backpack_middleware() helper everywhere, which pulls this key for you.

    // Username column for authentication
    // The Backpack default is the same as the Laravel default (email)
    // If you need to switch to username, you also need to create that column in your db
    'authentication_column' => 'email',
    'authentication_column_name' => 'Email',

    // Backpack assumes that your "database email column" for operations like Login and Register is called "email".
    // If your database email column have a different name, you can configure it here. Eg: `user_mail`
    'email_column' => 'email',

    // The guard that protects the Backpack admin panel.
    // If null, the config.auth.defaults.guard value will be used.
    'guard' => 'backpack',

    // The password reset configuration for Backpack.
    // If null, the config.auth.defaults.passwords value will be used.
    'passwords' => 'backpack',

    // What kind of avatar will you like to show to the user?
    // Default: gravatar (automatically use the gravatar for their email)
    // Other options:
    // - null (generic image with their first letter)
    // - example_method_name (specify the method on the User model that returns the URL)
    'avatar_type' => 'gravatar',

    // Gravatar fallback options are 'identicon', 'monsterid', 'wavatar', 'retro', 'robohash', 'blank'
    // 'blank' will keep the generic image with the user first letter
    'gravatar_fallback' => 'blank',

    /*
    |--------------------------------------------------------------------------
    | Theme (User Interface)
    |--------------------------------------------------------------------------
    */
    // Change the view namespace in order to load a different theme than the one Backpack provides.
    // You can create child themes yourself, by creating a view folder anywhere in your resources/views
    // and choosing that view_namespace instead of the default one. Backpack will load a file from there
    // if it exists, otherwise it will load it from the default namespace ("backpack::").

    'view_namespace' => 'backpack::',

    // EXAMPLE: if you create a new folder in resources/views/vendor/myname/mypackage,
    // your namespace would be the one below. IMPORTANT: in this case the namespace ends with a dot.
    // 'view_namespace' => 'vendor.myname.mypackage.',

    // Tell Backpack to look in more places for component views (like widgets)
    'component_view_namespaces' => [
        'widgets' => [
            'backpack::widgets', // falls back to 'resources/views/vendor/backpack/base/widgets'
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | File System
    |--------------------------------------------------------------------------
    */

    // Backpack\Base sets up its own filesystem disk, just like you would by
    // adding an entry to your config/filesystems.php. It points to the root
    // of your project and it's used throughout all Backpack packages.
    //
    // You can rename this disk here. Default: root
    'root_disk_name' => 'root',

    /*
    |--------------------------------------------------------------------------
    | Backpack Token Username
    |--------------------------------------------------------------------------
    |
    | If you have access to closed-source Backpack add-ons, please provide
    | your token username here, if you're getting yellow alerts on your
    | admin panel's pages. Normally this is not needed, it is
    | preferred to add this as an environment variable
    | (most likely in your .env file).
    |
    | More info and payment form on:
    | https://www.backpackforlaravel.com
    |
    */

    'token_username' => env('BACKPACK_TOKEN_USERNAME', false),
];
