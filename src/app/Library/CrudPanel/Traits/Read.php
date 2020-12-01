<?php

namespace Backpack\CRUD\app\Library\CrudPanel\Traits;

/**
 * Properties and methods used by the List operation.
 */
trait Read
{
    /**
     * Find and retrieve the id of the current entry.
     *
     * @return int|bool The id in the db or false.
     */
    public function getCurrentEntryId()
    {
        if ($this->entry) {
            return $this->entry->getKey();
        }

        $params = \Route::current()->parameters();

        return  // use the entity name to get the current entry
                // this makes sure the ID is corrent even for nested resources
                $this->getRequest()->input($this->entity_name) ??
                // otherwise use the next to last parameter
                array_values($params)[count($params) - 1] ??
                // otherwise return false
                false;
    }

    /**
     * Find and retrieve the current entry.
     *
     * @return \Illuminate\Database\Eloquent\Model|bool The row in the db or false.
     */
    public function getCurrentEntry()
    {
        $id = $this->getCurrentEntryId();

        if (! $id) {
            return false;
        }

        return $this->getEntry($id);
    }

    /**
     * Find and retrieve an entry in the database or fail.
     *
     * @param int The id of the row in the db to fetch.
     *
     * @return \Illuminate\Database\Eloquent\Model The row in the db.
     */
    public function getEntry($id)
    {
        if (! $this->entry) {
            $this->entry = $this->model->findOrFail($id);
            $this->entry = $this->entry->withFakes();
        }

        return $this->entry;
    }

    /**
     * Find and retrieve an entry in the database or fail.
     *
     * @param int The id of the row in the db to fetch.
     *
     * @return \Illuminate\Database\Eloquent\Model The row in the db.
     */
    public function getEntryWithoutFakes($id)
    {
        return $this->model->findOrFail($id);
    }

    /**
     * Make the query JOIN all relationships used in the columns, too,
     * so there will be less database queries overall.
     */
    public function autoEagerLoadRelationshipColumns()
    {
        $relationships = $this->getColumnsRelationships();

        if (count($relationships)) {
            $this->with($relationships);
        }
    }

    /**
     * Get all entries from the database.
     *
     * @return array|\Illuminate\Database\Eloquent\Collection
     */
    public function getEntries()
    {
        $this->autoEagerLoadRelationshipColumns();

        $entries = $this->query->get();

        // add the fake columns for each entry
        foreach ($entries as $key => $entry) {
            $entry->addFakes($this->getFakeColumnsAsArray());
        }

        return $entries;
    }

    /**
     * Enable the DETAILS ROW functionality:.
     *
     * In the table view, show a plus sign next to each entry.
     * When clicking that plus sign, an AJAX call will bring whatever content you want from the EntityCrudController::showDetailsRow($id) and show it to the user.
     */
    public function enableDetailsRow()
    {
        $this->setOperationSetting('detailsRow', true);
    }

    /**
     * Disable the DETAILS ROW functionality:.
     */
    public function disableDetailsRow()
    {
        $this->setOperationSetting('detailsRow', false);
    }

    /**
     * Add two more columns at the beginning of the ListEntrie table:
     * - one shows the checkboxes needed for bulk actions
     * - one is blank, in order for evenual detailsRow or expand buttons
     * to be in a separate column.
     */
    public function enableBulkActions()
    {
        $this->setOperationSetting('bulkActions', true);
    }

    /**
     * Remove the two columns needed for bulk actions.
     */
    public function disableBulkActions()
    {
        $this->setOperationSetting('bulkActions', false);

        $this->removeColumn('bulk_actions');
        $this->removeColumn('blank_first_column');
    }

    /**
     * Set the number of rows that should be show on the list view.
     */
    public function setDefaultPageLength($value)
    {
        $this->setOperationSetting('defaultPageLength', $value);
    }

    /**
     * Get the number of rows that should be show on the list view.
     *
     * @return int
     */
    public function getDefaultPageLength()
    {
        return $this->getOperationSetting('defaultPageLength') ?? config('backpack.crud.operations.list.defaultPageLength') ?? 25;
    }

    /**
     * If a custom page length was specified as default, make sure it
     * also show up in the page length menu.
     */
    public function addCustomPageLengthToPageLengthMenu()
    {
        // If the default Page Length isn't in the menu's values, Add it the beginnin and resort all to show a croissant list.
        // assume both arrays are the same length.
        if (! in_array($this->getDefaultPageLength(), $this->getOperationSetting('pageLengthMenu')[0])) {
            // Loop through 2 arrays of prop. pageLengthMenu
            foreach ($this->getOperationSetting('pageLengthMenu') as $key => &$page_length_choices) {
                // This is a condition that should be always true.
                if (is_array($page_length_choices)) {
                    array_unshift($page_length_choices, $this->getDefaultPageLength());
                }
            }
        }
    }

    /**
     * Specify array of available page lengths on the list view.
     *
     * @param array $menu 1d array of page length values,
     *                    or 2d array (first array: page length values, second array: page length labels)
     *                    More at: https://datatables.net/reference/option/lengthMenu
     */
    public function setPageLengthMenu($menu)
    {
        $this->setOperationSetting('pageLengthMenu', $menu);
    }

    /**
     * Get page length menu for the list view.
     *
     * @return array
     */
    public function getPageLengthMenu()
    {
        // if we have a 2D array, update all the values in the right hand array to their translated values
        if (isset($this->getOperationSetting('pageLengthMenu')[1]) && is_array($this->getOperationSetting('pageLengthMenu')[1])) {
            $aux = $this->getOperationSetting('pageLengthMenu');
            foreach ($this->getOperationSetting('pageLengthMenu')[1] as $key => $val) {
                $aux[1][$key] = trans($val);
            }
            $this->setOperationSetting('pageLengthMenu', $aux);
        }

        $this->addCustomPageLengthToPageLengthMenu();

        return $this->getOperationSetting('pageLengthMenu');
    }

    /*
    |--------------------------------------------------------------------------
    |                                EXPORT BUTTONS
    |--------------------------------------------------------------------------
    */

    /**
     * Tell the list view to show the DataTables export buttons.
     */
    public function enableExportButtons()
    {
        $this->setOperationSetting('exportButtons', true);
    }

    /**
     * Check if export buttons are enabled for the table view.
     *
     * @return bool
     */
    public function exportButtons()
    {
        return $this->getOperationSetting('exportButtons') ?? false;
    }
}
