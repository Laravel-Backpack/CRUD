<?php

namespace Backpack\CRUD\Columns;

use Backpack\CRUD\CRUDTraits\HasLabel;
use Backpack\CRUD\CRUDTraits\HasName;

class ArrayCountColumn extends Column {

    use HasName, HasLabel;

    protected $type = 'array_count';
}