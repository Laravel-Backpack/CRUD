<?php

namespace Backpack\CRUD\app\View\Components;

use Backpack\CRUD\app\Library\CrudPanel\CrudPanel;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class FiltersNavbar extends Component
{
    public CrudPanel $crud;
    public string $id;

    /**
     * Create a new component instance.
     */
    public function __construct($crud, $id = null)
    {
        $this->crud = $crud;
        $this->id = $id ?? 'filters-navbar-'.uniqid();
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return backpack_view('components.filters-navbar');
    }
}
