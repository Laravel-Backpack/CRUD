<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Backpack Testing Routes
|--------------------------------------------------------------------------
| This routes are loaded only when running unit tests.
|
*/

Route::group([
    (array) config('backpack.base.web_middleware', 'web'),
    (array) config('backpack.base.middleware_key', 'admin'),
    'prefix' => config('backpack.base.route_prefix', 'admin'),
],
    function () {
        Route::crud('users', 'Backpack\CRUD\Tests\Unit\Http\Controllers\UserCrudController');
    }
);
