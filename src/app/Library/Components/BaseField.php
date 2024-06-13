<?php

namespace Backpack\CRUD\app\Library\Components;

use Backpack\CRUD\app\Library\Components\Attributes\Entity;
use Backpack\CRUD\app\Library\Components\Attributes\Fields\FieldType;
use Backpack\CRUD\app\Library\Components\Attributes\Label;
use Backpack\CRUD\app\Library\Components\Attributes\Model;
use Backpack\CRUD\app\Library\Components\Attributes\RelationType;
use Backpack\CRUD\app\Library\Components\Fields\FieldCollection;

/**
 * @method self type(string $type) the type of the field
 * @method self label(string $type) the type of the field
 * @method self name()
 * @method self value()
 * @method self placeholder()
 * @method self hint()
 * @method self wrapper()
 */
class BaseField extends BaseComponent
{
    public function __construct(string|array $attributes)
    {
        $collection = new FieldCollection($attributes, static::attributes(), static::rules(), static::defaults(), static::blocked());

        parent::__construct($collection);
    }

    /**
     * IMPORTANT: the order of the attributes is important. If you need some attribute that depends on another
     * attribute, make sure that the attribute that you need is defined before the attribute that depends on it.
     *
     * @return array
     */
    public static function attributes(): array
    {
        return [
            Label::class,
            Entity::class,
            RelationType::class,
            FieldType::class,
            Model::class,
        ];
    }

    public static function make(string|array $attributes): static
    {
        return new static($attributes);
    }
}
