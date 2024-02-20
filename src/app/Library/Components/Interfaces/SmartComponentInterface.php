<?php

namespace Backpack\CRUD\app\Library\Components\Interfaces;

interface SmartComponentInterface
{
    public static function make(string|array $name): SmartComponentInterface;

    public static function makeOf(SmartCollectionInterface $attributes);

    public static function rules(): array;

    public static function defaults(): array;

    public static function blocked(): array;

    public static function attributes(): array;
}
