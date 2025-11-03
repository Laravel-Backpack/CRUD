<?php

namespace Backpack\CRUD\app\Library\CrudPanel\SaveActions;

use Backpack\CRUD\app\Library\CrudPanel\CrudPanel;
use Illuminate\Http\Request;

abstract class AbstractSaveAction implements SaveActionInterface
{
    protected ?int $order = null;

    public function __construct(?int $order = null)
    {
        if ($order !== null) {
            $this->order = $order;
        }
    }

    public function getOrder(): ?int
    {
        return $this->order;
    }

    public function setOrder(int $order): void
    {
        $this->order = $order;
    }

    public function isVisible(CrudPanel $crud): bool
    {
        return true;
    }

    public function getRedirectUrl(CrudPanel $crud, Request $request, $itemId = null): ?string
    {
        return null;
    }

    public function getReferrerUrl(CrudPanel $crud, Request $request, $itemId = null): ?string
    {
        return null;
    }

    public function toArray(): array
    {
        return [
            'name' => $this->getName(),
            'button_text' => $this->getButtonText(),
            'visible' => fn (CrudPanel $crud) => $this->isVisible($crud),
            'redirect' => fn (CrudPanel $crud, Request $request, $itemId = null) => $this->getRedirectUrl($crud, $request, $itemId),
            'referrer_url' => fn (CrudPanel $crud, Request $request, $itemId = null) => $this->getReferrerUrl($crud, $request, $itemId),
            'order' => $this->getOrder(),
            '_handler' => $this,
        ];
    }
}
