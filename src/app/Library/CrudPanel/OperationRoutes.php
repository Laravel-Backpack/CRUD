<?php

namespace Backpack\CRUD\app\Library\CrudPanel;

use Illuminate\Support\Facades\Route;

final class OperationRoutes
{
    public static function register($routes)
    {
        $routes = (array) $routes;
        $operations = [];
        foreach ($routes as $route) {
            Route::{$route->getVerb()}($route->getRoute(), $route->getConfiguration());
            $operations[] = $route->getOperation();
        }

        return $operations;
    }
}
