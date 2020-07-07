<?php

namespace Backpack\CRUD\app\Library\CrudPanel\Traits;

use Illuminate\Support\Arr;

trait SaveActions
{
    /**
     * Get the developer's preference on what save action is the default one
     * for the current operation.
     *
     * @return string
     */
    public function getSaveActionDefaultForCurrentOperation()
    {
        return config('backpack.crud.operations.'.$this->getCurrentOperation().'.defaultSaveAction', 'save_and_back');
    }

    /**
     * Get the save action with full fallback until default.
     *
     * @return string
     */
    public function getFallBackSaveAction()
    {
        //we get the higher order in save actions array. It will return something only when explicit by developer
        $higherAction = $this->getSaveActionByOrder(1);

        if (empty($higherAction)) {
            if ($this->hasOperationSetting('defaultSaveAction')) {
                return $this->getOperationSetting('defaultSaveAction');
            }

            return $this->getSaveActionDefaultForCurrentOperation();
        }

        return key($higherAction);
    }

    /**
     * Gets the save action that has the desired order.
     *
     * @param int $order
     * @return array
     */
    public function getSaveActionByOrder($order)
    {
        return array_filter($this->getOperationSetting('save_actions'), function ($arr) use ($order) {
            return $arr['order'] == $order;
        });
    }

    /**
     * Allow the developer to register multiple save actions.
     *
     * @param array $saveActions
     * @return void
     */
    public function addSaveActions($saveActions)
    {
        // count vs count recursive will be diferent when counting single dimension vs multiple dimension arrays.
        // count([1,2]) = 2, count([1,[2,3]]) = 2 with recursive it's 3. so if counts are different we have a
        // multi dimensional array
        if (count($saveActions) != count($saveActions, COUNT_RECURSIVE)) {
            foreach ($saveActions as $saveAction) {
                $this->addSaveAction($saveAction);
            }
        }
    }

    /**
     * Allow developers to register save action into CRUD.
     *
     * @param array $saveAction
     * @return void
     */
    public function addSaveAction(array $saveAction)
    {
        $orderCounter = $this->getOperationSetting('save_actions') !== null ? (count($this->getOperationSetting('save_actions')) + 1) : 1;
        //check for some mandatory fields
        $saveAction['name'] ?? abort(500, 'Please define save action name.');
        $saveAction['redirect'] = $saveAction['redirect'] ?? function ($crud, $request, $itemId) {
            return $request->has('http_referrer') ? $request->get('http_referrer') : $crud->route;
        };
        $saveAction['visible'] = $saveAction['visible'] ?? true;
        $saveAction['button_text'] = $saveAction['button_text'] ?? $saveAction['name'];
        $saveAction['order'] = isset($saveAction['order']) ? $this->orderSaveAction($saveAction['name'], $saveAction['order']) : $orderCounter;

        $actions = $this->getOperationSetting('save_actions') ?? [];

        if (! in_array($saveAction['name'], $actions)) {
            $actions[$saveAction['name']] = $saveAction;
        }

        $this->setOperationSetting('save_actions', $actions);
    }

    /**
     * Replaces setting order or forces some default.
     *
     * @param string $saveAction
     * @param int $wantedOrder
     * @return int
     */
    public function orderSaveAction(string $saveAction, int $wantedOrder)
    {
        $actions = $this->getOperationSetting('save_actions') ?? [];
        if (! empty($actions)) {
            $replaceOrder = isset($actions[$saveAction]) ? $actions[$saveAction]['order'] : count($actions) + 1;

            foreach ($actions as $key => $sv) {
                if ($wantedOrder == $sv['order']) {
                    $actions[$key]['order'] = $replaceOrder;
                }
                if ($key == $saveAction) {
                    $actions[$key]['order'] = $wantedOrder;
                }
            }
            $this->setOperationSetting('save_actions', $actions);
        }

        return $wantedOrder;
    }

