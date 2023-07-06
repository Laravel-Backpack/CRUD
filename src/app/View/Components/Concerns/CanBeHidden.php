<?php

namespace Backpack\CRUD\app\View\Components\Concerns;

trait CanBeHidden
{
    /**
     * Should this component output its HTML?
     *
     * @return bool
     */
    public function isHidden(): bool
    {
        if ($this->hidden instanceof Closure) {
            return $this->hidden();
        }

        return $this->hidden;
    }

    /**
     * Should this component ouput its HTML?
     *
     * @return bool
     */
    public function isVisible(): bool
    {
        return ! $this->isHidden();
    }
}
