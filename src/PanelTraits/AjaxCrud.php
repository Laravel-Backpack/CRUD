<?php

namespace Backpack\CRUD\PanelTraits;

trait AjaxCrud
{
    /*
    |--------------------------------------------------------------------------
    | AJAX CRUD
    |--------------------------------------------------------------------------
    */

    /**
     * @var bool
     */
    public $ajax_crud = false;

    /**
     * Check if AJAX is enabled for crud.
     *
     * @return bool
     */
    public function ajaxCrud()
    {
        return $this->ajax_crud;
    }

    /**
     * Enable AJAX for crud functionality.
     */
    public function enableAjaxCrud()
    {
        $this->ajax_crud = true;
    }

    /**
     * Disable AJAX for crud functionality.
     */
    public function disableAjaxCrud()
    {
        $this->ajax_crud = false;
    }
}
