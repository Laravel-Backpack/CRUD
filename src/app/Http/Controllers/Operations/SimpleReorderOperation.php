<?php

namespace App\Http\Controllers\Admin\Operations;

use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\View\View;

trait SimpleReorderOperation
{
    protected function setupSimpleReorderRoutes(string $segment, string $routeName, string $controller): void
    {
        Route::get("{$segment}/reorder", [
            'as' => "{$routeName}.reorder",
            'uses' => "{$controller}@reorder",
            'operation' => 'reorder',
        ]);

        Route::post("{$segment}/reorder", [
            'as' => "{$routeName}.save.reorder",
            'uses' => "{$controller}@saveReorder",
            'operation' => 'reorder',
        ]);
    }

    protected function setupSimpleReorderDefaults()
    {
        CRUD::set('reorder.enabled', true);
        CRUD::allowAccess('reorder');

        CRUD::operation('reorder', function () {
            CRUD::loadDefaultOperationSettingsFromConfig();
        });

        CRUD::operation('list', function () {
            CRUD::addButton('top', 'reorder', 'view', 'crud::buttons.reorder');
        });
    }

    public function reorder(): View
    {
        CRUD::hasAccessOrFail('reorder');

        $column = CRUD::get('reorder.column');

        return view('crud::simple-reorder', [
            'entries' => $this->crud->getEntries()->sortBy($column)->keyBy($this->crud->getModel()->getKeyName()),
            'crud' => $this->crud,
            'title' => $this->crud->getTitle() ?? trans('backpack::crud.reorder') . ' ' . $this->crud->entity_name,
        ]);
    }

    public function saveReorder(Request $request): string|false
    {
        CRUD::hasAccessOrFail('reorder');

        $entries = json_decode($request->input('tree'), true);

        if (empty($entries)) {
            return false;
        }

        DB::transaction(function () use ($entries) {
            $primaryKey = $this->crud->model->getKeyName();
            $table = $this->crud->model->getTable();
            $connection = $this->crud->model->getConnectionName();
            $column = $this->crud->get('reorder.column');

            $query = '';
            $bindings = $itemKeys = [];
            $query .= "UPDATE `{$table}` SET `{$column}` = CASE ";
            foreach ($entries as $order => $item) {
                $itemKey = str_replace('list_', '', $item);
                $itemKeys[] = $itemKey;

                $query .= "WHEN `{$primaryKey}` = ? THEN ? ";
                $bindings[] = $itemKey;
                $bindings[] = $order;
            }
            // add the bind placeholders for the item keys at the end the array of bindings
            array_push($bindings, ...$itemKeys);
            $reorderItemsBindString = implode(',', array_fill(0, count($itemKeys), '?'));

            // add the where clause to the query to help match the items
            $query .= "ELSE `{$column}` END WHERE `{$primaryKey}` IN ({$reorderItemsBindString})";

            DB::connection($connection)->statement($query, $bindings);
        });

        $updatedNumber = count($entries);

        return "success for {$updatedNumber} items";
    }
}
