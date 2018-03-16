<?php

namespace Backpack\CRUD\Columns;

class CustomColumn extends Column
{
    /**
     * @param $type
     * @return $this
     */
    public function type($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @param $key
     * @param $value
     * @return $this
     */
    public function data($key, $value)
    {
        if ($key == 'type') {
            throw new \InvalidArgumentException('[type] must be provided on initialisation');
        }

        $this->data[$key] = $value;
        return $this;
    }
}