<?php

return [
    /*
    |--------------------------------------------------------------------------
    | CRUD Controllers Path
    |--------------------------------------------------------------------------
    |
    | The path where your CrudControllers are located. The test generator
    | will scan this directory to find controllers and generate tests.
    | You can pass a string or an array of strings.
    |
    */
    'controllers_path' => app_path('Http/Controllers'),

    /*
    |--------------------------------------------------------------------------
    | Test Configurations
    |--------------------------------------------------------------------------
    |
    | Here you can map your CrudControllers to a specific TestConfiguration class.
    | This allows you to customize the data used for testing, mock dependencies,
    | or define valid/invalid inputs for specific controllers.
    |
    | Example:
    | App\Http\Controllers\Admin\ProductCrudController::class => Tests\Config\ProductConfig::class,
    |
    */
    'configurations' => [],
];
