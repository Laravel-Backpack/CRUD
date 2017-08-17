<?php

namespace Backpack\CRUD\Columns;

use Backpack\CRUD\CRUDTraits\HasName;
use Backpack\CRUD\CRUDTraits\HasLabel;
use Backpack\CRUD\CRUDTraits\HasModel;
use Backpack\CRUD\CRUDTraits\HasEntity;
use Backpack\CRUD\CRUDTraits\HasAttribute;

class SelectMultipleColumn extends Column
{
    use HasName, HasLabel, HasAttribute, HasEntity, HasModel;

    protected $type = 'select_multiple';
}
