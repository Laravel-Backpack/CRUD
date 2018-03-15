<?php

namespace Backpack\CRUD;

use Carbon\Carbon;
use Backpack\CRUD\PanelTraits\Read;
use Backpack\CRUD\PanelTraits\Tabs;
use Backpack\CRUD\PanelTraits\Query;
use Backpack\CRUD\PanelTraits\Views;
use Backpack\CRUD\PanelTraits\Access;
use Backpack\CRUD\PanelTraits\Create;
use Backpack\CRUD\PanelTraits\Delete;
use Backpack\CRUD\PanelTraits\Errors;
use Backpack\CRUD\PanelTraits\Fields;
use Backpack\CRUD\PanelTraits\Search;
use Backpack\CRUD\PanelTraits\Update;
use Backpack\CRUD\PanelTraits\AutoSet;
use Backpack\CRUD\PanelTraits\Buttons;
use Backpack\CRUD\PanelTraits\Columns;
use Backpack\CRUD\PanelTraits\Filters;
use Backpack\CRUD\PanelTraits\Reorder;
use Backpack\CRUD\PanelTraits\AutoFocus;
use Backpack\CRUD\PanelTraits\FakeFields;
use Backpack\CRUD\PanelTraits\FakeColumns;
use Backpack\CRUD\PanelTraits\ViewsAndRestoresRevisions;

class CrudPanel
{
    use Create, Read, Search, Update, Delete, Errors, Reorder, Access, Columns, Fields, Query, Buttons, AutoSet, FakeFields, FakeColumns, ViewsAndRestoresRevisions, AutoFocus, Filters, Tabs, Views;

    // --------------
    // CRUD variables
    // --------------
    // These variables are passed to the CRUD views, inside the $crud variable.
    // All variables are public, so they can be modified from your EntityCrudController.
    // All functions and methods are also public, so they can be used in your EntityCrudController to modify these variables.

    // TODO: translate $entity_name and $entity_name_plural by default, with english fallback

    public $model = "\App\Models\Entity"; // what's the namespace for your entity's model
    public $route; // what route have you defined for your entity? used for links.
    public $entity_name = 'entry'; // what name will show up on the buttons, in singural (ex: Add entity)
    public $entity_name_plural = 'entries'; // what name will show up on the buttons, in plural (ex: Delete 5 entities)
    public $request;

    public $access = ['list', 'create', 'update', 'delete'/* 'revisions', reorder', 'show', 'details_row' */];

    public $reorder = false;
    public $reorder_label = false;
    public $reorder_max_level = 3;

    public $details_row = false;
    public $export_buttons = false;

    public $columns = []; // Define the columns for the table view as an array;
    public $create_fields = []; // Define the fields for the "Add new entry" view as an array;
    public $update_fields = []; // Define the fields for the "Edit entry" view as an array;

    public $query;
    public $entry;
    public $buttons;
    public $db_column_types = [];
    public $default_page_length = false;
    public $page_length_menu = false;

    // TONE FIELDS - TODO: find out what he did with them, replicate or delete
    public $sort = [];

    private $excel_filename = null;

    // The following methods are used in CrudController or your EntityCrudController to manipulate the variables above.
    public function __construct()
    {
        $this->setErrorDefaults();
        $this->initButtons();
    }

    // ------------------------------------------------------
    // BASICS - model, route, entity_name, entity_name_plural
    // ------------------------------------------------------

    /**
     * This function binds the CRUD to its corresponding Model (which extends Eloquent).
     * All Create-Read-Update-Delete operations are done using that Eloquent Collection.
     *
     * @param string $model_namespace Full model namespace. Ex: App\Models\Article]
     *
     * @throws \Exception in case the model does not exist
     */
    public function setModel($model_namespace)
    {
        if (! class_exists($model_namespace)) {
            throw new \Exception('This model does not exist.', 404);
        }

        $this->model = new $model_namespace();
        $this->query = $this->model->select('*');
        $this->entry = null;
    }

    /**
     * Get the corresponding Eloquent Model for the CrudController, as defined with the setModel() function;.
     *
     * @return [Eloquent Collection]
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * Get the database connection, as specified in the .env file or overwritten by the property on the model.
     */
    private function getSchema()
    {
        return \Schema::setConnection($this->getModel()->getConnection());
    }

    /**
     * Set the route for this CRUD.
     * Ex: admin/article.
     *
     * @param [string] Route name.
     * @param [array] Parameters.
     */
    public function setRoute($route)
    {
        $this->route = $route;
    }

