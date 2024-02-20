<?php

namespace Backpack\CRUD\app\Library\Components\Attributes\Buttons;

use Backpack\CRUD\app\Library\Components\AttributeCollection;
use Backpack\CRUD\app\Library\Components\Attributes\BaseAttribute;

class StackAttribute extends BaseAttribute
{
    public static function getDefault(AttributeCollection $attributes)
    {
        return 'line';
    }

    public static function getValidationRules(): array
    {
        return ['required', 'string', 'in:top,bottom,line'];
    }

    public static function getAttributeName(): string
    {
        return 'stack';
    }
}
