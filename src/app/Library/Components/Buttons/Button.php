<?php

namespace Backpack\CRUD\app\Library\Components\Buttons;

use Backpack\CRUD\app\Library\Components\AttributeCollection;
use Backpack\CRUD\app\Library\Components\Attributes\Fields\StackAttribute;
use Backpack\CRUD\app\Library\Components\Component;

class Button extends Component
{
    public function __construct(AttributeCollection $attributes)
    {
        parent::__construct($attributes);
    }

    public static function getAttributeDefaults(): array
    {
        return [
            'stack'   => 'top',
            'content' => 'default',
        ];
    }

    public static function getAttributeValidationRules(): array
    {
        return [
            'stack'   => StackAttribute::class,
            'content' => ['required', 'string'],
        ];
    }

    public function stack(string $stack)
    {
        $this->setAttribute('stack', $stack);

        return $this;
    }

    public function content(string $content)
    {
        $this->setAttribute('content', $content);

        return $this;
    }
}
