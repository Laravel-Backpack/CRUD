<?php

namespace Backpack\CRUD;

use Backpack\Basset\Facades\Basset;
use Backpack\CRUD\app\Http\Middleware\EnsureEmailVerification;
use Backpack\CRUD\app\Http\Middleware\ThrottlePasswordRecovery;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanel;
use Backpack\CRUD\app\Library\Database\DatabaseSchema;
use Backpack\CRUD\app\Library\Uploaders\Support\UploadersRepository;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Routing\Router;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\View\Compilers\BladeCompiler;

class BackpackServiceProvider extends ServiceProvider
{
    use Stats;

    protected $commands = [
        app\Console\Commands\Install::class,
        app\Console\Commands\AddMenuContent::class,
        app\Console\Commands\AddCustomRouteContent::class,
        app\Console\Commands\Version::class,
        app\Console\Commands\CreateUser::class,
        app\Console\Commands\PublishBackpackMiddleware::class,
        app\Console\Commands\PublishView::class,
        app\Console\Commands\Addons\RequireDevTools::class,
        app\Console\Commands\Addons\RequireEditableColumns::class,
        app\Console\Commands\Addons\RequirePro::class,
        app\Console\Commands\Themes\RequireThemeTabler::class,
        app\Console\Commands\Themes\RequireThemeCoreuiv2::class,
        app\Console\Commands\Themes\RequireThemeCoreuiv4::class,
        app\Console\Commands\Fix::class,
        app\Console\Commands\PublishHeaderMetas::class,
    ];

    // Indicates if loading of the provider is deferred.
    protected $defer = false;

    // Where the route file lives, both inside the package and in the app (if overwritten).
    public $routeFilePath = '/routes/backpack/base.php';

