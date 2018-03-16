<?php


namespace Backpack\CRUD\CRUDTraits;


trait HasAttribute
{
    /**
     * @param $name
     * @return $this
     */
    public function attribute($attribute)
    {
        $this->data['attribute'] = $attribute;
        return $this;
    }
}