    /**
     * Set the route for this CRUD using the route name.
     * Ex: admin.article.
     *
     * @param [string] Route name.
     * @param [array] Parameters.
     */
    public function setRouteName($route, $parameters = [])
    {
        $complete_route = $route.'.index';

        if (! \Route::has($complete_route)) {
            throw new \Exception('There are no routes for this route name.', 404);
        }

        $this->route = route($complete_route, $parameters);
        $this->initButtons();
    }

    /**
     * Get the current CrudController route.
     *
     * Can be defined in the CrudController with:
     * - $this->crud->setRoute(config('backpack.base.route_prefix').'/article')
     * - $this->crud->setRouteName(config('backpack.base.route_prefix').'.article')
     * - $this->crud->route = config('backpack.base.route_prefix')."/article"
     *
     * @return [string]
     */
    public function getRoute()
    {
        return $this->route;
    }

    /**
     * Set the entity name in singular and plural.
     * Used all over the CRUD interface (header, add button, reorder button, breadcrumbs).
     *
     * @param [string] Entity name, in singular. Ex: article
     * @param [string] Entity name, in plural. Ex: articles
     */
    public function setEntityNameStrings($singular, $plural)
    {
        $this->entity_name = $singular;
        $this->entity_name_plural = $plural;
    }

    // ----------------------------------
    // Miscellaneous functions or methods
    // ----------------------------------

    /**
     * Return the first element in an array that has the given 'type' attribute.
     *
     * @param string $type
     * @param array $array
     *
     * @return array
     */
    public function getFirstOfItsTypeInArray($type, $array)
    {
        return array_first($array, function ($item) use ($type) {
            return $item['type'] == $type;
        });
    }

    // ------------
    // TONE FUNCTIONS - UNDOCUMENTED, UNTESTED, SOME MAY BE USED IN THIS FILE
    // ------------
    //
    // TODO:
    // - figure out if they are really needed
    // - comments inside the function to explain how they work
    // - write docblock for them
    // - place in the correct section above (CREATE, READ, UPDATE, DELETE, ACCESS, MANIPULATION)

    public function sync($type, $fields, $attributes)
    {
        if (! empty($this->{$type})) {
            $this->{$type} = array_map(function ($field) use ($fields, $attributes) {
                if (in_array($field['name'], (array) $fields)) {
                    $field = array_merge($field, $attributes);
                }

                return $field;
            }, $this->{$type});
        }
    }

    /**
     * @deprecated No longer used by internal code and not recommended.
     */
    public function setSort($items, $order)
    {
        $this->sort[$items] = $order;
    }

    /**
     * @deprecated No longer used by internal code and not recommended.
     */
    public function sort($items)
    {
        if (array_key_exists($items, $this->sort)) {
            $elements = [];

            foreach ($this->sort[$items] as $item) {
                if (is_numeric($key = array_search($item, array_column($this->{$items}, 'name')))) {
                    $elements[] = $this->{$items}[$key];
                }
            }

            return $this->{$items} = array_merge($elements, array_filter($this->{$items}, function ($item) use ($items) {
                return ! in_array($item['name'], $this->sort[$items]);
            }));
        }

        return $this->{$items};
    }

    /**
     * Get the Eloquent Model name from the given relation definition string.
     *
     * @example For a given string 'company' and a relation between App/Models/User and App/Models/Company, defined by a
     *          company() method on the user model, the 'App/Models/Company' string will be returned.
     *
     * @example For a given string 'company.address' and a relation between App/Models/User, App/Models/Company and
     *          App/Models/Address defined by a company() method on the user model and an address() method on the
     *          company model, the 'App/Models/Address' string will be returned.
     *
     * @param $relationString String Relation string. A dot notation can be used to chain multiple relations.
     *
     * @return string relation model name
     */
    private function getRelationModel($relationString)
    {
        $result = array_reduce(explode('.', $relationString), function ($obj, $method) {
            return $obj->$method()->getRelated();
        }, $this->model);

        return get_class($result);
    }

    /**
     * Enables server side export button in filters bar.
     *
     * @param string|null $filename
     */
    public function enableServerSideExport(string $filename = null)
    {
        $this->excel_filename = $filename;

        $this->addFilter([
            'type' => 'export',
            'name' => 'exel_export',
            'label' => 'Exel Export',
        ]);
    }

    public function getExcelFileName()
    {
        return ucfirst(
            (empty($this->excel_filename) ? $this->entity_name_plural : $this->excel_filename)
            .'-'.Carbon::now()->toDateString()
        );
    }
}
