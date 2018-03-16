<?php


namespace Backpack\CRUD\CRUDTraits;


trait HasModel
{
    /**
     * @param $fqn
     * @return $this
     */
    public function model($fqn)
    {
        $this->data['model'] = $fqn;
        return $this;
    }
}