    /**
     * Replace the current save actions with the ones provided.
     *
     * @param array $saveActions
     * @return void
     */
    public function replaceSaveActions($saveActions)
    {
        //we reset all save actions
        $this->setOperationSetting('save_actions', []);

        if (count($saveActions) != count($saveActions, COUNT_RECURSIVE)) {
            $this->addSaveActions($saveActions);
        } else {
            $this->addSaveAction($saveActions);
        }
    }

    /**
     * Alias function of replaceSaveActions() for CRUD consistency.
     *
     * @param array $saveActions
     * @return void
     */
    public function setSaveActions($saveActions)
    {
        return $this->replaceSaveActions($saveActions);
    }

    /**
     * Allow the developer to remove multiple save actions from settings.
     *
     * @param array $saveActions
     * @return void
     */
    public function removeSaveActions(array $saveActions)
    {
        foreach ($saveActions as $sv) {
            $this->removeSaveAction($sv);
        }
    }

    /**
     * Allow the developer to remove a save action from settings.
     *
     * @param string $saveAction
     * @return void
     */
    public function removeSaveAction(string $saveAction)
    {
        $actions = $this->getOperationSetting('save_actions') ?? [];
        if (isset($actions[$saveAction])) {
            $actions[$saveAction] = null;
        }
        $this->setOperationSetting('save_actions', array_filter($actions));
    }

    /**
     * Allow the developer to unset all save actions.
     *
     * @param string $saveAction
     * @return void
     */
    public function removeAllSaveActions()
    {
        $this->setOperationSetting('save_actions', []);
    }

    /**
     * Allows the developer to set save actions order. It could be ['action1','action2'] or ['action1' => 1, 'action2' => 2].
     *
     * @param array $saveActions
     * @return void
     */
    public function orderSaveActions(array $saveActions)
    {
        foreach ($saveActions as $sv => $order) {
            if (! is_int($order)) {
                $this->orderSaveAction($order, $sv + 1);
            } else {
                $this->orderSaveAction($sv, $order);
            }
        }
    }

    /**
     * Return the ordered save actions to use in the crud panel.
     *
     * @return void
     */
    public function getOrderedSaveActions()
    {
        $actions = $this->getOperationSetting('save_actions') ?? [];

        uasort($actions, function ($a, $b) {
            return $a['order'] <=> $b['order'];
        });

        return $actions;
    }

    /**
     * Returns the save actions that passed the visible callback.
     *
     * @return void
     */
    public function getVisibleSaveActions()
    {
        $actions = $this->getOrderedSaveActions();
        foreach ($actions as $actionName => $action) {
            $visible = $action['visible'];
            if (is_callable($visible)) {
                $actions[$actionName]['visible'] = $visible($this);
            }
        }

        return array_filter($actions, function ($action) {
            return $action['visible'] == true;
        }, ARRAY_FILTER_USE_BOTH);
    }

    /**
     * Gets the current save action for this crud.
     *
     * @param array $saveOptions
     * @return array
     */
    public function getCurrentSaveAction($saveOptions)
    {

        //get save action from session if exists, or get the developer defined order
        $saveAction = session($this->getCurrentOperation().'.saveAction', $this->getFallBackSaveAction());
        if (isset($saveOptions[$saveAction])) {
            $currentAction = $saveOptions[$saveAction];
        } else {
            $currentAction = Arr::first($saveOptions);
        }

        return [
            'value' => $currentAction['name'],
            'label' => $currentAction['button_text'],
        ];
    }

    /**
     * Here we check for save action visibility and prepare the actions array for display.
     *
     * @return array
     */
    public function getSaveAction()
    {
        //get only the save actions that pass visibility callback
        $saveOptions = $this->getVisibleSaveActions();

        //get the current action
        $saveCurrent = $this->getCurrentSaveAction($saveOptions);

        //get the dropdown options
        $dropdownOptions = [];
        foreach ($saveOptions as $key => $option) {
            if ($option['name'] != $saveCurrent['value']) {
                $dropdownOptions[$option['name']] = $option['button_text'];
            }
        }

        return [
            'active'  => $saveCurrent,
            'options' => $dropdownOptions,
        ];
    }

