<?php


namespace Backpack\CRUD\CRUDTraits;


trait HasName
{
    /**
     * @param string $name
     * @return $this
     */
    public function name(string $name)
    {
        $this->data['name'] = $name;
        return $this;
    }
}