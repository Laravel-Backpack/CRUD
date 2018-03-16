<?php


namespace Backpack\CRUD\CRUDTraits;


trait HasFunctionName
{
    /**
     * @param $name
     * @return $this
     */
    public function functionName($name)
    {
        $this->data['function_name'] = $name;
        return $this;
    }
}