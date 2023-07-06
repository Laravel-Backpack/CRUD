<?php

namespace Backpack\CRUD\app\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class MenuSeparator extends Component
{
    use Concerns\CanBeHidden;

    /**
     * Create a new component instance.
     */
    public function __construct(
        public ?string $title = null,
        public $hidden = false,
    ) {
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        if ($this->isHidden()) {
            return '';
        }

        return backpack_view('components.menu-separator');
    }
}