    // Where custom routes can be written, and will be registered by Backpack.
    public $customRoutesFilePath = '/routes/backpack/custom.php';

    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot(Router $router)
    {
        $this->loadTranslationsFrom(realpath(__DIR__.'/resources/lang'), 'backpack');
        $this->loadConfigs();
        $this->registerMiddlewareGroup($this->app->router);
        $this->setupRoutes($this->app->router);
        $this->setupCustomRoutes($this->app->router);
        $this->publishFiles();
        $this->sendUsageStats();

        Basset::addViewPath(realpath(__DIR__.'/resources/views'));

        Basset::map('bp-jquery', 'https://unpkg.com/jquery@3.6.1/dist/jquery.min.js', ['integrity' => 'sha384-i61gTtaoovXtAbKjo903+O55Jkn2+RtzHtvNez+yI49HAASvznhe9sZyjaSHTau9', 'crossorigin' => 'anonymous']);
        Basset::map('bp-popper-js', 'https://unpkg.com/@popperjs/core@2.11.6/dist/umd/popper.min.js', ['integrity' => 'sha384-oBqDVmMz9ATKxIep9tiCxS/Z9fNfEXiDAYTujMAeBAsjFuCZSmKbSSUnQlmh/jp3', 'crossorigin' => 'anonymous']);
        Basset::map('bp-summernote-css', 'https://unpkg.com/summernote@0.8.20/dist/summernote-lite.min.css', ['integrity' => 'sha384-vmPR5F5DxvnVZxuw9+hxaSj8MDX3rP49GZu/JvPS1qYD2xeg+0TGJUJ/H6e/HTkV', 'crossorigin' => 'anonymous']);
        Basset::map('bp-summernote-woof', 'https://unpkg.com/summernote@0.8.20/dist/font/summernote.woff2', ['integrity' => 'sha384-jin6VSG0kKkHctWc/DhVx2PL8YqVcnWvrAcqrTkLdi9evxi77MNjsgSUqbNGWijo', 'crossorigin' => 'anonymous']);
        Basset::map('bp-datatables-js', 'https://cdn.datatables.net/1.13.1/js/jquery.dataTables.min.js', ['integrity' => 'sha384-t11ZTRbO9om+k0pVXmc3c8SsIHonT3oUvoi3FxMm1c9DVQwl9VbTNv3+UjbUrI6Z', 'crossorigin' => 'anonymous']);
        Basset::map('bp-datatables-bootstrap-js', 'https://cdn.datatables.net/1.13.1/js/dataTables.bootstrap5.min.js', ['integrity' => 'sha384-bHpoWS7HfBjbqWmqPFVsEwT0EyCKgMw/hbKswCjYLSLS+TyPUAG51MTMIXgy/4Pl', 'crossorigin' => 'anonymous']);
        Basset::map('bp-datatables-bootstrap-css', 'https://cdn.datatables.net/1.13.1/css/dataTables.bootstrap5.min.css', ['integrity' => 'sha384-GADhaOJCr6lsUqdHJnYcH/QaARzVT92beGzAYxLTSoxUorHjQZci1FW+X9BqbnE3', 'crossorigin' => 'anonymous']);
        Basset::map('bp-datatables-responsive-js', 'https://cdn.datatables.net/responsive/2.4.0/js/dataTables.responsive.min.js', ['integrity' => 'sha384-yYqZ2Jue83rlzHS23Jp/xwZjRZ9KQCACGR5lhhWFtDIQeBMwAuav+irRqSKrucSP', 'crossorigin' => 'anonymous']);
        Basset::map('bp-datatables-responsive-css', 'https://cdn.datatables.net/responsive/2.4.0/css/responsive.dataTables.min.css', ['integrity' => 'sha384-cWgz6YKDgXz/mTomsnOIXd/1s0iivK+FhwVdmzN0ErdazMmt4RieKmZXMWdwScEm', 'crossorigin' => 'anonymous']);
        Basset::map('bp-datatables-fixedheader-js', 'https://cdn.datatables.net/fixedheader/3.3.1/js/dataTables.fixedHeader.min.js', ['integrity' => 'sha384-PeR7ate8YuUE8EYJp3d9zNrgSm9jFJL1b2Hrb8Za0RtVj6YMU8IZlyBwlRjiXq0i', 'crossorigin' => 'anonymous']);
        Basset::map('bp-datatables-fixedheader-css', 'https://cdn.datatables.net/fixedheader/3.3.1/css/fixedHeader.dataTables.min.css', ['integrity' => 'sha384-g0QDiyi3I9zPzO1O8mvkZd0/MWDKDs8Lk+pHQo3+kkTJEFTdzDCwjqIuoOG+yG0q', 'crossorigin' => 'anonymous']);
        Basset::map('bp-datatables-buttons-bs5-css', 'https://cdn.datatables.net/buttons/2.3.3/css/buttons.bootstrap5.min.css', ['integrity' => 'sha384-vLlNBaHuV6cqBlVjiJITSKkzOaeomPYdKV54KsyxIF88PDfOKqDRzlHSe5FVMe/4', 'crossorigin' => 'anonymous']);
        Basset::map('bp-datatables-dt-buttons-bs5-js', 'https://cdn.datatables.net/buttons/2.3.3/js/dataTables.buttons.min.js', ['integrity' => 'sha384-4UCu2Y40paen66DRD9HqMQTQDFzPOPrHQQ3Hj3il7NOYMVE9+8PKV9YqrNjOvB+G', 'crossorigin' => 'anonymous']);
        Basset::map('bp-datatables-buttons-bs5-js', 'https://cdn.datatables.net/buttons/2.3.3/js/buttons.bootstrap5.min.js', ['integrity' => 'sha384-ydNsKc6RC3ZCjn9sUZuBlPsIf/bBKmjNuYZZzBTvNag/JKDcIlRstd/eU+ZifNxM', 'crossorigin' => 'anonymous']);
        Basset::map('bp-datatables-jszip', 'https://cdnjs.cloudflare.com/ajax/libs/jszip/2.5.0/jszip.min.js', ['integrity' => 'sha384-yib/J8n+cev8VyEYY+4A3nSvnqCRhEkxUQhVFp8X+YuIXd0qhT3rTYXqel0zWzni', 'crossorigin' => 'anonymous']);
        Basset::map('bp-datatables-pdfmake', 'https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.18/pdfmake.min.js', ['integrity' => 'sha384-7B0GkWcyEmGJnsHkE0Z0cCpXaUS9i1tFM1/e0jAKoxA2YkI2XYaZdCOJo0kBBsS5', 'crossorigin' => 'anonymous']);
        Basset::map('bp-datatables-pdfmake-fonts', 'https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.18/vfs_fonts.js', ['integrity' => 'sha384-Zu5dDR1DhOW4Qpz55vgv84xMQSt0V2nX/rGj309pIyhOnc2KHssTcYszIjiYUjYL', 'crossorigin' => 'anonymous']);
        Basset::map('bp-datatables-html5-button', 'https://cdn.datatables.net/buttons/2.3.2/js/buttons.html5.min.js', ['integrity' => 'sha384-+KP6ruIqPlKDSU+EJ+oMlSh7cUCDWe2rHFaGVL4iHG4plXJaco2DetEl6yOrugqf', 'crossorigin' => 'anonymous']);
        Basset::map('bp-datatables-print-button', 'https://cdn.datatables.net/buttons/2.3.2/js/buttons.print.min.js', ['integrity' => 'sha384-5MVvNT5w7ht9uU89ZuYPDHs3Yap4hV/h/cPjiKBHdbS2pDKuY4ADmve/XwXN5oPQ', 'crossorigin' => 'anonymous']);
        Basset::map('bp-datatables-colvis-button', 'https://cdn.datatables.net/buttons/2.3.2/js/buttons.colVis.min.js', ['integrity' => 'sha384-iWhT+VbS/XoLnEHwlRhpwgeNPjEFIs88MypVWxe99IVGqXljIX4vdFLr3fAbRGHM', 'crossorigin' => 'anonymous']);
        Basset::map('bp-urijs', 'https://unpkg.com/urijs@1.19.11/src/URI.min.js', ['integrity' => 'sha384-VS1T95+I9NZNIjvlP0D/3HRlU1sKmoBzh71Pt08ckCdxEM2++7QEdbbp/3gi20js', 'crossorigin' => 'anonymous']);
        Basset::map('bp-highlight-js', 'https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.6.0/highlight.min.js', ['integrity' => 'sha512-gU7kztaQEl7SHJyraPfZLQCNnrKdaQi5ndOyt4L4UPL/FHDd/uB9Je6KDARIqwnNNE27hnqoWLBq+Kpe4iHfeQ==', 'crossorigin' => 'anonymous']);
        Basset::map('bp-highlight-css', 'https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.6.0/styles/base16/dracula.min.css', ['integrity' => 'sha512-GfRggx2Wc+POEPR0asMTNTyNug3rWJ9Jp4wxnHZ5VApMOUJRK4cEaRriXsx5tV1DakKHQWQ2noCbuzFiPJaYqA==', 'crossorigin' => 'anonymous']);
        Basset::map('bp-animate-css', 'https://unpkg.com/animate.css@4.1.1/animate.compat.css', ['integrity' => 'sha384-B6emdNLLuHwwngyCsBGzzo6MQPmSygQu5cG4lfUfdSxzj0FJVJZu+7GOmB8/NHpd', 'crossorigin' => 'anonymous']);
        Basset::map('bp-noty-css', 'https://unpkg.com/noty@3.2.0-beta-deprecated/lib/noty.css', ['integrity' => 'sha384-J/zBMo8aKN23nuimkvPbkLpGS1Uf1eCha++IoQfNVUpYCjvkuzLqO9nc4wizNAsv', 'crossorigin' => 'anonymous']);
        Basset::map('bp-lineawesome-css', 'https://cdnjs.cloudflare.com/ajax/libs/line-awesome/1.3.0/line-awesome/css/line-awesome.min.css', ['integrity' => 'sha512-vebUliqxrVkBy3gucMhClmyQP9On/HAWQdKDXRaAlb/FKuTbxkjPKUyqVOxAcGwFDka79eTF+YXwfke1h3/wfg==', 'crossorigin' => 'anonymous']);
        Basset::map('bp-lineawesome-regular-400', 'https://cdnjs.cloudflare.com/ajax/libs/line-awesome/1.3.0/line-awesome/fonts/la-regular-400.woff2', ['integrity' => 'sha384-2MTGaE1Ew+cNL5I7ilrE0E2sXLiuf90OL5DDceb5boJQOSLbVZCEHWpUPpvdOF4Z', 'crossorigin' => 'anonymous']);
        Basset::map('bp-lineawesome-solid-900', 'https://cdnjs.cloudflare.com/ajax/libs/line-awesome/1.3.0/line-awesome/fonts/la-solid-900.woff2', ['integrity' => 'sha384-6Y7zlEnVxM1wRcJx7qtpAK54L2QoP4CcAaosx1EKa3QOLvq0LjT7Fus7E9IDidgR', 'crossorigin' => 'anonymous']);
        Basset::map('bp-lineawesome-brands-400', 'https://cdnjs.cloudflare.com/ajax/libs/line-awesome/1.3.0/line-awesome/fonts/la-brands-400.woff2', ['integrity' => 'sha384-4EZZRKjFRNbc6sX193USv6Is1dJHSHzcGtijtyeUV2KQFRhVgpeWsrebTKu7RKEl', 'crossorigin' => 'anonymous']);
        Basset::map('bp-lineawesome-regular-400-woff', 'https://cdnjs.cloudflare.com/ajax/libs/line-awesome/1.3.0/line-awesome/fonts/la-regular-400.woff', ['integrity' => 'sha384-Pu6x4bfCQDro/gELWySVvZ04SlesuYt1QlQH2zvv1i43+7E6HkUIGGOixPLtP3XX', 'crossorigin' => 'anonymous']);
        Basset::map('bp-lineawesome-solid-900-woff', 'https://cdnjs.cloudflare.com/ajax/libs/line-awesome/1.3.0/line-awesome/fonts/la-solid-900.woff', ['integrity' => 'sha384-Ik+er7DjVwGt6ex3J05QtimAVRCq1JxCWf+t5n7fW80EAQS99uIIk+FJfIRy8sSN', 'crossorigin' => 'anonymous']);
        Basset::map('bp-lineawesome-brands-400-woff', 'https://cdnjs.cloudflare.com/ajax/libs/line-awesome/1.3.0/line-awesome/fonts/la-brands-400.woff', ['integrity' => 'sha384-gamww7YJnsFRoFVACJb6kLAoK2emK2ZIuoZgyvBM4kaEPEu3xDE/ivmXQmrYKp+l', 'crossorigin' => 'anonymous']);
        Basset::map('bp-lineawesome-regular-400-ttf', 'https://cdnjs.cloudflare.com/ajax/libs/line-awesome/1.3.0/line-awesome/fonts/la-regular-400.ttf', ['integrity' => 'sha384-1zzwN3v7UDtBGkTNjIFcNTy1U3dQTa5nix6OaV0CY5YXqxhlXDm0dum9bWOfpbqF', 'crossorigin' => 'anonymous']);
        Basset::map('bp-lineawesome-solid-900-ttf', 'https://cdnjs.cloudflare.com/ajax/libs/line-awesome/1.3.0/line-awesome/fonts/la-solid-900.ttf', ['integrity' => 'https://cdnjs.cloudflare.com/ajax/libs/line-awesome/1.3.0/line-awesome/fonts/la-solid-900.ttf', 'crossorigin' => 'anonymous']);
        Basset::map('bp-lineawesome-brands-400-ttf', 'https://cdnjs.cloudflare.com/ajax/libs/line-awesome/1.3.0/line-awesome/fonts/la-brands-400.ttf', ['integrity' => 'https://cdnjs.cloudflare.com/ajax/libs/line-awesome/1.3.0/line-awesome/fonts/la-brands-400.ttf', 'crossorigin' => 'anonymous']);
        Basset::map('bp-summernote-js', 'https://unpkg.com/summernote@0.8.20/dist/summernote-lite.min.js', ['integrity' => 'sha384-fq3mhgSZ+13XGKx7olcZUFWes9hDmAR3b/WnNLKH6fRFsHonf6CGG+Dj1wypCgLq', 'crossorigin' => 'anonymous']);
        Basset::map('bp-noty-js', 'https://unpkg.com/noty@3.2.0-beta-deprecated/lib/noty.min.js', ['integrity' => 'sha384-z7oxDqgQB0ThPzpmEjy9pcQT5oLRWvagLjZypnMIdKqBBLLvKNINZdifoEEPmrn1', 'crossorigin' => 'anonymous']);
        Basset::map('bp-sweet-alert-js', 'https://unpkg.com/sweetalert@2.1.2/dist/sweetalert.min.js', ['integrity' => 'sha384-RIQuldGV8mnjGdob13cay/K1AJa+LR7VKHqSXrrB5DPGryn4pMUXRLh92Ev8KlGF', 'crossorigin' => 'anonymous']);
        Basset::map('bp-bootstrap4-js', 'https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.2/js/bootstrap.min.js', ['integrity' => 'sha512-7rusk8kGPFynZWu26OKbTeI+QPoYchtxsmPeBqkHIEXJxeun4yJ4ISYe7C6sz9wdxeE1Gk3VxsIWgCZTc+vX3g==', 'crossorigin' => 'anonymous']);
        Basset::map('bp-bootstrap5-js', 'https://unpkg.com/bootstrap@5.2.3/dist/js/bootstrap.min.js', ['integrity' => 'sha384-cuYeSxntonz0PPNlHhBs68uyIAVpIIOZZ5JqeqvYYIcEL727kskC66kF92t6Xl2V', 'crossorigin' => 'anonymous']);

        foreach (config('backpack.ui.styles', []) as $style) {
            if (is_array($style)) {
                foreach ($style as $file) {
                    Basset::map($file);
                }
            } else {
                Basset::map($style);
            }
        }

        foreach (config('backpack.ui.scripts', []) as $script) {
            if (is_array($script)) {
                foreach ($script as $file) {
                    Basset::map($file);
                }
            } else {
                Basset::map($script);
            }
        }
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        // load the macros
        include_once __DIR__.'/macros.php';

        $this->loadViewsWithFallbacks('crud');
        $this->loadViewsWithFallbacks('ui', 'backpack.ui');
        $this->loadViewNamespace('widgets', 'backpack.ui::widgets');
        $this->loadViewComponents();

        $this->registerBackpackErrorViews();

        // Bind the CrudPanel object to Laravel's service container
        $this->app->scoped('crud', function ($app) {
            return new CrudPanel();
        });

        $this->app->scoped('DatabaseSchema', function ($app) {
            return new DatabaseSchema();
        });

        $this->app->singleton('BackpackViewNamespaces', function ($app) {
            return new ViewNamespaces();
        });

        // Bind the widgets collection object to Laravel's service container
        $this->app->singleton('widgets', function ($app) {
            return new Collection();
        });

        $this->app->scoped('UploadersRepository', function ($app) {
            return new UploadersRepository();
        });

        // register the helper functions
        $this->loadHelpers();

        // register the artisan commands
        $this->commands($this->commands);
    }

