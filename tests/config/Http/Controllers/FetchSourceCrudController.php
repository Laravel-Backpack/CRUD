<?php

namespace Backpack\CRUD\Tests\config\Http\Controllers;

use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\Tests\config\Models\Article;

/**
 * Test double mimicking the PRO FetchOperation contract used by the save-time relation
 * guard, so the `relation_options_query_source` branch can be exercised without PRO.
 */
class FetchSourceCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;

    public function setup()
    {
        $this->crud->setModel(Article::class);
        $this->crud->setRoute('fetch-source');
    }

    public function getRelationFetchQuery(string $fetchMethod)
    {
        $queries = [
            'fetchPlanet' => function ($query) {
                return $query->where('id', 1);
            },
            'fetchRole' => function ($query) {
                return $query->where('id', 1);
            },
            'fetchModeratorUser' => function ($query) {
                return $query->where('id', 1);
            },
        ];

        $query = $queries[$fetchMethod] ?? null;

        return is_callable($query) ? $query : null;
    }
}
