# CRUD API Cheat Sheet

---

Here are all the functions you will be using **inside your EntityCrudController's ```setup()``` method**, grouped by the operation you will most likely use them for.

## Operations

<a name="list-entries-api"></a>
### List

<a name="columns-api"></a>
#### Columns

<small>Methods: column(), addColumn(), addColumns(), modifyColumn(), removeColumn(), removeColumns(), setColumnDetails(), setColumnsDetails(), setColumns(), beforeColumn(), afterColumn(), makeFirstColumn()</small>

```php
// Adding and configuring columns:
CRUD::column('name')->someOtherAttribute($value)->anotherAttribute($anotherValue);  // add a column, at the end of the stack
CRUD::column($column_definition_array)->someChainedAttribute($value); // add a column, at the end of the stack

// Changing columns
CRUD::column('target')->attributeName($attributeValue); // change column attribute value
CRUD::column('target')->remove(); // remove column
CRUD::columns('target')->forget('someAttribute');

// Reordering columns:
CRUD::column('target')->before('destination'); // move target column before destination column
CRUD::column('target')->after('destination');  // move target column after destination column
CRUD::column('target')->makeFirst();
CRUD::column('target')->makeLast();

// Bulk actions on columns:
CRUD::addColumns([$column_definition_array, $another_column_definition_array]); // add multiple columns, at the end of the stack
CRUD::removeColumns(['column_name_1', 'column_name_2']); // remove an array of columns from the stack
CRUD::setColumns(['name', 'description']); // set the columns you want in the table view, as array of column names
CRUD::setColumns([$firstColumnDefinitionArray, $secondColumnDefinitionArray]); // set the columns to be exactly these
CRUD::setColumnsDetails(['column_1', 'column_2'], ['attribute' => 'value']); // change attributes for multiple columns at once
```

<a name="buttons-api"></a>
#### Buttons

<small>Methods: button(), buttons(), addButton(), addButtonFromModelFunction(), addButtonFromView(), removeButton(), removeButtonFromStack(), removeButtons(), removeAllButtons(), removeAllButtonsFromStack(), modifyButton(), moveButton()</small>

```php
// possible stacks: 'top', 'line', 'bottom';
// possible positions: 'beginning' and 'end'; defaults to 'beginning' for the 'line' stack, 'end' for the others;
// possible types: 'view', 'model_function'

// Adding buttons:
CRUD::button('name');
CRUD::button('name')->stack('line')->position('end')->type('view')->content('path.to.blade.file');

CRUD::addButtonFromModelFunction($stack, $name, $model_function_name, $position); // its HTML is returned by a method in the CRUD model
CRUD::addButtonFromView($stack, $name, $view, $position); // its HTML is in a view placed at resources\views\vendor\backpack\crud\buttons

// changing buttons
CRUD::button('name')->remove();
CRUD::button('name')->remove();
CRUD::button('name')->before('destination');
CRUD::button('name')->after('destination');
CRUD::button('name')->makeFirst();
CRUD::button('name')->makeLast();
CRUD::button('name')->forget('someAttribute');

// bulk actions on buttons
CRUD::buttons(); // get a collection of all buttons
CRUD::orderButtons($stack, $order); // order is an array with button names in the new order
CRUD::removeButtons($names, $stack);
CRUD::removeAllButtons();
CRUD::removeAllButtonsFromStack($stack);

// supported for backwards-compatibility:
CRUD::addButton($stack, $name, $type, $content, $position);
CRUD::modifyButton($name, $modifications); // modifications are the attributes and their new values
CRUD::moveButton($target, $where, $destination); // move the target button to the destination position, target and destion are the button names, where is 'before' or 'after'
CRUD::removeButton($name);
CRUD::removeButtonFromStack($name, $stack);
```

<a name="filters-api"></a>
#### Filters

<small>Methods: addFilter(), modifyFilter(), removeFilter(), removeAllFilters(), filters()</small>

```php
// Add filters
CRUD::filter('active')
    ->type('simple')
    ->whenActive(function ($value) {
        $this->crud->addClause('where', 'active', '1');
    })->else(function ($value) {
        $this->crud->addClause('where', 'active', '0');
    });

// alternative syntax, manually applied right away
CRUD::filter('active')
    ->type('simple')
    ->logic(function ($value) {
        $this->crud->addClause('where', 'active', '1');
    })->fallbackLogic(function ($value) {
        $this->crud->addClause('where', 'active', '0');
    })->apply();

// changing filters:
CRUD::filter('active')->remove();
CRUD::filter('active')->forget('attributeName');
CRUD::filter('active')->attributeName($newValue);

// reordering filters
CRUD::filter('active')->before('destination');
CRUD::filter('active')->after('destination');
CRUD::filter('active')->makeFirst();
CRUD::filter('active')->makeLast();

// bulk filter methods
CRUD::filters(); // gets all the filters
CRUD::removeAllFilters();

// other methods for backwards compatibility
// Note: check out CRUD > Features > Filters in the docs to see examples of $filter_definition_array
CRUD::addFilter($filter_definition_array, $values, $filter_logic);
CRUD::modifyFilter($name, $modifs_array);
CRUD::removeFilter($name);
```

