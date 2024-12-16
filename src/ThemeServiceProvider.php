<?php

namespace Backpack\CRUD;

use Backpack\Basset\Facades\Basset;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\Compilers\BladeCompiler;

class ThemeServiceProvider extends ServiceProvider
{
    protected string $path; // the root directory of the theme
    protected string $vendorName = 'backpack';
    protected string $packageName = 'theme-name';
    protected array $commands = [];
    protected bool $theme = true;
    protected ?string $componentsNamespace = null;

    /**
     * -------------------------
     * SERVICE PROVIDER DEFAULTS
     * -------------------------.
     */

    /**
     * Boot method may be overridden by AddonServiceProvider.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->autoboot();
    }

    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function autoboot(): void
    {
        if ($this->packageDirectoryExistsAndIsNotEmpty('bootstrap') &&
            file_exists($helpers = $this->packageHelpersFile())) {
            require $helpers;
        }

        if ($this->packageDirectoryExistsAndIsNotEmpty('resources/lang')) {
            $this->loadTranslationsFrom($this->packageLangsPath(), $this->vendorNameDotPackageName());
        }

        if ($this->packageDirectoryExistsAndIsNotEmpty('resources/views')) {
            $this->loadViews();
        }

        if ($this->packageDirectoryExistsAndIsNotEmpty('database/migrations')) {
            $this->loadMigrationsFrom($this->packageMigrationsPath());
        }

        if ($this->packageDirectoryExistsAndIsNotEmpty('routes')) {
            $this->loadRoutesFrom($this->packageRoutesFile());
        }

        $this->registerPackageBladeComponents();

        // Publishing is only necessary when using the CLI.
        if (app()->runningInConsole()) {
            $this->bootForConsole();
        }
    }

    public function loadViews()
    {
        // if this addon is a theme, but isn't active, don't load any views
        // if ($this->theme && !$this->packageIsActiveTheme()) {
        //     return;
        // }

        // Load published views
        if (is_dir($this->publishedViewsPath())) {
            $this->loadViewsFrom($this->publishedViewsPath(), $this->vendorNameDotPackageName());
        }

        // Fallback to package views
        $this->loadViewsFrom($this->packageViewsPath(), $this->vendorNameDotPackageName());

        // Add default ViewNamespaces
        foreach (['buttons', 'columns', 'fields', 'filters', 'widgets'] as $viewNamespace) {
            if ($this->packageDirectoryExistsAndIsNotEmpty("resources/views/$viewNamespace")) {
                ViewNamespaces::addFor($viewNamespace, $this->vendorNameDotPackageName()."::{$viewNamespace}");
            }
        }

        // Add basset view path
        Basset::addViewPath($this->packageViewsPath());
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register(): void
    {
        if ($this->packageDirectoryExistsAndIsNotEmpty('config')) {
            $this->mergeConfigFrom($this->packageConfigFile(), $this->vendorNameDotPackageName());
        }
    }

    /**
     * Console-specific booting.
     *
     * @return void
     */
    protected function bootForConsole(): void
    {
        // Publishing the configuration file.
        if ($this->packageDirectoryExistsAndIsNotEmpty('config')) {
            $this->publishes([
                $this->packageConfigFile() => $this->publishedConfigFile(),
            ], $this->packageName.'-config');
        }

        // Publishing the views.
        if ($this->packageDirectoryExistsAndIsNotEmpty('resources/views')) {
            $this->publishes([
                $this->packageViewsPath() => $this->publishedViewsPath(),
            ], 'views');

            // Add basset view path
            Basset::addViewPath($this->packageViewsPath());
        }

        // Publishing assets.
        if ($this->packageDirectoryExistsAndIsNotEmpty('resources/assets')) {
            $this->publishes([
                $this->packageAssetsPath() => $this->publishedAssetsPath(),
            ], 'assets');
        }

        // Publishing the translation files.
        if ($this->packageDirectoryExistsAndIsNotEmpty('resources/lang')) {
            $this->publishes([
                $this->packageLangsPath() => $this->publishedLangsPath(),
            ], 'lang');
        }

        // Registering package commands.
        if (! empty($this->commands)) {
            $this->commands($this->commands);
        }
    }

    /**
     * -------------------
     * CONVENIENCE METHODS
     * -------------------.
     */
    protected function vendorNameDotPackageName()
    {
        return $this->vendorName.'.'.$this->packageName;
    }

    protected function vendorNameSlashPackageName()
    {
        return $this->vendorName.'/'.$this->packageName;
    }

    // -------------
    // Package paths
    // -------------

    protected function getPath()
    {
        return $this->path ?? base_path('vendor/'.$this->vendorName.'/'.$this->packageName);
    }

    protected function packageViewsPath()
    {
        return $this->getPath().'/resources/views';
    }

    protected function packageLangsPath()
    {
        return $this->getPath().'/resources/lang';
    }

    protected function packageAssetsPath()
    {
        return $this->getPath().'/resources/assets';
    }

    protected function packageMigrationsPath()
    {
        return $this->getPath().'/database/migrations';
    }

    protected function packageConfigFile()
    {
        return $this->getPath().'/config/'.$this->packageName.'.php';
    }

    protected function packageRoutesFile()
    {
        return $this->getPath().'/routes/'.$this->packageName.'.php';
    }

    protected function packageHelpersFile()
    {
        return $this->getPath().'/bootstrap/helpers.php';
    }

    // ---------------
    // Published paths
    // ---------------

    protected function publishedViewsPath()
    {
        return base_path('resources/views/vendor/'.$this->vendorName.'/'.$this->packageName);
    }

    protected function publishedConfigFile()
    {
        return config_path($this->vendorNameSlashPackageName().'.php');
    }

    protected function publishedAssetsPath()
    {
        return public_path('vendor/'.$this->vendorNameSlashPackageName());
    }

    protected function publishedLangsPath()
    {
        return resource_path('lang/vendor/'.$this->vendorName);
    }

    // -------------
    // Miscellaneous
    // -------------

    protected function packageDirectoryExistsAndIsNotEmpty($name)
    {
        // check if directory exists
        if (! is_dir($this->getPath().'/'.$name)) {
            return false;
        }

        // check if directory has files
        foreach (scandir($this->getPath().'/'.$name) as $file) {
            if ($file != '.' && $file != '..' && $file != '.gitkeep') {
                return true;
            }
        }

        return false;
    }

    public function packageIsActiveTheme()
    {
        $viewNamespace = $this->vendorNameDotPackageName().'::';

        return config('backpack.ui.view_namespace') === $viewNamespace ||
            config('backpack.ui.view_namespace_fallback') === $viewNamespace;
    }

    public function registerPackageBladeComponents()
    {
        if ($this->componentsNamespace) {
            $this->app->afterResolving(BladeCompiler::class, function () {
                Blade::componentNamespace($this->componentsNamespace, $this->packageName);
            });
        }
    }
}
