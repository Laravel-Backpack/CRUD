<?php

namespace Backpack\CRUD\app\Library\CrudPanel\Traits;

use Backpack\CRUD\app\Library\CrudPanel\SaveActions\SaveActionInterface;
use Backpack\CRUD\app\Library\CrudPanel\SaveActions\SaveAndBack;
use Backpack\CRUD\app\Library\CrudPanel\SaveActions\SaveAndEdit;
use Backpack\CRUD\app\Library\CrudPanel\SaveActions\SaveAndNew;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Redirect;
use InvalidArgumentException;
use Prologue\Alerts\Facades\Alert;

trait SaveActions
{
    /**
     * Get the developer's preference on what save action is the default one
     * for the current operation.
     */
    public function getSaveActionDefaultForCurrentOperation(): string
    {
        return config('backpack.crud.operations.'.$this->getCurrentOperation().'.defaultSaveAction', 'save_and_back');
    }

    /**
     * Get the save action with full fallback until default.
     */
    public function getFallBackSaveAction(): string
    {
        $higherAction = $this->getSaveActionByOrder(1);

        if (! empty($higherAction) && key($higherAction) !== 'save_and_back') {
            return key($higherAction);
        }

        if ($this->hasOperationSetting('defaultSaveAction')) {
            return $this->getOperationSetting('defaultSaveAction');
        }

        return $this->getSaveActionDefaultForCurrentOperation();
    }

    /**
     * Gets the save action that has the desired order.
     */
    public function getSaveActionByOrder($order): array
    {
        return array_filter($this->getOperationSetting('save_actions') ?? [], function ($arr) use ($order) {
            return ($arr['order'] ?? null) == $order;
        });
    }

    /**
     * Allow the developer to register multiple save actions.
     *
     * @param  array|SaveActionInterface|string  $saveActions
     */
    public function addSaveActions($saveActions)
    {
        if ($saveActions instanceof SaveActionInterface || is_string($saveActions)) {
            $this->addSaveAction($saveActions);

            return;
        }

        if (! is_array($saveActions)) {
            throw new InvalidArgumentException('Save actions must be provided as an array, class name, or instance implementing '.SaveActionInterface::class.'.');
        }

        if ($this->isSingleSaveActionArray($saveActions)) {
            $this->addSaveAction($saveActions);

            return;
        }

        foreach ($saveActions as $key => $saveAction) {
            if (is_array($saveAction) && ! isset($saveAction['name']) && is_string($key)) {
                $saveAction['name'] = $key;
            }

            $this->addSaveAction($saveAction);
        }
    }

    /**
     * Allow developers to register save action into CRUD.
     *
     * @param  array|SaveActionInterface|string  $saveAction
     */
    public function addSaveAction($saveAction)
    {
        $saveAction = $this->prepareSaveActionDefinition($saveAction);

        $orderCounter = $this->getOperationSetting('save_actions') !== null ? (count($this->getOperationSetting('save_actions')) + 1) : 1;
        $saveAction['name'] ?? abort(500, 'Please define save action name.', ['developer-error-exception']);
        $saveAction['redirect'] = $saveAction['redirect'] ?? fn ($crud, $request, $itemId) => $request->has('_http_referrer') ? $request->get('_http_referrer') : $crud->route;
        $saveAction['visible'] = $saveAction['visible'] ?? true;
        $saveAction['button_text'] = $saveAction['button_text'] ?? $saveAction['name'];
        $saveAction['order'] = isset($saveAction['order']) ? $this->orderSaveAction($saveAction['name'], $saveAction['order']) : $orderCounter;

        if (isset($saveAction['_handler']) && $saveAction['_handler'] instanceof SaveActionInterface) {
            $saveAction['_handler']->setOrder((int) $saveAction['order']);
        }

        $actions = $this->getOperationSetting('save_actions') ?? [];

        if (! array_key_exists($saveAction['name'], $actions)) {
            $actions[$saveAction['name']] = $saveAction;
        }

        $this->setOperationSetting('save_actions', $actions);
    }

