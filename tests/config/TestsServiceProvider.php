<?php

namespace Backpack\CRUD\Tests\config;

use Backpack\CRUD\ViewNamespaces;

class TestsServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function register()
    {
        // register theme views as coreuiv2, the default ui.namespace.
        $this->loadViewsFrom(__DIR__.'/views', 'backpack.theme-coreuiv2');

        foreach (['buttons', 'fields', 'columns'] as $domain) {
            ViewNamespaces::addFor($domain, 'backpack.theme-coreuiv2::'.$domain);
        }

        // Register the  facade alias for basset, alert and crud
        $loader = \Illuminate\Foundation\AliasLoader::getInstance();
        $loader->alias('Basset', \Backpack\Basset\Facades\Basset::class);
        $loader->alias('Alert', \Backpack\CRUD\app\Library\Alerts\Alert::class);
        $loader->alias('CRUD', \Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade::class);
    }
}
