<?php

namespace Backpack\CRUD\app\Library\CrudPanel\Traits;

trait WarnBeforeLeaving
{
    /**
     * Here we check if user should be warned before leaving the page.
     *
     * @return bool
     */
    public function getWarnBeforeLeaving(): bool
    {
        return $this->getOperationSetting('warnBeforeLeaving') ?? config('backpack.crud.operations.'.$this->getCurrentOperation().'.warnBeforeLeaving') ?? false;
    }

    /**
     * Change the variable that determines if user should be warned before leaving the page.
     *
     * @param bool $value
     * @return void
     */
    public function setWarnBeforeLeaving(bool $value = true): void
    {
        $this->setOperationSetting('warnBeforeLeaving', $value);
    }
}