    /**
     * Replaces setting order or forces some default.
     */
    public function orderSaveAction(string $saveAction, int $wantedOrder)
    {
        $actions = $this->getOperationSetting('save_actions') ?? [];
        if (! empty($actions)) {
            $replaceOrder = isset($actions[$saveAction]['order']) ? $actions[$saveAction]['order'] : count($actions) + 1;

            foreach ($actions as $key => $sv) {
                if (($sv['order'] ?? null) == $wantedOrder) {
                    $actions[$key]['order'] = $replaceOrder;
                    if (isset($actions[$key]['_handler']) && $actions[$key]['_handler'] instanceof SaveActionInterface) {
                        $actions[$key]['_handler']->setOrder((int) $replaceOrder);
                    }
                }
                if ($key == $saveAction) {
                    $actions[$key]['order'] = $wantedOrder;
                    if (isset($actions[$key]['_handler']) && $actions[$key]['_handler'] instanceof SaveActionInterface) {
                        $actions[$key]['_handler']->setOrder((int) $wantedOrder);
                    }
                }
            }
            $this->setOperationSetting('save_actions', $actions);
        }

        return $wantedOrder;
    }

    /**
     * Replace the current save actions with the ones provided.
     *
     * @param  array|SaveActionInterface|string  $saveActions
     */
    public function replaceSaveActions($saveActions)
    {
        $this->setOperationSetting('save_actions', []);

        if ($saveActions === null || $saveActions === []) {
            return;
        }

        $this->addSaveActions($saveActions);
    }

    /**
     * Alias function of replaceSaveActions() for CRUD consistency.
     */
    public function setSaveActions($saveActions)
    {
        return $this->replaceSaveActions($saveActions);
    }

    /**
     * Allow the developer to remove multiple save actions from settings.
     */
    public function removeSaveActions(array $saveActions)
    {
        foreach ($saveActions as $sv) {
            $this->removeSaveAction($sv);
        }
    }

    /**
     * Allow the developer to remove a save action from settings.
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
     */
    public function removeAllSaveActions()
    {
        $this->setOperationSetting('save_actions', []);
    }

    /**
     * Allows the developer to set save actions order. It could be ['action1','action2'] or ['action1' => 1, 'action2' => 2].
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
     */
    public function getOrderedSaveActions()
    {
        $actions = $this->getOperationSetting('save_actions') ?? [];

        uasort($actions, function ($a, $b) {
            return ($a['order'] ?? PHP_INT_MAX) <=> ($b['order'] ?? PHP_INT_MAX);
        });

        return $actions;
    }

    /**
     * Returns the save actions that passed the visible callback.
     */
    public function getVisibleSaveActions()
    {
        $actions = $this->getOrderedSaveActions();
        foreach ($actions as $actionName => $action) {
            $visible = $action['visible'];
            if ($visible instanceof \Closure) {
                $actions[$actionName]['visible'] = $visible($this);
            } elseif (is_array($visible) && is_callable($visible)) {
                $actions[$actionName]['visible'] = call_user_func($visible, $this);
            }
        }

        return array_filter($actions, function ($action) {
            return $action['visible'] == true;
        }, ARRAY_FILTER_USE_BOTH);
    }

    /**
     * Gets the current save action for this crud.
     */
    public function getCurrentSaveAction($saveOptions)
    {
        $saveAction = session($this->getCurrentOperation().'.saveAction', $this->getFallBackSaveAction());
        if (isset($saveOptions[$saveAction])) {
            $currentAction = $saveOptions[$saveAction];
        } else {
            $currentAction = Arr::first($saveOptions);
        }

        return [
            'value' => $currentAction['name'] ?? null,
            'label' => $currentAction['button_text'] ?? null,
        ];
    }

    /**
     * Here we check for save action visibility and prepare the actions array for display.
     */
    public function getSaveAction()
    {
        $saveOptions = $this->getVisibleSaveActions();

        if (empty($saveOptions)) {
            return [];
        }

        $saveCurrent = $this->getCurrentSaveAction($saveOptions);

        if ($saveCurrent['value'] === null) {
            return [];
        }

        $dropdownOptions = [];
        foreach ($saveOptions as $key => $option) {
            if (($option['name'] ?? null) != $saveCurrent['value']) {
                $dropdownOptions[$option['name']] = $option['button_text'];
            }
        }

        return [
            'active' => $saveCurrent,
            'options' => $dropdownOptions,
        ];
    }

