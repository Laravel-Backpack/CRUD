<?php

namespace Backpack\CRUD\app\Library\CrudPanel\Traits;

trait Delete
{
    /*
    |--------------------------------------------------------------------------
    |                                   DELETE
    |--------------------------------------------------------------------------
    */

    /**
     * Delete a row from the database.
     *
     * @param int $id The id of the item to be deleted.
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException if the model was not found.
     *
     * TODO: should this delete items with relations to it too?
     *
     * @return bool True if the item was deleted.
     */
    public function delete($id)
    {
        return (string) $this->model->findOrFail($id)->delete();
    }
}
