<?php

namespace Backpack\CRUD\app\Library\Components\Interfaces;

use Illuminate\Support\Collection;

interface SmartCollectionInterface
{
    public function __construct(array|string $initAttributes, array $componentAttributes, array $rules = [], array $defaults = [], array $blocked = []);

    public function getItemByName(string $name);

    public function getAttributeValue(string $attribute);

    public function getItems(): Collection;

    public function getAttributes(): Collection;

    public function deleteItem(string $name);

    public static function attributes(): array;

    public function setAttribute(string $attribute, $value);

    public function hasAttribute(string $attribute): bool;

    public function getCollection();

    public function saveCollection($collection);

    public function toArray(): array;

    public function toCollection(): Collection;
}