    /**
     * Change the session variable that remembers what to do after the "Save" action.
     */
    public function setSaveAction($forceSaveAction = null)
    {
        $saveAction = $forceSaveAction ?:
            $this->getRequest()->input('_save_action', $this->getFallBackSaveAction());

        $showBubble = $this->getOperationSetting('showSaveActionChange') ?? config('backpack.crud.operations.'.$this->getCurrentOperation().'.showSaveActionChange') ?? true;

        if (
            $showBubble &&
            session($this->getCurrentOperation().'.saveAction', 'save_and_back') !== $saveAction
        ) {
            Alert::info(trans('backpack::crud.save_action_changed_notification'))->flash();
        }

        session([$this->getCurrentOperation().'.saveAction' => $saveAction]);
    }

    /**
     * Redirect to the correct URL, depending on which save action has been selected.
     */
    public function performSaveAction($itemId = null)
    {
        $request = $this->getRequest();
        $saveAction = $request->input('_save_action', $this->getFallBackSaveAction());
        $itemId = $itemId ?: $request->input('id');
        $actions = $this->getOperationSetting('save_actions');
        $redirectUrl = $this->route;
        $referrer_url = null;

        if (isset($actions[$saveAction])) {
            if ($actions[$saveAction]['redirect'] instanceof \Closure) {
                $redirectUrl = $actions[$saveAction]['redirect']($this, $request, $itemId);
            } elseif (is_array($actions[$saveAction]['redirect']) && is_callable($actions[$saveAction]['redirect'])) {
                $redirectUrl = call_user_func($actions[$saveAction]['redirect'], $this, $request, $itemId);
            }

            if (isset($actions[$saveAction]['referrer_url'])) {
                if ($actions[$saveAction]['referrer_url'] instanceof \Closure) {
                    $referrer_url = $actions[$saveAction]['referrer_url']($this, $request, $itemId);
                } elseif (is_array($actions[$saveAction]['referrer_url']) && is_callable($actions[$saveAction]['referrer_url'])) {
                    $referrer_url = call_user_func($actions[$saveAction]['referrer_url'], $this, $request, $itemId);
                } else {
                    $referrer_url = $actions[$saveAction]['referrer_url'];
                }
            }
        }

        if ($this->getRequest()->ajax()) {
            return response()->json([
                'success' => true,
                'data' => $this->entry,
                'redirect_url' => $redirectUrl,
                'referrer_url' => $referrer_url ?? false,
            ]);
        }

        if ($referrer_url !== null) {
            session()->flash('referrer_url_override', $referrer_url);
        }

        if ($redirectUrl === null) {
            $redirectUrl = $this->route ?? url('/');
        }

        return Redirect::to($redirectUrl);
    }

    /**
     * This functions register Backpack default save actions into CRUD.
     */
    public function setupDefaultSaveActions()
    {
        $this->addSaveActions([
            new SaveAndBack(),
            new SaveAndEdit(),
            new SaveAndNew(),
        ]);
    }

    protected function prepareSaveActionDefinition($saveAction): array
    {
        if ($saveAction instanceof SaveActionInterface) {
            return $saveAction->toArray();
        }

        if (is_string($saveAction)) {
            if (! class_exists($saveAction)) {
                throw new InvalidArgumentException(sprintf('Save action class [%s] does not exist.', $saveAction));
            }

            $instance = app()->make($saveAction);

            if (! $instance instanceof SaveActionInterface) {
                throw new InvalidArgumentException(sprintf('Save action class [%s] must implement %s.', $saveAction, SaveActionInterface::class));
            }

            return $instance->toArray();
        }

        if (! is_array($saveAction)) {
            throw new InvalidArgumentException('Save action definition must be a class name, array, or SaveActionInterface instance.');
        }

        if (isset($saveAction['order'])) {
            $saveAction['order'] = $saveAction['order'] !== null ? (int) $saveAction['order'] : null;
        }

        return $saveAction;
    }

    protected function isSingleSaveActionArray(array $saveActions): bool
    {
        if (! Arr::isAssoc($saveActions)) {
            return false;
        }

        return array_key_exists('name', $saveActions);
    }
}
