<?php

namespace Backpack\CRUD\app\Library\Components\Attributes;

use Backpack\CRUD\app\Library\Components\Interfaces\SmartAttributeInterface;
use Backpack\CRUD\app\Library\Components\Interfaces\SmartCollectionInterface;

class Model extends BaseAttribute implements SmartAttributeInterface
{
    public static function getDefault(SmartCollectionInterface $attributes)
    {
        return $attributes->getAttributeValue('relation_type') ? app('crud')->inferFieldModelFromRelationship($attributes->toArray()) : false;
    }

    public static function getValidationRules(SmartCollectionInterface $attributes): array
    {
        return [
            'required',
            function ($attribute, $value, $fail) use ($attributes) {
                if ($attributes->hasAttribute('entity') && $attributes->getAttributeValue('entity')) {
                    if (! is_string($value)) {
                        $fail('The '.$attribute.' should be a valid model string.');
                    }

                    if (! is_a($value, 'Illuminate\Database\Eloquent\Model', true)) {
                        $fail('The '.$attribute.' should be a valid class that extends Illuminate\Database\Eloquent\Model .');
                    }
                } else {
                    if ($value !== false) {
                        $fail('The '.$attribute.' should be either false or a valid class that extends Illuminate\Database\Eloquent\Model.');
                    }
                }
            },
        ];
    }

    public static function getAttributeName(): string
    {
        return 'model';
    }
}
