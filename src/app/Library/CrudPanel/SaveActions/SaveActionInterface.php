<?php

namespace Backpack\CRUD\app\Library\CrudPanel\SaveActions;

use Backpack\CRUD\app\Library\CrudPanel\CrudPanel;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;

interface SaveActionInterface extends Arrayable
{
    public function getName(): string;

    public function getButtonText(): string;

    public function isVisible(CrudPanel $crud): bool;

    public function getRedirectUrl(CrudPanel $crud, Request $request, $itemId = null): ?string;

    public function getReferrerUrl(CrudPanel $crud, Request $request, $itemId = null): ?string;

    public function getOrder(): ?int;

    public function setOrder(int $order): void;
}
