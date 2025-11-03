<?php

namespace Backpack\CRUD\app\Library\CrudPanel\SaveActions;

use Backpack\CRUD\app\Library\CrudPanel\CrudPanel;
use Illuminate\Http\Request;

class SaveAndBack extends AbstractSaveAction
{
    protected ?int $order = 1;

    public function getName(): string
    {
        return 'save_and_back';
    }

    public function getButtonText(): string
    {
        return trans('backpack::crud.save_action_save_and_back');
    }

    public function isVisible(CrudPanel $crud): bool
    {
        return $crud->hasAccess('list');
    }

    public function getRedirectUrl(CrudPanel $crud, Request $request, $itemId = null): ?string
    {
        return $request->has('_http_referrer') ? $request->get('_http_referrer') : $crud->route;
    }
}
