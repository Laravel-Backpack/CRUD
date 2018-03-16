<?php


namespace Backpack\CRUD\CRUDTraits;


trait HasLabel
{
    /**
     * @param string $label
     * @return $this
     */
    public function label(string $label)
    {
        $this->data['label'] = $label;
        return $this;
    }
}