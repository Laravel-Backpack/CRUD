<?php

namespace Backpack\CRUD\app\Http\Controllers\Operations;

use Illuminate\Support\Facades\Route;

trait ShowOperation
{
    /**
     * Define which routes are needed for this operation.
     *
     * @param  string  $segment  Name of the current entity (singular). Used as first URL segment.
     * @param  string  $routeName  Prefix of the route name.
     * @param  string  $controller  Name of the current CrudController.
     */
    protected function setupShowRoutes($segment, $routeName, $controller)
    {
        Route::get($segment.'/{id}/show', [
            'as' => $routeName.'.show',
            'uses' => $controller.'@show',
            'operation' => 'show',
        ]);
    }

    /**
     * Add the default settings, buttons, etc that this operation needs.
     */
    protected function setupShowDefaults()
    {
        $this->crud->allowAccess('show');
        $this->crud->setOperationSetting('setFromDb', true);

        $this->crud->operation('show', function () {
            $this->crud->loadDefaultOperationSettingsFromConfig();

            if (! method_exists($this, 'setupShowOperation')) {
                $this->autoSetupShowOperation();
            }
        });

        $this->crud->operation('list', function () {
            $this->crud->addButton('line', 'show', 'view', 'crud::buttons.show', 'beginning');
        });

        $this->crud->operation(['create', 'update'], function () {
            $this->crud->addSaveAction([
                'name' => 'save_and_preview',
                'visible' => function ($crud) {
                    return $crud->hasAccess('show');
                },
                'redirect' => function ($crud, $request, $itemId = null) {
                    $itemId = $itemId ?: $request->input('id');
                    $redirectUrl = $crud->route.'/'.$itemId.'/show';
                    if ($request->has('_locale')) {
                        $redirectUrl .= '?_locale='.$request->input('_locale');
                    }

                    return $redirectUrl;
                },
                'button_text' => trans('backpack::crud.save_action_save_and_preview'),
            ]);
        });
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\View\View
     */
    public function show($id)
    {
        $this->crud->hasAccessOrFail('show');

        // get entry ID from Request (makes sure its the last ID for nested resources)
        $id = $this->crud->getCurrentEntryId() ?? $id;

        // get the info for that entry (include softDeleted items if the trait is used)
        if ($this->crud->get('show.softDeletes') && in_array('Illuminate\Database\Eloquent\SoftDeletes', class_uses($this->crud->model))) {
            $this->data['entry'] = $this->crud->getModel()->withTrashed()->findOrFail($id);
        } else {
            $this->data['entry'] = $this->crud->getEntryWithLocale($id);
        }

        $this->data['crud'] = $this->crud;
        $this->data['title'] = $this->crud->getTitle() ?? trans('backpack::crud.preview').' '.$this->crud->entity_name;

        // load the view from /resources/views/vendor/backpack/crud/ if it exists, otherwise load the one in the package
        return view($this->crud->getShowView(), $this->data);
    }

    /**
     * Default behaviour for the Show Operation, in case none has been
     * provided by including a setupShowOperation() method in the CrudController.
     */
    protected function autoSetupShowOperation()
    {
        // guess which columns to show, from the database table
        if ($this->crud->get('show.setFromDb')) {
            $this->crud->setFromDb(false, true);
        }

        // if the model has timestamps, add columns for created_at and updated_at
        if ($this->crud->get('show.timestamps') && $this->crud->model->usesTimestamps()) {
            if ($this->crud->model->getCreatedAtColumn()) {
                $this->crud->column($this->crud->model->getCreatedAtColumn())->type('datetime');
            }
            if ($this->crud->model->getUpdatedAtColumn()) {
                $this->crud->column($this->crud->model->getUpdatedAtColumn())->type('datetime');
            }
        }

        // if the model has SoftDeletes, add column for deleted_at
        if ($this->crud->get('show.softDeletes') && in_array('Illuminate\Database\Eloquent\SoftDeletes', class_uses($this->crud->model))) {
            $this->crud->column($this->crud->model->getDeletedAtColumn())->type('datetime');
        }

        // remove the columns that usually don't make sense inside the Show operation
        $this->removeColumnsThatDontBelongInsideShowOperation();
    }

    protected function removeColumnsThatDontBelongInsideShowOperation()
    {
        // cycle through columns
        foreach ($this->crud->columns() as $key => $column) {
            // remove any autoset relationship columns
            if (array_key_exists('model', $column) && array_key_exists('autoset', $column) && $column['autoset']) {
                $this->crud->removeColumn($column['key']);
            }

            // remove any autoset table columns
            if ($column['type'] == 'table' && array_key_exists('autoset', $column) && $column['autoset']) {
                $this->crud->removeColumn($column['key']);
            }

            // remove the row_number column, since it doesn't make sense in this context
            if ($column['type'] == 'row_number') {
                $this->crud->removeColumn($column['key']);
            }

            // remove columns that have visibleInShow set as false
            if (isset($column['visibleInShow'])) {
                if ((is_callable($column['visibleInShow']) && $column['visibleInShow']($this->data['entry']) === false) || $column['visibleInShow'] === false) {
                    $this->crud->removeColumn($column['key']);
                }
            }

            // remove the character limit on columns that take it into account
            if (in_array($column['type'], ['text', 'email', 'model_function', 'model_function_attribute', 'phone', 'row_number', 'select'])) {
                $this->crud->modifyColumn($column['key'], ['limit' => ($column['limit'] ?? 999)]);
            }
        }

        // remove bulk actions columns
        $this->crud->removeColumns(['blank_first_column', 'bulk_actions']);
    }
}
