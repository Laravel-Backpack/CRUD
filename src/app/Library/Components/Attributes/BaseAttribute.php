<?php

namespace Backpack\CRUD\app\Library\Components\Attributes;

use Backpack\CRUD\app\Library\Components\Interfaces\SmartAttributeInterface;
use Backpack\CRUD\app\Library\Components\Interfaces\SmartCollectionInterface;
use Illuminate\Support\Facades\Validator;

class BaseAttribute implements SmartAttributeInterface
{
    public function __construct(
                    protected string $name,
                    private $value = null,
                    private $rules = [],
                    private $default = []
                ) {
    }

    public static function getValidationRules(SmartCollectionInterface $attributes): array
    {
        return [];
    }

    public static function make(string $name, $value = null, $rules = [])
    {
        return new static($name, $value, $rules);
    }

    public static function getDefault(SmartCollectionInterface $attributes)
    {
        return null;
    }

    public static function getAttributeName(): string
    {
        return self::$name;
    }

    public function setValue($value)
    {
        $this->validate($value);

        $this->value = $value;

        return $this;
    }

    public function setRules(array $rules)
    {
        $this->rules = $rules;
    }

    public function rules(): array
    {
        return $this->rules;
    }

    public function value()
    {
        return $this->value;
    }

    public function name(): string
    {
        return $this->name;
    }

    private function validate($value)
    {
        $validator = Validator::make([$this->name => $value], [$this->name => $this->rules])->stopOnFirstFailure();

        if ($validator->fails()) {
            throw new \Exception($validator->errors()->first());
        }
    }
}