<a name="details-row-api"></a>
#### Details Row

<small>Methods: enableDetailsRow(), disableDetailsRow()</small>

```php
// Shows a + sign next to each table row, so that the user can expand that row and reveal details. You are responsible for creating the view with those details.
CRUD::enableDetailsRow();
// NOTE: you also need to do allow access to the right users: CRUD::allowAccess('details_row');
// NOTE: you also need to do overwrite the showDetailsRow($id) method in your EntityCrudController to show whatever you'd like in the details row OR overwrite the views/backpack/crud/details_row.blade.php

CRUD::disableDetailsRow();
```

<a name="export-buttons-api"></a>
#### Export Buttons

<small>Methods: enableExportButtons()</small>

```php
// Show export to PDF, CSV, XLS and Print buttons on the table view. Please note it will only export the current _page_ of results. So in order to export all entries the user needs to make the current page show "All" entries from the top-left picker.
CRUD::enableExportButtons();
```

<a name="responsive-table-api"></a>
#### Responsive Table

<small>Methods: enableResponsiveTable(), disableResponsiveTable()</small>

```php
CRUD::disableResponsiveTable();
CRUD::enableResponsiveTable();
```

<a name="persistent-table-api"></a>
#### Persistent Table

<small>Methods: enablePersistentTable(), disablePersistentTable()</small>

```php
CRUD::disablePersistentTable();
CRUD::enablePersistentTable();
```

<a name="page-length"></a>
#### Page Length

<small>Methods: setDefaultPageLength(), setPageLengthMenu()</small>

```php
// you can define the default page length. If it does not exist we will add it to the pagination array.
CRUD::setDefaultPageLength(10);

// you can configure the paginator shown to the user in various ways

// values and labels, 1st array the values, 2nd array the labels:
CRUD::setPageLengthMenu([[100, 200, 300], ['one hundred', 'two hundred', 'three hundred']]);

// values and labels in one array:
CRUD::setPageLengthMenu([100 => 'one hundred', 200 => 'two hundred', 300 => 'three hundred']);

// only values, we will use the values as labels:
CRUD::setPageLengthMenu([100, 200, 300]); // OR
CRUD::setPageLengthMenu([[100, 200, 300]]);

// only one option available:
CRUD::setPageLengthMenu(10);
```

<small>NOTE: Do not use 0 as a key, if you want to represent "ALL" use -1 instead.</small>

<a name="action-column-api"></a>
#### Actions Column

<small>Methods: setActionsColumnPriority()</small>

```php
// make the actions column (in the table view) hide when not enough space is available, by giving it an unreasonable priority
CRUD::setActionsColumnPriority(10000);
```

<a name="custom-queries-api"></a>
#### Custom / Advanced Queries

<small>Methods: addClause(), groupBy(), limit(), orderBy()</small>

```php
// Change what entries are shown in the table view.
// This changes all queries on the table view,
// as opposed to filters, who only change it when that filter is applied.
CRUD::addClause('active'); // apply local scope
CRUD::addClause('type', 'car'); // apply local dynamic scope
CRUD::addClause('where', 'name', '=', 'car');
CRUD::addClause('whereName', 'car');
CRUD::addClause('whereHas', 'posts', function($query) {
     $query->activePosts();
 });
CRUD::groupBy();
CRUD::limit();

CRUD::orderBy();
// please note it's generally a good idea to use crud->orderBy() inside "if (!CRUD::getRequest()->has('order')) {}"; that way, your custom order is applied ONLY IF the user hasn't forced another order (by clicking a column heading)
```

<a name="show-api"></a>
### Show

Use the same Columns API as for the ListEntries operation, but inside your ```show()``` method.

<a name="create-and-update-api"></a>
### Create & Update Operations

<small>Methods: field(), addField(), addFields(), modifyField(), modifyFields(), removeField(), removeFields(), removeAllFields(), beforeField(), afterField() </small>

```php
// Adding and configuring form fields:
CRUD::field($field_name)->someOtherAttribute($value)->anotherAttribute($another_value);  // add a field to the form
CRUD::field($field_definition_array)->someChainedAttribute($value); // add a field to the form

// Changing fields:
CRUD::field('name')->remove();
CRUD::field('name')->attributeName($newValue); // change the value of a field attribute
CRUD::field('name')->size(6); // shorthand for changing the CSS classes on the field (will set col-md-6 so the max is 12)
CRUD::field('name')->forget('someAttribute');

// Reordering fields:
CRUD::field('name')->before('destination'); // will show this before the given field
CRUD::field('name')->after('destination'); // will show this after the given field
CRUD::field('name')->makeFirst();
CRUD::field('name')->makeLast();

// Bulk actions on fields:
CRUD::addFields($array_of_fields_definition_arrays);
CRUD::removeFields($array_of_names);
CRUD::removeAllFields();
```

