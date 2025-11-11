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
        app\Console\Commands\Upgrade\UpgradeCommand::class,
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
     * @param  \Illuminate\Routing\Router  $router
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
     * @param  \Illuminate\Routing\Router  $router
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
