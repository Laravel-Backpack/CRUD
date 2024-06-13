<?php

namespace Backpack\CRUD\app\Library\Components\Interfaces;

interface SmartAttributeInterface
{
    public static function getAttributeName(): string;

    public function rules(): array;

    public function value();

    public function name(): string;

    public static function getDefault(SmartCollectionInterface $attributes);

    public static function getValidationRules(SmartCollectionInterface $attributes): array;
}
