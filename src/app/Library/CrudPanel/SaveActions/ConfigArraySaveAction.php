<?php

namespace Backpack\CRUD\app\Library\CrudPanel\SaveActions;

use Backpack\CRUD\app\Library\CrudPanel\CrudPanel;
use Closure;
use Illuminate\Http\Request;

class ConfigArraySaveAction extends AbstractSaveAction
{
    protected string $name;

    protected string $buttonText;

    protected mixed $visible;

    protected mixed $redirect;

    protected mixed $referrerUrl;

    public function __construct(array $definition)
    {
        if (! isset($definition['name']) || $definition['name'] === '') {
            abort(500, 'Please define save action name.', ['developer-error-exception']);
        }

        $this->name = $definition['name'];
        $this->buttonText = $definition['button_text'] ?? $definition['name'];
        $this->visible = $definition['visible'] ?? true;
        $this->redirect = $definition['redirect'] ?? null;
        $this->referrerUrl = $definition['referrer_url'] ?? null;

        parent::__construct($definition['order'] ?? null);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getButtonText(): string
    {
        return $this->buttonText;
    }

    public function isVisible(CrudPanel $crud): bool
    {
        if ($this->visible instanceof Closure) {
            return (bool) ($this->visible)($crud);
        }

        if (is_callable($this->visible)) {
            return (bool) call_user_func($this->visible, $crud);
        }

        return (bool) $this->visible;
    }

    public function getRedirectUrl(CrudPanel $crud, Request $request, $itemId = null): ?string
    {
        if ($this->redirect instanceof Closure) {
            return ($this->redirect)($crud, $request, $itemId);
        }

        if (is_callable($this->redirect)) {
            return call_user_func($this->redirect, $crud, $request, $itemId);
        }

        if (is_string($this->redirect) && $this->redirect !== '') {
            return $this->redirect;
        }

        return $request->has('_http_referrer') ? $request->get('_http_referrer') : $crud->route;
    }

    public function getReferrerUrl(CrudPanel $crud, Request $request, $itemId = null): ?string
    {
        if ($this->referrerUrl instanceof Closure) {
            return ($this->referrerUrl)($crud, $request, $itemId);
        }

        if (is_callable($this->referrerUrl)) {
            return call_user_func($this->referrerUrl, $crud, $request, $itemId);
        }

        if (is_string($this->referrerUrl) && $this->referrerUrl !== '') {
            return $this->referrerUrl;
        }

        return parent::getReferrerUrl($crud, $request, $itemId);
    }
}
