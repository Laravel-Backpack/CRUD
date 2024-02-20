<?php

namespace  Backpack\CRUD\app\Library\Components\Fields;

use Backpack\CRUD\app\Library\Components\BaseField;

/**
 * @method self skizo(string $test)
 */
class TextField extends BaseField
{
    public function __construct(string|array $attributes)
    {
        parent::__construct($attributes);
        $this->attributes->setAttribute('type', 'text');
    }

    public static function blocked(): array
    {
        return ['type'];
    }
}
