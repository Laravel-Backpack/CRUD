<?php

use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudColumn;
use Backpack\CRUD\app\Library\CrudPanel\CrudField;
use Backpack\CRUD\app\Library\Uploaders\Support\RegisterUploadEvents;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

/**
 * This macro adds the ability to convert a dot.notation string into a [bracket][notation] with some special
 * options that helps us in our usecases.
 *
 * - $ignore: useful when you want to convert a laravel validator rule for nested items and you
 *   would like to ignore the `*` element from the string.
 *
 * - $keyFirst: when true, we will use the first part of the string as key and only bracket the remaining elements.
 *   eg: `address.street`
 *      - when true: `address[street]`
 *      - when false: `[address][street]`
 */
if (! Str::hasMacro('dotsToSquareBrackets')) {
    Str::macro('dotsToSquareBrackets', function ($string, $ignore = [], $keyFirst = true) {
        $stringParts = explode('.', $string);
        $result = '';

        foreach ($stringParts as $key => $part) {
            if (in_array($part, $ignore)) {
                continue;
            }
            $result .= ($key === 0 && $keyFirst) ? $part : '['.$part.']';
        }

        return $result;
    });
}
if (! CrudColumn::hasMacro('withFiles')) {
    CrudColumn::macro('withFiles', function ($uploadDefinition = [], $subfield = null, $registerUploaderEvents = true) {
        /** @var CrudColumn $this */
        $uploadDefinition = is_array($uploadDefinition) ? $uploadDefinition : [];
        $this->setAttributeValue('withFiles', $uploadDefinition);
        $this->save();
        RegisterUploadEvents::handle($this, $uploadDefinition, 'withFiles', $subfield, $registerUploaderEvents);

        return $this;
    });
}

if (! CrudField::hasMacro('withFiles')) {
    CrudField::macro('withFiles', function ($uploadDefinition = [], $subfield = null, $registerUploaderEvents = true) {
        /** @var CrudField $this */
        $uploadDefinition = is_array($uploadDefinition) ? $uploadDefinition : [];
        $this->setAttributeValue('withFiles', $uploadDefinition);
        $this->save();
        RegisterUploadEvents::handle($this, $uploadDefinition, 'withFiles', $subfield, $registerUploaderEvents);

        return $this;
    });
}

if (! CrudColumn::hasMacro('linkTo')) {
    CrudColumn::macro('linkTo', function (string|array|Closure $routeOrConfiguration, ?array $parameters = []): static {
        $wrapper = $this->attributes['wrapper'] ?? [];

        // parse the function input to get the actual route and parameters we'll be working with
        if (is_array($routeOrConfiguration)) {
            $route = $routeOrConfiguration['route'] ?? null;
            $parameters = $routeOrConfiguration['parameters'] ?? [];
        } else {
            $route = $routeOrConfiguration;
        }

        // if the route is a closure, we'll just call it
        if ($route instanceof Closure) {
            $wrapper['href'] = function ($crud, $column, $entry, $related_key) use ($route) {
                return $route($entry, $related_key, $column, $crud);
            };
            $this->wrapper($wrapper);

            return $this;
        }

        // if the route doesn't exist, we'll throw an exception
        if (! $routeInstance = Route::getRoutes()->getByName($route)) {
            throw new Exception("Route [{$route}] not found while building the link for column [{$this->attributes['name']}].");
        }

        // calculate the parameters we'll be using for the route() call
        // (eg. if there's only one parameter and user didn't provide it, we'll assume it's the entry's related key)
        $parameters = (function () use ($parameters, $routeInstance, $route) {
            $expectedParameters = $routeInstance->parameterNames();

            if (count($expectedParameters) === 0) {
                return $parameters;
            }

            $autoInferredParameter = array_diff($expectedParameters, array_keys($parameters));
            if (count($autoInferredParameter) > 1) {
                throw new Exception("Route [{$route}] expects parameters [".implode(', ', $expectedParameters)."]. Insufficient parameters provided in column: [{$this->attributes['name']}].");
            }
            $autoInferredParameter = current($autoInferredParameter) ? [current($autoInferredParameter) => function ($entry, $related_key, $column, $crud) {
                $entity = $crud->isAttributeInRelationString($column) ? Str::before($column['entity'], '.') : $column['entity'];

                return $related_key ?? $entry->{$entity}?->getKey();
            }] : [];

            return array_merge($autoInferredParameter, $parameters);
        })();

        // set up the wrapper href attribute
        $wrapper['href'] = function ($crud, $column, $entry, $related_key) use ($route, $parameters) {
            // if the parameter is callable, we'll call it
            $parameters = collect($parameters)->map(fn ($item) => is_callable($item) ? $item($entry, $related_key, $column, $crud) : $item)->toArray();

            try {
                return route($route, $parameters);
            } catch (Exception $e) {
                return false;
            }
        };

        $this->wrapper($wrapper);

        return $this;
    });
}

if (! CrudColumn::hasMacro('linkToShow')) {
    CrudColumn::macro('linkToShow', function (): static {
        $name = $this->attributes['name'];
        $entity = $this->attributes['entity'] ?? null;
        $route = "$entity.show";

        if (! $entity) {
            throw new Exception("Entity not found while building the link for column [{$name}].");
        }

        if (! Route::getRoutes()->getByName($route)) {
            throw new Exception("Route '{$route}' not found while building the link for column [{$name}].");
        }

        // set up the link to the show page
        $this->linkTo($route);

        return $this;
    });
}

if (! CrudColumn::hasMacro('linkTarget')) {
    CrudColumn::macro('linkTarget', function (string $target = '_self'): static {
        $this->wrapper([
            ...$this->attributes['wrapper'] ?? [],
            'target' => $target,
        ]);

        return $this;
    });
}

/**
 * The route macro allows developers to generate the routes for a CrudController,
 * for all operations, using a simple syntax: Route::crud().
 *
 * It will go to the given CrudController and get the setupRoutes() method on it.
 */
if (! Route::hasMacro('crud')) {
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

        Backpack\CRUD\app\Library\CrudPanel\CrudRouter::setupControllerRoutes($name, $routeName, $controller, $groupNamespace);
    });
}
