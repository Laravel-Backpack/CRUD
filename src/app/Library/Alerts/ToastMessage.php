<?php

namespace Backpack\CRUD\app\Library\Alerts;

final class ToastMessage extends AlertMessage
{
    private AlertType $type;
    private string $message;
    private int $timeout = 2500;
    private bool $dismissible = true;
    private ?string $className = null;
    private ?string $position = null;

    public function __construct(
        AlertsMessageBag $bag,
        AlertType $type,
        string $message,
    ) {
        parent::__construct($bag);
        $this->type = $type;
        $this->message = $message;
    }

    public function timeout(int $ms): static
    {
        $this->timeout = $ms;
        return $this;
    }

    public function dismissible(bool $value = true): static
    {
        $this->dismissible = $value;
        return $this;
    }

    public function className(string $class): static
    {
        $this->className = $class;
        return $this;
    }

    public function position(string $pos): static
    {
        $this->position = $pos;
        return $this;
    }

    public function type(): AlertType
    {
        return $this->type;
    }

    public function jsonSerialize(): array
    {
        return [
            'mode'        => 'toast',
            'type'        => $this->type->value,
            'message'     => $this->message,
            'title'       => $this->title,
            'icon'        => $this->icon,
            'timeout'     => $this->timeout,
            'dismissible' => $this->dismissible,
            'className'   => $this->className,
            'position'    => $this->position,
        ];
    }
}