    /**
     * Change the session variable that remembers what to do after the "Save" action.
     *
     * @param string|null $forceSaveAction
     *
     * @return void
     */
    public function setSaveAction($forceSaveAction = null)
    {
        $saveAction = $forceSaveAction ?:
            \Request::input('save_action', $this->getFallBackSaveAction());

        $showBubble = $this->getOperationSetting('showSaveActionChange') ?? config('backpack.crud.operations.'.$this->getCurrentOperation().'.showSaveActionChange') ?? true;

        if (
            $showBubble &&
            session($this->getCurrentOperation().'.saveAction', 'save_and_back') !== $saveAction
        ) {
            \Alert::info(trans('backpack::crud.save_action_changed_notification'))->flash();
        }

        session([$this->getCurrentOperation().'.saveAction' => $saveAction]);
    }

    /**
     * Redirect to the correct URL, depending on which save action has been selected.
     *
     * @param string $itemId
     *
     * @return \Illuminate\Http\Response
     */
    public function performSaveAction($itemId = null)
    {
        $request = \Request::instance();
        $saveAction = $request->input('save_action', $this->getFallBackSaveAction());
        $itemId = $itemId ?: $request->input('id');
        $actions = $this->getOperationSetting('save_actions');

        if (isset($actions[$saveAction])) {
            if (is_callable($actions[$saveAction]['redirect'])) {
                $redirectUrl = $actions[$saveAction]['redirect']($this, $request, $itemId);
            }

            //allow the save action to define default http_referrer (url for the save_and_back button)
            if (isset($actions[$saveAction]['referrer_url'])) {
                if (is_callable($actions[$saveAction]['referrer_url'])) {
                    $referrer_url = $actions[$saveAction]['referrer_url']($this, $request, $itemId);
                }
            }
        }

        // if the request is AJAX, return a JSON response
        if ($this->getRequest()->ajax()) {
            return [
                'success'      => true,
                'data'         => $this->entry,
                'redirect_url' => $redirectUrl,
                'referrer_url' => $referrer_url ?? false,
            ];
        }

        if (isset($referrer_url)) {
            session()->flash('referrer_url_override', $referrer_url);
        }

        return \Redirect::to($redirectUrl);
    }

    /**
     * This functions register Backpack default save actions into CRUD.
     *
     * @return array
     */
    public function setupDefaultSaveActions()
    {
        $defaultSaveActions = [
            [
                'name' => 'save_and_back',
                'visible' => function ($crud) {
                    return $crud->hasAccess('list');
                },
                'redirect' => function ($crud, $request, $itemId = null) {
                    return $request->has('http_referrer') ? $request->get('http_referrer') : $crud->route;
                },
                'button_text' => trans('backpack::crud.save_action_save_and_back'),
            ],
            [
                'name' => 'save_and_edit',
                'visible' => function ($crud) {
                    return $crud->hasAccess('update');
                },
                'redirect' => function ($crud, $request, $itemId = null) {
                    $itemId = $itemId ?: $request->input('id');
                    $redirectUrl = $crud->route.'/'.$itemId.'/edit';
                    if ($request->has('locale')) {
                        $redirectUrl .= '?locale='.$request->input('locale');
                    }
                    if ($request->has('current_tab')) {
                        $redirectUrl = $redirectUrl.'#'.$request->get('current_tab');
                    }

                    return $redirectUrl;
                },
                'referrer_url' => function ($crud, $request, $itemId) {
                    return url($crud->route.'/'.$itemId.'/edit');
                },
                'button_text' => trans('backpack::crud.save_action_save_and_edit'),
            ],
            [
                'name' => 'save_and_new',
                'visible' => function ($crud) {
                    return $crud->hasAccess('create');
                },
                'redirect' => function ($crud, $request, $itemId = null) {
                    return $this->route.'/create';
                },
                'button_text' => trans('backpack::crud.save_action_save_and_new'),
            ],
        ];

        $this->addSaveActions($defaultSaveActions);
    }
}
