<?php

namespace Backpack\CRUD\Columns;

use Backpack\CRUD\CRUDTraits\HasLabel;
use Backpack\CRUD\CRUDTraits\HasName;

class MultidimensionalArrayColumn extends Column
{
    use HasName, HasLabel;

    protected $type = 'multidimensional_array';

    /**
     * @param $key
     * @return $this
     */
    public function visibleKey($key)
    {
        $this->data['visible_key'] = $key;
        return $this;
    }
}