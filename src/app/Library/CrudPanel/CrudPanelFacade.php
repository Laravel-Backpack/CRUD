<?php

namespace Backpack\CRUD\app\Library\CrudPanel;

use Illuminate\Support\Facades\Facade;

/**
 * This object allows developers to use CRUD::addField() instead of $this->crud->addField(),
 * by providing a Facade that leads to the CrudPanel object. That object is stored in Laravel's
 * service container as 'crud'.
 */
/**
 * @codeCoverageIgnore
 * Class CrudPanelFacade.
 *
 * @method static CrudPanel setModel($model)
 * @method static CrudPanel setRoute(string $route)
 * @method static CrudPanel setEntityNameStrings(string $singular, string $plural)
 * @method static CrudField field(string $name)
 * @method static CrudPanel addField(array $field)
 * @method static CrudPanel addFields(array $fields)
 * @method static CrudColumn column(string $name)
 * @method static CrudPanel addColumn(array $column)
 * @method static CrudPanel addColumns(array $columns)
 * @method static CrudPanel afterColumn(string $targetColumn)
 * @method static CrudPanel setValidation($classOrRulesArray = false, $messages = [], $attributes = [])
 *
 * @mixin CrudPanel
 */
class CrudPanelFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'crud';
    }
}
