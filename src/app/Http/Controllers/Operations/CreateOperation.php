<?php

namespace Backpack\CRUD\app\Http\Controllers\Operations;

use Backpack\CRUD\app\Library\CrudPanel\Hooks\Facades\LifecycleHook;
use Illuminate\Support\Facades\Route;

trait CreateOperation
{
    /**
     * Define which routes are needed for this operation.
     *
     * @param  string  $segment  Name of the current entity (singular). Used as first URL segment.
     * @param  string  $routeName  Prefix of the route name.
     * @param  string  $controller  Name of the current CrudController.
     */
    protected function setupCreateRoutes($segment, $routeName, $controller)
    {
        Route::get($segment.'/create', [
            'as' => $routeName.'.create',
            'uses' => $controller.'@create',
            'operation' => 'create',
        ]);

        Route::post($segment, [
            'as' => $routeName.'.store',
            'uses' => $controller.'@store',
            'operation' => 'create',
        ]);
    }

    /**
     * Add the default settings, buttons, etc that this operation needs.
     */
    protected function setupCreateDefaults()
    {
        $this->crud->allowAccess('create');

        LifecycleHook::hookInto('create:before_setup', function () {
            $this->crud->setupDefaultSaveActions();
        });

        LifecycleHook::hookInto('list:before_setup', function () {
            $this->crud->addButton('top', 'create', 'view', 'crud::buttons.create');
        });
    }

    /**
     * Show the form for creating inserting a new row.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function create()
    {
        $this->crud->hasAccessOrFail('create');

        // prepare the fields you need to show
        $this->data['crud'] = $this->crud;
        $this->data['saveAction'] = $this->crud->getSaveAction();
        $this->data['title'] = $this->crud->getTitle() ?? trans('backpack::crud.add').' '.$this->crud->entity_name;

        // load the view from /resources/views/vendor/backpack/crud/ if it exists, otherwise load the one in the package
        return  request()->ajax() ? 
            view('crud::components.form.form_ajax_view', $this->data) :
            view($this->crud->getCreateView(), $this->data);
    }

    public function createForm()
    {
        $this->crud->hasAccessOrFail('create');

        // if the request isn't an AJAX request, return a 404
        if (! request()->ajax()) {
            abort(404);
        }

        return view(
            $this->crud->getFirstFieldView('form.create_form'),
            [
                'fields' => $this->crud->getCreateFields(),
                'action' => 'create',
                'crud' => $this->crud,
                'modalClass' => request()->get('modal_class'),
                'parentLoadedAssets' => request()->get('parent_loaded_assets'),
            ]
        );
    }

    /**
     * Store a newly created resource in the database.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store()
    {
        $this->crud->hasAccessOrFail('create');

        // execute the FormRequest authorization and validation, if one is required
        $request = $this->crud->validateRequest();

        // register any Model Events defined on fields
        $this->crud->registerFieldEvents();

        // insert item in the db
        $item = $this->crud->create($this->crud->getStrippedSaveRequest($request));
        $this->data['entry'] = $this->crud->entry = $item;

        // show a success message
        \Alert::success(trans('backpack::crud.insert_success'))->flash();

        // save the redirect choice for next time
        $this->crud->setSaveAction();

        return $this->crud->performSaveAction($item->getKey());
    }
}
