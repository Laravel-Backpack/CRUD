<?php

namespace Backpack\CRUD;

use Backpack\CRUD\app\Library\CrudPanel\CrudPanel;
use Illuminate\Routing\Router;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class BackpackServiceProvider extends ServiceProvider
{
    use Stats, LicenseCheck;

    protected $commands = [
        \Backpack\CRUD\app\Console\Commands\Install::class,
        \Backpack\CRUD\app\Console\Commands\AddSidebarContent::class,
        \Backpack\CRUD\app\Console\Commands\AddCustomRouteContent::class,
        \Backpack\CRUD\app\Console\Commands\Version::class,
        \Backpack\CRUD\app\Console\Commands\CreateUser::class,
        \Backpack\CRUD\app\Console\Commands\PublishBackpackMiddleware::class,
        \Backpack\CRUD\app\Console\Commands\PublishView::class,
        \Backpack\CRUD\app\Console\Commands\RequireDevTools::class,
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
    public function boot(\Illuminate\Routing\Router $router)
    {
        $this->loadViewsWithFallbacks();
        $this->loadTranslationsFrom(realpath(__DIR__.'/resources/lang'), 'backpack');
        $this->loadConfigs();
        $this->registerMiddlewareGroup($this->app->router);
        $this->setupRoutes($this->app->router);
        $this->setupCustomRoutes($this->app->router);
        $this->publishFiles();
        $this->checkLicenseCodeExists();
        $this->sendUsageStats();
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        // Bind the CrudPanel object to Laravel's service container
        $this->app->singleton('crud', function ($app) {
            return new CrudPanel($app);
        });

        // Bind the widgets collection object to Laravel's service container
        $this->app->singleton('widgets', function ($app) {
            return new Collection();
        });

        // load a macro for Route,
        // helps developers load all routes for a CRUD resource in one line
        if (! Route::hasMacro('crud')) {
            $this->addRouteMacro();
        }

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
    }

    public function publishFiles()
    {
        $error_views = [__DIR__.'/resources/error_views' => resource_path('views/errors')];
        $backpack_views = [__DIR__.'/resources/views' => resource_path('views/vendor/backpack')];
        $backpack_public_assets = [__DIR__.'/public' => public_path()];
        $backpack_lang_files = [__DIR__.'/resources/lang' => resource_path('lang/vendor/backpack')];
        $backpack_config_files = [__DIR__.'/config' => config_path()];

        // sidebar content views, which are the only views most people need to overwrite
        $backpack_menu_contents_view = [
            __DIR__.'/resources/views/base/inc/sidebar_content.blade.php'      => resource_path('views/vendor/backpack/base/inc/sidebar_content.blade.php'),
            __DIR__.'/resources/views/base/inc/topbar_left_content.blade.php'  => resource_path('views/vendor/backpack/base/inc/topbar_left_content.blade.php'),
            __DIR__.'/resources/views/base/inc/topbar_right_content.blade.php' => resource_path('views/vendor/backpack/base/inc/topbar_right_content.blade.php'),
        ];
        $backpack_custom_routes_file = [__DIR__.$this->customRoutesFilePath => base_path($this->customRoutesFilePath)];

        // calculate the path from current directory to get the vendor path
        $vendorPath = dirname(__DIR__, 3);
        $gravatar_assets = [$vendorPath.'/creativeorange/gravatar/config' => config_path()];

        // establish the minimum amount of files that need to be published, for Backpack to work; there are the files that will be published by the install command
        $minimum = array_merge(
            // $backpack_views,
            // $backpack_lang_files,
            $error_views,
            $backpack_public_assets,
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
        $this->publishes($error_views, 'errors');
        $this->publishes($backpack_public_assets, 'public');
        $this->publishes($backpack_custom_routes_file, 'custom_routes');
        $this->publishes($gravatar_assets, 'gravatar');
        $this->publishes($minimum, 'minimum');
    }

    /**
     * Define the routes for the application.
     *
     * @param \Illuminate\Routing\Router $router
     *
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
     * @param \Illuminate\Routing\Router $router
     *
     * @return void
     */
    public function setupCustomRoutes(Router $router)
    {
        // if the custom routes file is published, register its routes
        if (file_exists(base_path().$this->customRoutesFilePath)) {
            $this->loadRoutesFrom(base_path().$this->customRoutesFilePath);
        }
    }

    /**
     * The route macro allows developers to generate the routes for a CrudController,
     * for all operations, using a simple syntax: Route::crud().
     *
     * It will go to the given CrudController and get the setupRoutes() method on it.
     */
    private function addRouteMacro()
    {
        Route::macro('crud', function ($name, $controller) {
            // put together the route name prefix,
            // as passed to the Route::group() statements
            $routeName = '';
            if ($this->hasGroupStack()) {
                foreach ($this->getGroupStack() as $key => $groupStack) {
                    if (isset($groupStack['name'])) {
                        if (is_array($groupStack['name'])) {
                            $routeName = implode('', $groupStack['name']);
                        } else {
                            $routeName = $groupStack['name'];
                        }
                    }
                }
            }
            // add the name of the current entity to the route name prefix
            // the result will be the current route name (not ending in dot)
            $routeName .= $name;

            // get an instance of the controller
            if ($this->hasGroupStack()) {
                $groupStack = $this->getGroupStack();
                $groupNamespace = $groupStack && isset(end($groupStack)['namespace']) ? end($groupStack)['namespace'].'\\' : '';
            } else {
                $groupNamespace = '';
            }
            $namespacedController = $groupNamespace.$controller;
            $controllerInstance = App::make($namespacedController);

            return $controllerInstance->setupRoutes($name, $routeName, $controller);
        });
    }

    public function loadViewsWithFallbacks()
    {
        $customBaseFolder = resource_path('views/vendor/backpack/base');
        $customCrudFolder = resource_path('views/vendor/backpack/crud');

        // - first the published/overwritten views (in case they have any changes)
        if (file_exists($customBaseFolder)) {
            $this->loadViewsFrom($customBaseFolder, 'backpack');
        }
        if (file_exists($customCrudFolder)) {
            $this->loadViewsFrom($customCrudFolder, 'crud');
        }
        // - then the stock views that come with the package, in case a published view might be missing
        $this->loadViewsFrom(realpath(__DIR__.'/resources/views/base'), 'backpack');
        $this->loadViewsFrom(realpath(__DIR__.'/resources/views/crud'), 'crud');
    }

    public function loadConfigs()
    {
        // use the vendor configuration file as fallback
        $this->mergeConfigFrom(__DIR__.'/config/backpack/crud.php', 'backpack.crud');
        $this->mergeConfigFrom(__DIR__.'/config/backpack/base.php', 'backpack.base');

        // add the root disk to filesystem configuration
        app()->config['filesystems.disks.'.config('backpack.base.root_disk_name')] = [
            'driver' => 'local',
            'root'   => base_path(),
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
                'driver'  => 'eloquent',
                'model'   => config('backpack.base.user_model_fqn'),
            ],
        ];

        // add the backpack_users password broker to the configuration
        app()->config['auth.passwords'] = app()->config['auth.passwords'] +
        [
            'backpack' => [
                'provider'  => 'backpack',
                'table'     => 'password_resets',
                'expire'    => 60,
            ],
        ];

        // add the backpack_users guard to the configuration
        app()->config['auth.guards'] = app()->config['auth.guards'] +
        [
            'backpack' => [
                'driver'   => 'session',
                'provider' => 'backpack',
            ],
        ];
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
        return ['crud', 'widgets'];
    }
}
