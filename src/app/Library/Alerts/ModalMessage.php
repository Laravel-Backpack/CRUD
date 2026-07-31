<?php

namespace Backpack\CRUD\app\Library\Alerts;

final class ModalMessage extends AlertMessage
{
    private AlertType $type;
    private ?string $text;
    private ?int $timer = null;
    private ?string $button = 'OK';
    private bool $closeOnClickOutside = true;
    private bool $closeOnEsc = true;
    private ?string $className = null;
    private bool $showCloseButton = false;

    public function __construct(
        AlertsMessageBag $bag,
        AlertType $type,
        ?string $text = null,
    ) {
        parent::__construct($bag);
        $this->type = $type;
        $this->text = $text;
    }

    // ── Fluent setters (friendly PHP names, store as JS-compatible keys) ──

    public function text(string $text): static
    {
        $this->text = $text;

        return $this;
    }

    public function timer(?int $ms): static
    {
        $this->timer = $ms;

        return $this;
    }

    /** @param string|bool $text Button label, or false to hide the confirm button. */
    public function button(string|bool $text = 'OK'): static
    {
        $this->button = $text === false ? null : $text;

        return $this;
    }

    public function backdrop(bool $value = true): static
    {
        $this->closeOnClickOutside = $value;

        return $this;
    }

    public function escapeKey(bool $value = true): static
    {
        $this->closeOnEsc = $value;

        return $this;
    }

    public function className(?string $class): static
    {
        $this->className = $class;

        return $this;
    }

    public function showCloseButton(bool $value = true): static
    {
        $this->showCloseButton = $value;

        return $this;
    }

    // ── Getters ──

    public function type(): AlertType
    {
        return $this->type;
    }

    // ── Serialization ──

    public function jsonSerialize(): array
    {
        return [
            'mode' => 'modal',
            'title' => $this->title,
            'text' => $this->text,
            'icon' => $this->icon ?? $this->type->value,
            'timer' => $this->timer,
            'button' => $this->button,
            'closeOnClickOutside' => $this->closeOnClickOutside,
            'closeOnEsc' => $this->closeOnEsc,
            'className' => $this->className,
            'showCloseButton' => $this->showCloseButton,
        ];
    }
}
