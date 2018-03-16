<?php


namespace Backpack\CRUD\Columns;

use Backpack\CRUD\Facades\CRUDPanel;

abstract class Column
{
    protected $type;
    protected $data = [];

    /**
     * Column constructor.
     * @param null $data
     */
    function __construct($data = null)
    {
        if (is_array($data)) {
            $this->data = $data;
        }
    }

    /**
     * Add this column to the panel columns
     * @return \Backpack\CRUD\Facades\CRUDPanel
     */
    function add()
    {
        return CRUDPanel::addColumn($this->toArray());
    }

    /**
     * Generate the data for CrudPanel
     * @return array this colums array representation
     */
    public function toArray()
    {
        return array_merge($this->data, ['type' => $this->type]);
    }
}