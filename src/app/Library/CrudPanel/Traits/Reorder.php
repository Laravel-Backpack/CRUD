<?php

namespace Backpack\CRUD\app\Library\CrudPanel\Traits;

use Illuminate\Support\Facades\DB;

/**
 * Properties and methods for the Reorder operation.
 */
trait Reorder
{
    /**
     * Change the order and parents of the given elements, according to the NestedSortable AJAX call.
     *
     * @param  array  $request  The entire request from the NestedSortable AJAX Call.
     * @return int The number of items whose position in the tree has been changed.
     */
    public function updateTreeOrder($request)
    {
        $primaryKey = $this->model->getKeyName();

        $columns = $this->getOperationSetting('reorderColumnNames');

        // we use the upsert method that should update the values of the matching ids.
        // it has the drawback of creating new entries when the id is not found
        // for that reason we get a list of all the ids and filter the ones
        // sent in the request that are not in the database
        $itemKeys = $this->getModelWithCrudPanelQuery()->select($primaryKey)->get()->pluck($primaryKey);

        // compare the keys as strings, so that only the exact keys in the database match
        $existingKeys = $itemKeys->map(fn ($key) => (string) $key)->all();
        $isExistingKey = function ($key) use ($existingKeys) {
            return (is_int($key) || is_string($key)) && in_array((string) $key, $existingKeys, true);
        };

        // filter the items that are not in the database and map the request
        $reorderItems = collect($request)->filter(function ($item) use ($isExistingKey) {
            return is_array($item) && $isExistingKey($item['item_id'] ?? null);
        })->map(function ($item) use ($primaryKey, $columns, $isExistingKey) {
            $item[$primaryKey] = $item['item_id'];
            $item[$columns['parent_id']] = empty($item['parent_id']) ? null : $item['parent_id'];

            // only allow re-parenting under items the current user can actually see
            if ($item[$columns['parent_id']] !== null && ! $isExistingKey($item[$columns['parent_id']])) {
                $item[$columns['parent_id']] = null;
            }

            $item[$columns['depth']] = empty($item['depth']) ? null : (int) $item['depth'];
            $item[$columns['lft']] = empty($item['left']) ? null : (int) $item['left'];
            $item[$columns['rgt']] = empty($item['right']) ? null : (int) $item['right'];

            // unset mapped items properties.
            if ($columns['parent_id'] !== 'parent_id') {
                unset($item['parent_id']);
            }
            if ($columns['depth'] !== 'depth') {
                unset($item['depth']);
            }
            if ($columns['lft'] !== 'left') {
                unset($item['left']);
            }
            if ($columns['rgt'] !== 'right') {
                unset($item['right']);
            }

            // unset the item_id property
            unset($item['item_id']);

            return $item;
        })->toArray();

        $this->abortIfTreeIsNotAllowed($reorderItems, $primaryKey, $columns['parent_id']);

        $sentIds = array_column($reorderItems, $primaryKey);

        $itemKeys = $itemKeys->filter(function ($id) use ($sentIds) {
            return in_array($id, $sentIds);
        });

        // wrap the queries in a transaction to avoid partial updates
        DB::connection($this->model->getConnectionName())->transaction(function () use ($reorderItems, $primaryKey, $itemKeys, $columns) {
            // create a string of ?,?,?,? to use as bind placeholders for item keys
            $reorderItemsBindString = implode(',', array_fill(0, count($reorderItems), '?'));

            // each of this properties will be updated using a single query with a CASE statement
            // this ensures that only 4 queries are run, no matter how many items are reordered
            foreach (array_values($columns) as $column) {
                $query = '';
                $bindings = [];
                $query .= "UPDATE {$this->model->getTable()} SET {$column} = CASE ";
                foreach ($reorderItems as $item) {
                    $query .= "WHEN {$primaryKey} = ? THEN ? ";
                    $bindings[] = $item[$primaryKey];
                    $bindings[] = $item[$column];
                }
                // add the bind placeholders for the item keys at the end the array of bindings
                array_push($bindings, ...$itemKeys->toArray());

                // add the where clause to the query to help match the items
                $query .= "ELSE {$column} END WHERE {$primaryKey} IN ({$reorderItemsBindString})";

                DB::connection($this->model->getConnectionName())->statement($query, $bindings);
            }
        });

        return count($reorderItems);
    }

    /**
     * Make sure the submitted tree has no loops and does not go deeper than the maximum level allowed.
     *
     * @param  array  $items  The reorder items, already mapped to the database columns.
     * @param  string  $primaryKey
     * @param  string  $parentColumn
     */
    private function abortIfTreeIsNotAllowed(array $items, $primaryKey, $parentColumn): void
    {
        $maxLevel = (int) ($this->get('reorder.max_level') ?? 3);

        $parents = [];
        foreach ($items as $item) {
            $parents[(string) $item[$primaryKey]] = $item[$parentColumn] === null ? null : (string) $item[$parentColumn];
        }

        foreach ($parents as $key => $parent) {
            $depth = 1;
            $visited = [(string) $key => true];

            while ($parent !== null) {
                if (isset($visited[$parent])) {
                    abort(422, 'The reorder tree is not valid.');
                }

                $visited[$parent] = true;
                $depth++;
                $parent = $parents[$parent] ?? null;
            }

            if ($maxLevel > 0 && $depth > $maxLevel) {
                abort(422, 'The reorder tree exceeds the maximum level allowed.');
            }
        }
    }

    /**
     * Enable the Reorder functionality in the CRUD Panel for users that have the been given access to 'reorder' using:
     * $this->crud->allowAccess('reorder');.
     *
     * @param  string  $label  Column name that will be shown on the labels.
     * @param  int  $max_level  Maximum hierarchy level to which the elements can be nested (1 = no nesting, just reordering).
     */
    public function enableReorder($label = 'name', $max_level = 1)
    {
        $this->setOperationSetting('enabled', true);
        $this->setOperationSetting('label', $label);
        $this->setOperationSetting('max_level', $max_level);
    }

    /**
     * Disable the Reorder functionality in the CRUD Panel for all users.
     */
    public function disableReorder()
    {
        $this->setOperationSetting('enabled', false);
    }

    /**
     * Check if the Reorder functionality is enabled or not.
     *
     * @return bool
     */
    public function isReorderEnabled()
    {
        return $this->getOperationSetting('enabled');
    }
}
