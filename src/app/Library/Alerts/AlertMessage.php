<?php

namespace Backpack\CRUD\app\Library\Alerts;

use JsonSerializable;

abstract class AlertMessage implements JsonSerializable
{
    protected string $title = '';
    protected ?string $icon = null;

    protected function __construct(
        protected AlertsMessageBag $bag,
    ) {
    }

    public function title(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function icon(string $icon): static
    {
        $this->icon = $icon;

        return $this;
    }

    public function flash(): AlertsMessageBag
    {
        $this->bag->flashSession();

        return $this->bag;
    }

    abstract public function jsonSerialize(): array;
}
