<?php


namespace Backpack\CRUD\CRUDTraits;


trait HasEntity
{
    /**
     * @param $name
     * @return $this
     */
    public function entity($name)
    {
        $this->data['entity'] = $name;
        return $this;
    }
}