<a name="reorder-api"></a>
### Reorder

<small>Methods: enableReorder(), disableReorder(), isReorderEnabled()</small>

```php
    protected function setupReorderOperation()
    {
    	// model attribute to be shown on draggable items
        CRUD::set('reorder.label', 'name');
        // maximum number of nesting allowed
        CRUD::set('reorder.max_level', 2);

        // extras:
		CRUD::disableReorder();
		CRUD::isReorderEnabled();
    }
```

<a name="revisions-api"></a>
### Revisions

```php
// -------------------------
// REVISIONS aka Audit Trail
// -------------------------
// Tracks all changes to an entry and provides an interface to revert to a previous state.
//
// IMPORTANT: You also need to use \Venturecraft\Revisionable\RevisionableTrait;
// Please check out: https://backpackforlaravel.com/docs/crud-operation-revisions
CRUD::allowAccess('revisions');
```

<a name="all-operations-api"></a>
## All Operations

<small>Methods: allowAccess(), denyAccess(), hasAccess(), setAccessCondition(), hasAccessOrFail(), hasAccessToAll(), hasAccessToAny(), setShowView(), setEditView(), setCreateView(), setListView(), setReorderView(), setRevisionsView, setRevisionsTimelineView(), setDetailsRowView(), getEntry(), getFields(), getColumns(), getCurrentEntry(), getTitle(), setTitle(), getHeading(), setHeading(), getSubheading(), setSubheading(), </small>

```php
// ------
// ACCESS
// ------
// Prevent or allow users from accessing different CRUD operations.

CRUD::allowAccess('list');
CRUD::allowAccess(['list', 'create', 'delete']);
CRUD::denyAccess('list');
CRUD::denyAccess(['list', 'create', 'delete']);
CRUD::setAccessCondition(['update', 'delete'], function ($entry) {
    return backpack_user()->isSuperAdmin() ? true : false;
});

CRUD::hasAccess('add'); // returns true/false
CRUD::hasAccessOrFail('add'); // throws 403 error
CRUD::hasAccessToAll(['create', 'update']); // returns true/false
CRUD::hasAccessToAny(['create', 'update']); // returns true/false

// -------------
// EAGER LOADING
// -------------

// eager load a relationship
CRUD::with('relationship_name');

// ------------
// CUSTOM VIEWS
// ------------

// use a custom view for a CRUD operation
CRUD::setShowView('your-view');
CRUD::setEditView('your-view');
CRUD::setCreateView('your-view');
CRUD::setListView('your-view');
CRUD::setReorderView('your-view');
CRUD::setRevisionsView('your-view');
CRUD::setRevisionsTimelineView('your-view');
CRUD::setDetailsRowView('your-view');

// -------------
// CONTENT CLASS
// -------------

// use a custom CSS class for the content of a CRUD operation
CRUD::setShowContentClass('col-md-12');
CRUD::setEditContentClass('col-md-12');
CRUD::setCreateContentClass('col-md-12');
CRUD::setListContentClass('col-md-12');
CRUD::setReorderContentClass('col-md-12');
CRUD::setRevisionsContentClass('col-md-12');
CRUD::setRevisionsTimelineContentClass('col-md-12');

// -------
// GETTERS
// -------

CRUD::getEntry($entry_id);
CRUD::getEntries();

CRUD::getFields('create/update/both');

// in your update() method, after calling parent::updateCrud()
CRUD::getCurrentEntry();

// -------
// OPERATIONS
// -------

CRUD::setOperation('list');
CRUD::getOperation();

// -------
// ACTIONS
// -------

CRUD::getActionMethod(); // returns the method on the controller that was called by the route; ex: create(), update(), edit() etc;
CRUD::actionIs('create'); // checks if the controller method given is the one called by the route

CRUD::getTitle('create'); // get the Title for the create action
CRUD::getHeading('create'); // get the Heading for the create action
CRUD::getSubheading('create'); // get the Subheading for the create action

CRUD::setTitle('some string', 'create'); // set the Title for the create action
CRUD::setHeading('some string', 'create'); // set the Heading for the create action
CRUD::setSubheading('some string', 'create'); // set the Subheading for the create action

// ---------------------------
// CrudPanel Basic Information
// ---------------------------
CRUD::setModel("App\Models\Example");
CRUD::setRoute("admin/example");
// OR CRUD::setRouteName("admin.example");
CRUD::setEntityNameStrings("example", "examples");

// check the FormRequests used in that EntityCrudController for required fields, and add an asterisk to them in the create/edit form
CRUD::setRequiredFields(StoreRequest::class, 'create');
CRUD::setRequiredFields(UpdateRequest::class, 'edit');
```
