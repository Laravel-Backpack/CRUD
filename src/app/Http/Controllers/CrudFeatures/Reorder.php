<?php

namespace Backpack\CRUD\app\Http\Controllers\CrudFeatures;

use Illuminate\Http\Request;

trait Reorder
{
    /**
     *  Reorder the items in the database using the Nested Set pattern.
     *
     *  @return \Illuminate\Http\Response
     */
    public function reorder()
    {
        $this->crud->hasAccessOrFail('reorder');

        if (! $this->crud->isReorderEnabled()) {
            abort(403, 'Reorder is disabled.');
        }

        // get all results for that entity
        $this->data['entries'] = $this->crud->getEntries();
        $this->data['crud'] = $this->crud;
        $this->data['title'] = trans('backpack::crud.reorder').' '.$this->crud->entity_name;

        // load the view from /resources/views/vendor/backpack/crud/ if it exists, otherwise load the one in the package
        return view($this->crud->getReorderView(), $this->data);
    }

    /**
     * Save the new order, using the Nested Set pattern.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|false
     */
    public function saveReorder(Request $request)
    {
        $this->crud->hasAccessOrFail('reorder');

        $entries = $request->tree;
        if (empty($entries)) {
            return false;
        }

        $count = $this->crud->updateTreeOrder($entries);

        return 'success for '.$count.' items';
    }
}