    public function registerMiddlewareGroup(Router $router)
    {
        $middleware_key = config('backpack.base.middleware_key');
        $middleware_class = config('backpack.base.middleware_class');

        if (! is_array($middleware_class)) {
            $router->pushMiddlewareToGroup($middleware_key, $middleware_class);

            return;
        }

        foreach ($middleware_class as $middleware_class) {
            $router->pushMiddlewareToGroup($middleware_key, $middleware_class);
        }

        // register internal backpack middleware for throttling the password recovery functionality
        // but only if functionality is enabled by developer in config
        if (config('backpack.base.setup_password_recovery_routes')) {
            $router->aliasMiddleware('backpack.throttle.password.recovery', ThrottlePasswordRecovery::class);
        }

        // register the email verification middleware, if the developer enabled it in the config.
        if (config('backpack.base.setup_email_verification_routes', false) && config('backpack.base.setup_email_verification_middleware', true)) {
            $router->pushMiddlewareToGroup($middleware_key, EnsureEmailVerification::class);
        }
    }

    public function publishFiles()
    {
        $backpack_views = [__DIR__.'/resources/views' => resource_path('views/vendor/backpack')];
        $backpack_lang_files = [__DIR__.'/resources/lang' => app()->langPath().'/vendor/backpack'];
        $backpack_config_files = [__DIR__.'/config' => config_path()];

        // sidebar content views, which are the only views most people need to overwrite
        $backpack_menu_contents_view = [
            __DIR__.'/resources/views/ui/inc/menu_items.blade.php' => resource_path('views/vendor/backpack/ui/inc/menu_items.blade.php'),
        ];
        $backpack_custom_routes_file = [__DIR__.$this->customRoutesFilePath => base_path($this->customRoutesFilePath)];

        // calculate the path from current directory to get the vendor path
        $vendorPath = dirname(__DIR__, 3);
        $gravatar_assets = [$vendorPath.'/creativeorange/gravatar/config' => config_path()];

        // establish the minimum amount of files that need to be published, for Backpack to work; there are the files that will be published by the install command
        $minimum = array_merge(
            // $backpack_views,
            // $backpack_lang_files,
            $backpack_config_files,
            $backpack_menu_contents_view,
            $backpack_custom_routes_file,
            $gravatar_assets
        );

        // register all possible publish commands and assign tags to each
        $this->publishes($backpack_config_files, 'config');
        $this->publishes($backpack_lang_files, 'lang');
        $this->publishes($backpack_views, 'views');
        $this->publishes($backpack_menu_contents_view, 'menu_contents');
        $this->publishes($backpack_custom_routes_file, 'custom_routes');
        $this->publishes($gravatar_assets, 'gravatar');
        $this->publishes($minimum, 'minimum');
    }

