<?php

namespace Backpack\CRUD\app\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class FiltersNavbar extends Component
{
    public $crud;
    /**
     * Create a new component instance.
     */
    public function __construct($crud)
    {
        $this->crud = $crud;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return backpack_view('components.filters-navbar');
    }
}
