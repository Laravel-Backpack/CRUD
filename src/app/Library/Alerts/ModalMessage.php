<?php

namespace Backpack\CRUD\app\Library\Alerts;

final class ModalMessage extends AlertMessage
{
    private ModalAction $action;
    private ?string $text = null;
    private ?string $confirmText = null;
    private ?string $cancelText = null;
    private string $variant = 'primary';

    public function __construct(
        AlertsMessageBag $bag,
        ModalAction $action,
        string $title,
    ) {
        parent::__construct($bag);
        $this->action = $action;
        $this->title = $title;
    }

    public function text(string $text): static
    {
        $this->text = $text;
        return $this;
    }

    public function confirmText(string $text): static
    {
        $this->confirmText = $text;
        return $this;
    }

    public function cancelText(string $text): static
    {
        $this->cancelText = $text;
        return $this;
    }

    public function variant(string $variant): static
    {
        $this->variant = $variant;
        return $this;
    }

    public function jsonSerialize(): array
    {
        return [
            'mode'        => 'modal',
            'action'      => $this->action->value,
            'title'       => $this->title,
            'text'        => $this->text,
            'icon'        => $this->icon,
            'confirmText' => $this->confirmText,
            'cancelText'  => $this->cancelText,
            'variant'     => $this->variant,
        ];
    }
}
