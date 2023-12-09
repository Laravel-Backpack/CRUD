<?php

namespace Backpack\CRUD\app\Library\CrudPanel\Traits;

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

        // we use the upsert method that should update the values of the matching ids.
        // it has the drawback of creating new entries when the id is not found
        // for that reason we get a list of all the ids and filter the ones
        // sent in the request that are not in the database
        $itemKeys = $this->model->all()->pluck('id');

        $reorderItems = collect($request)->filter(function ($item) use ($itemKeys) {
            return $item['item_id'] != '' && $item['item_id'] != null && $itemKeys->contains($item['item_id']);
        })->map(function ($item) use ($primaryKey) {
            $item[$primaryKey] = $item['item_id'];
            $item['parent_id'] = empty($item['parent_id']) ? null : $item['parent_id'];
            $item['depth'] = empty($item['depth']) ? null : $item['depth'];
            $item['lft'] = empty($item['left']) ? null : $item['left'];
            $item['rgt'] = empty($item['right']) ? null : $item['right'];
            unset($item['item_id']);

            return $item;
        })->toArray();

        $this->model->upsert(
            $reorderItems,
            [$primaryKey],
            ['parent_id', 'depth', 'lft', 'rgt']
        );

        return count($reorderItems);
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
