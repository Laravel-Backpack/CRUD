<?php

namespace Backpack\CRUD\app\Library\CrudPanel\SaveActions;

use Backpack\CRUD\app\Library\CrudPanel\CrudPanel;
use Illuminate\Http\Request;

class SaveAndNew extends AbstractSaveAction
{
    protected ?int $order = 3;

    public function getName(): string
    {
        return 'save_and_new';
    }

    public function getButtonText(): string
    {
        return trans('backpack::crud.save_action_save_and_new');
    }

    public function isVisible(CrudPanel $crud): bool
    {
        return $crud->hasAccess('create');
    }

    public function getRedirectUrl(CrudPanel $crud, Request $request, $itemId = null): ?string
    {
        return $crud->route.'/create';
    }
}