    /**
     * Define the routes for the application.
     *
     * @param  Router  $router
     * @return void
     */
    public function setupRoutes(Router $router)
    {
        // by default, use the routes file provided in vendor
        $routeFilePathInUse = __DIR__.$this->routeFilePath;

        // but if there's a file with the same name in routes/backpack, use that one
        if (file_exists(base_path().$this->routeFilePath)) {
            $routeFilePathInUse = base_path().$this->routeFilePath;
        }

        $this->loadRoutesFrom($routeFilePathInUse);
    }

    /**
     * Load custom routes file.
     *
     * @param  Router  $router
     * @return void
     */
    public function setupCustomRoutes(Router $router)
    {
        // if the custom routes file is published, register its routes
        if (file_exists(base_path().$this->customRoutesFilePath)) {
            $this->loadRoutesFrom(base_path().$this->customRoutesFilePath);
        }
    }

    public function loadViewNamespace($domain, $namespace)
    {
        ViewNamespaces::addFor($domain, $namespace);
    }

    public function loadViewsWithFallbacks($dir, $namespace = null)
    {
        $customFolder = resource_path('views/vendor/backpack/'.$dir);
        $vendorFolder = realpath(__DIR__.'/resources/views/'.$dir);
        $namespace = $namespace ?? $dir;

        // first the published/overwritten views (in case they have any changes)
        if (file_exists($customFolder)) {
            $this->loadViewsFrom($customFolder, $namespace);
        }
        // then the stock views that come with the package, in case a published view might be missing
        $this->loadViewsFrom($vendorFolder, $namespace);
    }

