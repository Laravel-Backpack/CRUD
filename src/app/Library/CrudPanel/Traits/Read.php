<?php

namespace Backpack\CRUD\app\Library\CrudPanel\Traits;

use Backpack\CRUD\app\Exceptions\BackpackProRequiredException;
use Exception;

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

        if ($id === false) {
            return false;
        }

        return $this->getEntry($id);
    }

    /**
     * Find and retrieve an entry in the database or fail.
     *
     * @param int The id of the row in the db to fetch.
     * @return \Illuminate\Database\Eloquent\Model The row in the db.
     */
    public function getEntry($id)
    {
        if (! $this->entry) {
            $this->entry = $this->getModelWithCrudPanelQuery()->findOrFail($id);
            $this->entry = $this->entry->withFakes();
        }

        return $this->entry;
    }

    /**
     * Find and retrieve an entry in the database or fail.
     * When found, make sure we set the Locale on it.
     *
     * @param int The id of the row in the db to fetch.
     * @return \Illuminate\Database\Eloquent\Model The row in the db.
     */
    public function getEntryWithLocale($id)
    {
        if (! $this->entry) {
            $this->entry = $this->getEntry($id);
        }

        if ($this->entry->translationEnabled()) {
            $locale = $this->getRequest()->input('_locale', app()->getLocale());
            if (in_array($locale, array_keys($this->entry->getAvailableLocales()))) {
                $this->entry->setLocale($locale);
                $this->entry->useFallbackLocale = $this->getRequest()->input('_use_fallback') ? true : false;
            }
        }

        return $this->entry;
    }

    /**
     * Return a Model builder instance with the current crud query applied.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getModelWithCrudPanelQuery()
    {
        return $this->model->setQuery($this->query->getQuery());
    }

    /**
     * Find and retrieve an entry in the database or fail.
     *
     * @param int The id of the row in the db to fetch.
     * @return \Illuminate\Database\Eloquent\Model The row in the db.
     */
    public function getEntryWithoutFakes($id)
    {
        return $this->getModelWithCrudPanelQuery()->findOrFail($id);
    }

    /**
     * Make the query JOIN all relationships used in the columns, too,
     * so there will be less database queries overall.
     */
    public function autoEagerLoadRelationshipColumns()
    {
        $relationships = $this->getColumnsRelationships();

        foreach ($relationships as $relation) {
            if (strpos($relation, '.') !== false) {
                $parts = explode('.', $relation);
                $model = $this->model;

                // Iterate over each relation part to find the valid relations without attributes
                // We should eager load the relation but not the attribute
                foreach ($parts as $i => $part) {
                    try {
                        $model = $model->$part()->getRelated();
                    } catch (Exception $e) {
                        $relation = join('.', array_slice($parts, 0, $i));
                    }
                }
            }
            $this->with($relation);
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
        if (! backpack_pro()) {
            throw new BackpackProRequiredException('Details row');
        }

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
    }

    /**
     * Set the number of rows that should be show on the list view.
     */
    public function setDefaultPageLength($value)
    {
        $this->abortIfInvalidPageLength($value);

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
        $values = $this->getOperationSetting('pageLengthMenu')[0];
        $labels = $this->getOperationSetting('pageLengthMenu')[1];

        if (array_search($this->getDefaultPageLength(), $values) === false) {
            for ($i = 0; $i < count($values); $i++) {
                if ($values[$i] > $this->getDefaultPageLength() || $values[$i] === -1) {
                    array_splice($values, $i, 0, $this->getDefaultPageLength());
                    array_splice($labels, $i, 0, $this->getDefaultPageLength());
                    break;
                }
                if ($i === count($values) - 1) {
                    $values[] = $this->getDefaultPageLength();
                    $labels[] = $this->getDefaultPageLength();
                    break;
                }
            }
        }

        $this->setOperationSetting('pageLengthMenu', [$values, $labels]);
    }

    /**
     * Specify array of available page lengths on the list view.
     *
     * @param  array|int  $menu
     *
     * https://backpackforlaravel.com/docs/4.1/crud-cheat-sheet#page-length
     */
    public function setPageLengthMenu($menu)
    {
        if (is_array($menu)) {
            // start checking $menu integrity
            if (count($menu) !== count($menu, COUNT_RECURSIVE)) {
                // developer defined as setPageLengthMenu([[50, 100, 300]]) or setPageLengthMenu([[50, 100, 300],['f','h','t']])
                // we will apply the same labels as the values to the menu if developer didn't
                $this->abortIfInvalidPageLength($menu[0]);

                if (! isset($menu[1]) || ! is_array($menu[1])) {
                    $menu[1] = $menu[0];
                }
            } else {
                // developer defined setPageLengthMenu([10 => 'f', 100 => 'h', 300 => 't']) OR setPageLengthMenu([50, 100, 300])
                $menu = $this->buildPageLengthMenuFromArray($menu);
            }
        } else {
            // developer added only a single value setPageLengthMenu(10)
            $this->abortIfInvalidPageLength($menu);

            $menu = [[$menu], [$menu]];
        }

        $this->setOperationSetting('pageLengthMenu', $menu);
    }

    /**
     * Builds the menu from the given array. It works out with two different types of arrays:
     *  [1, 2, 3] AND [1 => 'one', 2 => 'two', 3 => 'three'].
     *
     * @param  array  $menu
     * @return array
     */
    private function buildPageLengthMenuFromArray($menu)
    {
        // check if the values of the array are strings, in case developer defined:
        // setPageLengthMenu([0 => 'f', 100 => 'h', 300 => 't'])
        if (count(array_filter(array_values($menu), 'is_string')) > 0) {
            $values = array_keys($menu);
            $labels = array_values($menu);

            $this->abortIfInvalidPageLength($values);

            return [$values, $labels];
        } else {
            // developer defined length as setPageLengthMenu([50, 100, 300])
            // we will use the same values as labels
            $this->abortIfInvalidPageLength($menu);

            return [$menu, $menu];
        }
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

    /**
     * Checks if the provided PageLength segment is valid.
     *
     * @param  array|int  $value
     * @return void
     */
    private function abortIfInvalidPageLength($value)
    {
        if ($value === 0 || (is_array($value) && in_array(0, $value))) {
            abort(500, 'You should not use 0 as a key in paginator. If you are looking for "ALL" option, use -1 instead.');
        }
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
        if (! backpack_pro()) {
            throw new BackpackProRequiredException('Export buttons');
        }

        $this->setOperationSetting('exportButtons', true);
        $this->setOperationSetting('showTableColumnPicker', true);
        $this->setOperationSetting('showExportButton', true);
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
