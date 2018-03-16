<?php

namespace Backpack\CRUD;

use Backpack\CRUD\Facades\TestFacade;
use Route;
use Illuminate\Support\ServiceProvider;

class CrudServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        // LOAD THE VIEWS

        // - first the published/overwritten views (in case they have any changes)
        $this->loadViewsFrom(resource_path('views/vendor/backpack/crud'), 'crud');
        // - then the stock views that come with the package, in case a published view might be missing
        $this->loadViewsFrom(realpath(__DIR__.'/resources/views'), 'crud');

        // PUBLISH FILES

        // publish lang files
        $this->publishes([__DIR__.'/resources/lang' => resource_path('lang/vendor/backpack')], 'lang');

        // publish views
        $this->publishes([__DIR__.'/resources/views' => resource_path('views/vendor/backpack/crud')], 'views');

        // publish config file
        $this->publishes([__DIR__.'/config' => config_path()], 'config');

        // publish public Backpack CRUD assets
        $this->publishes([__DIR__.'/public' => public_path('vendor/backpack')], 'public');

        // publish custom files for elFinder
        $this->publishes([
            __DIR__.'/config/elfinder.php'      => config_path('elfinder.php'),
            __DIR__.'/resources/views-elfinder' => resource_path('views/vendor/elfinder'),
        ], 'elfinder');

        // AUTO PUBLISH
        if (\App::environment('local')) {
            if ($this->shouldAutoPublishPublic()) {
                \Artisan::call('vendor:publish', [
                    '--provider' => 'Backpack\CRUD\CrudServiceProvider',
                    '--tag' => 'public',
                ]);
            }
        }

        // use the vendor configuration file as fallback
        $this->mergeConfigFrom(
            __DIR__.'/config/backpack/crud.php',
            'backpack.crud'
        );
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('CRUD', function ($app) {
            return new CRUD($app);
        });

        $this->app->bind('CRUDPanel', function ($app) {
            return new \Backpack\CRUD\CrudPanel();
        });

        $this->app->bind('CRUDColumns', function ($app) {
            return new \Backpack\CRUD\CrudColumns();
        });

        // register its dependencies
        $this->app->register(\Backpack\Base\BaseServiceProvider::class);
        $this->app->register(\Barryvdh\Elfinder\ElfinderServiceProvider::class);
        $this->app->register(\Intervention\Image\ImageServiceProvider::class);

        // register their aliases
        $loader = \Illuminate\Foundation\AliasLoader::getInstance();
        $loader->alias('Image', \Intervention\Image\Facades\Image::class);
        $loader->alias('CRUDPanel', \Backpack\CRUD\Facades\CRUDPanel::class);
        $loader->alias('CRUDColumns', \Backpack\CRUD\Facades\CRUDColumns::class);

        // map the elfinder prefix
        if (! \Config::get('elfinder.route.prefix')) {
            \Config::set('elfinder.route.prefix', \Config::get('backpack.base.route_prefix').'/elfinder');
        }
    }

    public static function resource($name, $controller, array $options = [])
    {
        return new CrudRouter($name, $controller, $options);
    }

    /**
     * Checks to see if we should automatically publish
     * vendor files from the public tag.
     *
     * @return bool
     */
    private function shouldAutoPublishPublic()
    {
        $crudPubPath = public_path('vendor/backpack/crud');

        if (! is_dir($crudPubPath)) {
            return true;
        }

        return false;
    }
}
