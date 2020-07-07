<?php

namespace Backpack\CRUD\app\Http\Controllers\Operations;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

trait FetchOperation
{
    /**
     * Define which routes are needed for this operation.
     *
     * @param string $segment    Name of the current entity (singular). Used as first URL segment.
     * @param string $routeName  Prefix of the route name.
     * @param string $controller Name of the current CrudController.
     */
    protected function setupFetchOperationRoutes($segment, $routeName, $controller)
    {
        // get all method names on the current model that start with "fetch" (ex: fetchCategory)
        // if a method that looks like that is present, it means we need to add the routes that fetch that entity
        preg_match_all('/(?<=^|;)fetch([^;]+?)(;|$)/', implode(';', get_class_methods($this)), $matches);

        if (count($matches[1])) {
            foreach ($matches[1] as $methodName) {
                Route::post($segment.'/fetch/'.Str::kebab($methodName), [
                    'uses'      => $controller.'@fetch'.$methodName,
                    'operation' => 'FetchOperation',
                ]);
            }
        }
    }

    /**
     * Gets items from database and returns to selects.
     *
     * @param string|array $arg
     * @return \Illuminate\Http\JsonResponse|Illuminate\Database\Eloquent\Collection|Illuminate\Pagination\LengthAwarePaginator
     */
    private function fetch($arg)
    {
        // get the actual words that were used to search for an item (the search term / search string)
        $search_string = request()->input('q') ?? false;

        // if the Class was passed as the sole argument, use that as the configured Model
        // otherwise assume the arguments are actually the configuration array
        $config = [];

        if (! is_array($arg)) {
            if (! class_exists($arg)) {
                return response()->json(['error' => 'Class: '.$arg.' does not exists'], 500);
            }
            $config['model'] = $arg;
        } else {
            $config = $arg;
        }

        $model_instance = new $config['model'];
        // set configuration defaults
        $config['paginate'] = isset($config['paginate']) ? $config['paginate'] : 10;
        $config['searchable_attributes'] = $config['searchable_attributes'] ?? $model_instance->identifiableAttribute();
        $config['query'] = isset($config['query']) && is_callable($config['query']) ? $config['query']($model_instance) : $model_instance; // if a closure that has been passed as "query", use the closure - otherwise use the model

        // FetchOperation is aware of an optional parameter 'keys' that will fetch you the entity/entities that match the provided keys
        if (request()->has('keys')) {
            $array_keys = request()->get('keys');
            if (is_array($array_keys)) {
                return $model_instance->whereIn($model_instance->getKeyName(), $array_keys)->get();
            } else {
                return $model_instance->where($model_instance->getKeyName(), $array_keys)->get();
            }
        }

        // FetchOperation sends an empty query to retrieve the default entry for select when field is not nullable.
        // Also sends an empty query in case we want to load all entities to emulate non-ajax fields
        // when using InlineCreate.

        if ($search_string === false) {
            return ($config['paginate'] !== false) ?
            $config['query']->paginate($config['paginate']) :
            $config['query']->get();
        }

        $textColumnTypes = ['string', 'json_string', 'text', 'longText', 'json_array'];

        // if the query builder brings any where clause already defined by the user we must
        // ensure that the where prevails and we should only use our search as a complement to the query constraints.
        // e.g user want only the active products, so in fetch he would return something like:
        // .... 'query' => function($model) { return $model->where('active', 1); }
        // So it reads: SELECT ... WHERE active = 1 AND (XXX = x OR YYY = y) and not SELECT ... WHERE active = 1 AND XXX = x OR YYY = y;

        if (! empty($config['query']->getQuery()->wheres)) {
            $config['query'] = $config['query']->where(function ($query) use ($model_instance, $config, $search_string, $textColumnTypes) {
                foreach ((array) $config['searchable_attributes'] as $k => $searchColumn) {
                    $operation = ($k == 0) ? 'where' : 'orWhere';
                    $columnType = $model_instance->getColumnType($searchColumn);

                    if (in_array($columnType, $textColumnTypes)) {
                        $tempQuery = $query->{$operation}($searchColumn, 'LIKE', '%'.$search_string.'%');
                    } else {
                        $tempQuery = $query->{$operation}($searchColumn, $search_string);
                    }
                }

                return $tempQuery;
            });
        } else {
            foreach ((array) $config['searchable_attributes'] as $k => $searchColumn) {
                $operation = ($k == 0) ? 'where' : 'orWhere';
                $columnType = $model_instance->getColumnType($searchColumn);

                if (in_array($columnType, $textColumnTypes)) {
                    $config['query'] = $config['query']->{$operation}($searchColumn, 'LIKE', '%'.$search_string.'%');
                } else {
                    $config['query'] = $config['query']->{$operation}($searchColumn, $search_string);
                }
            }
        }

        // return the results with or without pagination
        return ($config['paginate'] !== false) ?
                    $config['query']->paginate($config['paginate']) :
                    $config['query']->get();
    }
}
