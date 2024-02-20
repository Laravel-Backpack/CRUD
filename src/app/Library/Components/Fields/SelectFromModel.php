<?php

namespace Backpack\CRUD\app\Library\Components\Fields;

use Backpack\CRUD\app\Library\Components\Attributes\Entity;
use Backpack\CRUD\app\Library\Components\Attributes\FieldType;
use Backpack\CRUD\app\Library\Components\BaseField;

class SelectFromModel extends BaseField
{
    public function __construct(string|array $attributes)
    {
        parent::__construct($attributes);
        $this->attributes->setAttribute('type', 'select_from_model');
    }

    public static function blocked(): array
    {
        return [FieldType::class];
    }

    public function model(string $model): self
    {
        $this->attributes->setAttribute('model', $model);

        return $this;
    }

    public static function defaults(): array
    {
        return [
            Entity::class,
            'model'  => function ($attributes) {
                return app('crud')->inferModelFromRelationship($attributes->toArray());
            },
        ];
    }
}