    protected function mergeConfigsFromDirectory($dir)
    {
        $configs = scandir(__DIR__."/config/backpack/$dir/");
        $configs = array_diff($configs, ['.', '..']);

        if (! count($configs)) {
            return;
        }

        foreach ($configs as $configFile) {
            $this->mergeConfigFrom(
                __DIR__."/config/backpack/$dir/$configFile",
                "backpack.$dir.".substr($configFile, 0, strrpos($configFile, '.'))
            );
        }
    }

    public function loadConfigs()
    {
        // use the vendor configuration file as fallback
        $this->mergeConfigFrom(__DIR__.'/config/backpack/crud.php', 'backpack.crud');
        $this->mergeConfigFrom(__DIR__.'/config/backpack/base.php', 'backpack.base');
        $this->mergeConfigFrom(__DIR__.'/config/backpack/ui.php', 'backpack.ui');
        $this->mergeConfigsFromDirectory('operations');

        // add the root disk to filesystem configuration
        app()->config['filesystems.disks.'.config('backpack.base.root_disk_name')] = [
            'driver' => 'local',
            'root' => base_path(),
        ];

        /*
         * Backpack login differs from the standard Laravel login.
         * As such, Backpack uses its own authentication provider, password broker and guard.
         *
         * THe process below adds those configuration values on top of whatever is in config/auth.php.
         * Developers can overwrite the backpack provider, password broker or guard by adding a
         * provider/broker/guard with the "backpack" name inside their config/auth.php file.
         * Or they can use another provider/broker/guard entirely, by changing the corresponding
         * value inside config/backpack/base.php
         */

        // add the backpack_users authentication provider to the configuration
        app()->config['auth.providers'] = app()->config['auth.providers'] +
            [
                'backpack' => [
                    'driver' => 'eloquent',
                    'model' => config('backpack.base.user_model_fqn'),
                ],
            ];

        // add the backpack_users password broker to the configuration
        $laravelAuthPasswordBrokers = app()->config['auth.passwords'];
        $laravelFirstPasswordBroker = is_array($laravelAuthPasswordBrokers) && current($laravelAuthPasswordBrokers) ?
                                        current($laravelAuthPasswordBrokers)['table'] :
                                        '';

        $backpackPasswordBrokerTable = config('backpack.base.password_resets_table') ??
                                        config('auth.passwords.users.table') ??
                                        $laravelFirstPasswordBroker;

        app()->config['auth.passwords'] = $laravelAuthPasswordBrokers +
        [
            'backpack' => [
                'provider' => 'backpack',
                'table' => $backpackPasswordBrokerTable,
                'expire' => config('backpack.base.password_recovery_token_expiration', 60),
                'throttle' => config('backpack.base.password_recovery_throttle_notifications'),
            ],
        ];

        // add the backpack_users guard to the configuration
        app()->config['auth.guards'] = app()->config['auth.guards'] +
            [
                'backpack' => [
                    'driver' => 'session',
                    'provider' => 'backpack',
                ],
            ];
    }

