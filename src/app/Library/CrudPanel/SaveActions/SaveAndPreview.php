<?php

namespace Backpack\CRUD\app\Library\CrudPanel\SaveActions;

use Backpack\CRUD\app\Library\CrudPanel\CrudPanel;
use Illuminate\Http\Request;

class SaveAndPreview extends AbstractSaveAction
{
    protected ?int $order = 4;

    public function getName(): string
    {
        return 'save_and_preview';
    }

    public function getButtonText(): string
    {
        return trans('backpack::crud.save_action_save_and_preview');
    }

    public function isVisible(CrudPanel $crud): bool
    {
        return $crud->hasAccess('show');
    }

    public function getRedirectUrl(CrudPanel $crud, Request $request, $itemId = null): ?string
    {
        $itemId = $itemId ?: $request->get('id');

        if (! $itemId) {
            return $crud->route;
        }

        $redirectUrl = rtrim($crud->route, '/').'/'.$itemId.'/show';

        if ($request->has('_locale')) {
            $redirectUrl .= '?_locale='.$request->get('_locale');
        }

        if ($request->has('_current_tab')) {
            $redirectUrl .= '#'.$request->get('_current_tab');
        }

        return $redirectUrl;
    }

    public function getReferrerUrl(CrudPanel $crud, Request $request, $itemId = null): ?string
    {
        $itemId = $itemId ?: $request->get('id');

        if (! $itemId) {
            return null;
        }

        return url(rtrim($crud->route, '/').'/'.$itemId.'/show');
    }
}
