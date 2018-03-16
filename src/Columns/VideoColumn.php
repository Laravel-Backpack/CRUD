<?php

namespace Backpack\CRUD\Columns;

use Backpack\CRUD\CRUDTraits\HasLabel;
use Backpack\CRUD\CRUDTraits\HasName;

class VideoColumn extends Column
{
    use HasName, HasLabel;

    protected $type = 'video';
}