    public function loadViewComponents()
    {
        $this->app->afterResolving(BladeCompiler::class, function () {
            Blade::componentNamespace('Backpack\\CRUD\\app\\View\\Components', 'backpack');
        });
    }

    /**
     * Load the Backpack helper methods, for convenience.
     */
    public function loadHelpers()
    {
        require_once __DIR__.'/helpers.php';
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['crud', 'widgets', 'BackpackViewNamespaces', 'DatabaseSchema', 'UploadersRepository'];
    }

    private function registerBackpackErrorViews()
    {
        // register the backpack error when the exception handler is resolved from the container
        $this->callAfterResolving(ExceptionHandler::class, function ($handler) {
            if (! Str::startsWith(request()->path(), config('backpack.base.route_prefix'))) {
                return;
            }

            // parse the namespaces set in config
            [$themeNamespace, $themeFallbackNamespace] = (function () {
                $themeNamespace = config('backpack.ui.view_namespace');
                $themeFallbackNamespace = config('backpack.ui.view_namespace_fallback');

                return [
                    Str::endsWith($themeNamespace, '::') ? substr($themeNamespace, 0, -2) : substr($themeNamespace, 0, -1),
                    Str::endsWith($themeFallbackNamespace, '::') ? substr($themeFallbackNamespace, 0, -2) : substr($themeFallbackNamespace, 0, -1),
                ];
            })();

            $viewFinderHints = app('view')->getFinder()->getHints();

            // here we are going to generate the paths array containing:
            // - theme paths
            // - fallback theme paths
            // - ui path
            $themeErrorPaths = $viewFinderHints[$themeNamespace] ?? [];
            $themeErrorPaths = $themeNamespace === $themeFallbackNamespace ? $themeErrorPaths :
                array_merge($viewFinderHints[$themeFallbackNamespace] ?? [], $themeErrorPaths);
            $uiErrorPaths = [base_path('vendor/backpack/crud/src/resources/views/ui')];
            $themeErrorPaths = array_merge($themeErrorPaths, $uiErrorPaths);

            // merge the paths array with the view.paths defined in the application
            app('config')->set('view.paths', array_merge($themeErrorPaths, config('view.paths', [])));
        });
    }
}
