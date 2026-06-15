# CRUD API

---

Here are all the features you will be using **inside your EntityCrudController**, grouped by the operation you will most likely use them for.

## Operations

- **operation()** - allows you to add a set of instructions inside ```setup()```, that only gets called when a certain operation is being performed; 
```php
public function setup() {
	// ...
	$this->crud->operation('list', function() {
		$this->crud->addColumn('name');
	});
}
```

<a name="list-entries-api"></a>
### List Operation

<a name="columns-api"></a>
#### Columns

Manipulate what columns are shown in the table view.

- **addColumn()** - add a column, at the end of the stack
```php
$this->crud->addColumn($column_definition_array); 
```

- **addColumns()** - add multiple columns, at the end of the stack
```php
$this->crud->addColumns([$column_definition_array, $another_column_definition_array]); 
```

- **modifyColumn()** - change column attributes
```php
$this->crud->modifyColumn($name, $modifs_array);
```

- **removeColumn()** - remove one column from all operations
```php
$this->crud->removeColumn('column_name');
```

- **removeColumns()** - remove multiple columns from all operations
```php
$this->crud->removeColumns(['column_name_1', 'column_name_2']); // remove an array of columns from the stack
```

- **setColumnDetails()** - change the attributes of one column; alias of ```modifyColumn()```;
```php
$this->crud->setColumnDetails('column_name', ['attribute' => 'value']);
```

- **setColumnsDetails()** - change the attributes of multiple columns; alias of ```modifyColumn()```;
```php
$this->crud->setColumnsDetails(['column_1', 'column_2'], ['attribute' => 'value']);
```

- **setColumns()** - remove previously set columns and only use the ones give now;
```php
$this->crud->setColumns();
// sets the columns you want in the table view, either as array of column names, or multidimensional array with all columns detailed with their types
```

- **Chained - beforeColumn()** - insert current column _before_ the given column
```php
// ------ REORDER COLUMNS
$this->crud->addColumn()->beforeColumn('name');
```

- **Chained - afterColumn()** - insert current column _after_ the given column
```php
$this->crud->addColumn()->afterColumn('name');
```

- **Chained - makeFirstColumn()** - make this column the first one in the list
```php
$this->crud->addColumn()->makeFirstColumn();
// Please note: you need to also specify priority 1 in your addColumn statement for details_row or responsive expand buttons to show
```

<a name="buttons-api"></a>
#### Buttons

- **addButton()** - add a button in the given stack
```php
$this->crud->addButton($stack, $name, $type, $content, $position); 
// stacks: top, line, bottom
// types: view, model_function
// positions: beginning, end (defaults to 'beginning' for the 'line' stack, 'end' for the others);
```

- **addButtonFromModelFunction()** - add a button whose HTML is returned by a method in the CRUD model
```php
$this->crud->addButtonFromModelFunction($stack, $name, $model_function_name, $position);
```

- **addButtonFromView()** - add a button whose HTML is in a view placed at ```resources\views\vendor\backpack\crud\buttons```
```php
$this->crud->addButtonFromView($stack, $name, $view, $position);
```

- **modifyButton()** - modify the attributes of a button
```php
$this->crud->modifyButton($name, $modifications);
```

- **removeButton()** - remove a button from whatever stack it's in
```php
$this->crud->removeButton($name); // remove a single button
$this->crud->removeButtons($names); // or multiple
```

- **removeButtonFromStack()** - remove a button from a particular stack
```php
$this->crud->removeButtonFromStack($name, $stack);
```

- **removeAllButtons()** - remove all buttons from any stack
```php
$this->crud->removeAllButtons();
```

- **removeAllButtonsFromStack()** - remove all buttons from a particular stack
```php
$this->crud->removeAllButtonsFromStack($stack);
```

<a name="filters-api"></a>
#### Filters

Manipulate what filters are shown in the table view. Check out [CRUD > Operations > ListEntries > Filters](/docs/{{version}}/crud-filters) to see examples of ```$filter_definition_array```

- **addFilter()** - add a filter to the list view
```php
$this->crud->addFilter($filter_definition_array, $values, $filter_logic);
```

- **modifyFilter()** - change the attributes of a filter
```php
$this->crud->modifyFilter($name, $modifs_array);
```

- **removeFilter()** - remove a certain filter from the list view
```php
$this->crud->removeFilter($name);
```

- **removeAllFilters()** - remove all filters from the list view
```php
$this->crud->removeAllFilters();
```

- **filters()** - get all the registered filters for the list view
```php
$this->crud->filters();
```

<a name="details-row-api"></a>
#### Details Row

Shows a ```+``` (plus sign) next to each table row, so that the user can expand that row and reveal details. You are responsible for creating the view with those details.

- **enableDetailsRow()** - show the + sign in the table view
```php
$this->crud->enableDetailsRow();
// NOTE: you also need to do allow access to the right users:
$this->crud->allowAccess('details_row');
// NOTE: you also need to do overwrite the showDetailsRow($id) method in your EntityCrudController to show whatever you'd like in the details row OR overwrite the views/backpack/crud/details_row.blade.php
$this->crud->setDetailsRowView('your-view');
```

- **disableDetailsRow()** - hide the + sign in the table view
```php
$this->crud->disableDetailsRow();
```

<a name="export-buttons-api"></a>
#### Export Buttons

Please note it will only export the current _page_ of results. So in order to export all entries the user needs to make the current page show "All" entries from the top-left picker.

- **enableExportButtons()** - Show export to PDF, CSV, XLS and Print buttons on the table view
```php
$this->crud->enableExportButtons();
```

<a name="responsive-table-api"></a>
#### Responsive Table

- **disableResponsiveTable()** - stop the listEntries view from showing/hiding columns depending on viewport width
```php
$this->crud->disableResponsiveTable();
```

- **enableResponsiveTable()** - make the listEntries view show/hide columns depending on viewport width
```php
$this->crud->enableResponsiveTable();
```

<a name="persistent-table-api"></a>
#### Persistent Table

- **enablePersistentTable()** - make the listEntries remember the filters, search and pagination for a user, even if he leaves the page, for 2 hours
```php
$this->crud->enablePersistentTable();
```

- **disablePersistentTable()** - stop the listEntries from remembering the filters, search and pagination for a user, even if he leaves the page
```php
$this->crud->disablePersistentTable();
```

<a name="page-length"></a>
#### Page Length

- **setDefaultPageLength()** - change the number of items per page in the list view
```php
$this->crud->setDefaultPageLength(10);
```

- **setPageLengthMenu()** - change the entire page length menu in the list view
```php
$this->crud->setPageLengthMenu([100, 200, 300]);
```

<a name="action-column-api"></a>
#### Actions Column

- **setActionsColumnPriority()** - make the actions column (in the table view) hide when not enough space is available, by giving it an unreasonable priority
```php
$this->crud->setActionsColumnPriority(10000);
```

<a name="custom-queries-api"></a>
#### Custom / Advanced Queries

- **addClause()** - change what entries are shown in the table view; this allows _developers_ to forcibly change the query used by the table view, as opposed to filters, that allow _users_ to change the query with new inputs;
```php
$this->crud->addClause('active'); // apply local scope
$this->crud->addClause('type', 'car'); // apply local dynamic scope
$this->crud->addClause('where', 'name', '=', 'car');
$this->crud->addClause('whereName', 'car');
$this->crud->addClause('whereHas', 'posts', function($query) {
     $query->activePosts();
 });
```

- **groupBy()** - shorthand to add a **groupBy** clause to the query
```php
$this->crud->groupBy();
```

- **limit()** - shorthand to add a **limit** clause to the query
```php
$this->crud->limit();
```

- **orderBy()** - shorthand to add an **orderBy** clause to the query
```php
$this->crud->orderBy();
```
- **setQuery(Builder $query)** - replaces the query with the new provided query.
```php
$this->crud->setQuery(User::where('status', 'active'));
```

<a name="show-api"></a>
### Show Operation

Use [the same Columns API as for the ListEntries operation](#columns-api), but inside your ```show()``` method.

<a name="create-and-update-api"></a>
### Create & Update Operations

Manipulate what fields are shown in the create / update forms. Check out [CRUD > Operations > Create & Update > Fields](/docs/{{version}}/crud-fields) in the docs to see examples of ```$field_definition_array```.

**Note:** The call is being performed for the current operation. So it's important to pay attention _where_ you're calling fields. Most likely, you'll want to do this inside ```setupCreateOperation()``` or ```setupUpdateOperation()```.

- **addField()** - add one field
```php
$this->crud->addField($field_definition_array);
$this->crud->addField('db_column_name'); // a lazy way to add fields: let the CRUD decide what field type it is and set it automatically, along with the field label
```

- **addFields()** - add multiple fields
```php
$this->crud->addFields($array_of_fields_definition_arrays);
```

- **modifyField()** - change the attributes of an existing field
```php
$this->crud->modifyField($name, $modifs_array);
```

- **removeField()** - remove a given field from the current operation
```php
$this->crud->removeField('name');
```

- **removeFields()** - remove multiple fields from the current operation
```php
$this->crud->removeFields($array_of_names);
```

- **removeAllFields()** - remove all registered fields
```php
$this->crud->removeAllFields();
```
- **Chained - beforeField()** - add a field _before_ a given field
```php
$this->crud->addField()->beforeField('name');
```

- **Chained - afterField()** - add a field _after_ a given field
```php
$this->crud->addField()->afterField('name');
```

- **setRequiredFields()** - check the FormRequests used in this EntityCrudController for required fields, and add an asterisk to them in the create or edit forms
```php
$this->crud->setRequiredFields(StoreRequest::class);
```

- **setValidation()** - makes sure validation and authorization in the FormRequest you've passed is being performed; also uses that file to figure out asterisk to show in the forms (calls ```setRequiredFields()``` above):
```php
$this->crud->setValidation(ArticleRequest::class);
```

<a name="reorder-api"></a>
### Reorder Operation

Show a reorder button in the table view, next to Add. Provides an interface to reorder & nest elements, provided the ```parent_id```, ```lft```, ```rgt```, ```depth``` columns are in the database, and ```$fillable``` on the model.

```php
$this->crud->set('reorder.label', 'name'); // which model attribute to use for labels
$this->crud->set('reorder.max_level', 3); // maximum nesting depth; this example will prevent the user from creating trees deeper than 3 levels;
```

- **disableReorder()** - disable the Reorder functionality
```php
$this->crud->disableReorder();
```

- **isReorderEnabled()** - returns ```true```/```false``` if the Reorder operation is enabled or not
```php
$this->crud->isReorderEnabled();
```

<a name="revisions-api"></a>
### Revise Operation

A.k.a. Audit Trail. Tracks all changes to an entry and provides an interface to revert to a previous state. This operation is not installed by default - please check out [Revise Operation](/docs/{{version}}/crud-operation-revisions) for the installation & usage steps.

<a name="all-operations-api"></a>
## All Operations

### Access

Prevent or allow users from accessing different CRUD operations.

- **allowAccess()** - give users access to one or multiple operations
```php
$this->crud->allowAccess('list');
$this->crud->allowAccess(['list', 'create', 'delete']);
```

- **allowAccessOnlyTo()** - give users access only to one or some operations, denying the rest of them
```php
$this->crud->allowAccessOnlyTo('list');
$this->crud->allowAccessOnlyTo(['list', 'create', 'delete']);
```

- **denyAccess()** - prevent users from accessing one or multiple operations
```php
$this->crud->denyAccess('list');
$this->crud->denyAccess(['list', 'create', 'delete']);
```

- **denyAllAccess()** - prevent users from accessing all operations (you may allow some operations then)
```php
$this->crud->denyAllAccess();
```

- **hasAccess()** - check if the current user has access to one or multiple operations
```php
$this->crud->hasAccess('something'); // returns true/false
$this->crud->hasAccessOrFail('something'); // throws 403 error
$this->crud->hasAccessToAll(['create', 'update']); // returns true/false
$this->crud->hasAccessToAny(['create', 'update']); // returns true/false
```

### Eager Loading Relationships

- **with()** - when the current entry is loaded (in any operation) also get its relationships, so that only one query is made to the database per entry
```php
$this->crud->with('relationship_name');
```

### Custom Views

- **setShowView()**, **setEditView()**, **setCreateView()**, **setListView()**, **setReorderView()**, **setRevisionsView()**, **setRevisionsTimelineView()**, **setDetailsRowView()** - set the view for a certain CRUD operation or feature

```php
// use a custom view for a CRUD operation
$this->crud->setShowView('path.to.your.view');
$this->crud->setEditView('path.to.your.view');
$this->crud->setCreateView('path.to.your.view');
$this->crud->setListView('path.to.your.view');
$this->crud->setReorderView('path.to.your.view');
$this->crud->setRevisionsView('path.to.your.view');
$this->crud->setRevisionsTimelineView('path.to.your.view');
$this->crud->setDetailsRowView('path.to.your.view');

// more generally, you can use the Settings API:
$this->crud->set('create.view', 'path.to.your.view');

// if you want to load something from the /resources/vendor/backpack/crud directory, you can do
$this->crud->set('create.view', 'crud::yourfolder.yourview');
// or
$this->crud->set('create.view', 'resources.vendor.backpack.crud.yourfolder.yourview');
```

### Content Class

- **setShowContentClass()**, **setEditContentClass()**, **setCreateContentClass()**, **setListContentClass()**, **setReorderContentClass()**, **setRevisionsContentClass()**, **setRevisionsTimelineContentClass()** - set the CSS class for an operation view, to make the main area bigger or smaller:

```php
// use a custom view for a CRUD operation
$this->crud->setShowContentClass('col-md-8');
$this->crud->setEditContentClass('col-md-8');
$this->crud->setCreateContentClass('col-md-8');
$this->crud->setListContentClass('col-md-8');
$this->crud->setReorderContentClass('col-md-8');
$this->crud->setRevisionsContentClass('col-md-8');
$this->crud->setRevisionsTimelineContentClass('col-md-8');

// more generally, you can use the Settings API:
$this->crud->set('create.contentClass', 'col-md-12');
```

### Getters

- **getEntry()** - get a certain entry of the current model type
```php
$this->crud->getEntry($entry_id);
```
- **getEntries()** - get all entries using the current CRUD query
```php
$this->crud->getEntries();
```

- **getFields()** - get all fields for a certain operation, or for both
```php
$this->crud->getFields('create/update/both');
```

- **getCurrentEntry()** - get the current entry, for operations that work on a single entry
```php
$this->crud->getCurrentEntry();
// ex: in your update() method, after calling parent::updateCrud()
```

### Operations

- **getOperation()** - get the name of the operation that is currently being performed
```php
$this->crud->getOperation();
```

- **setOperation()** - set the name of the operation that is currently being performed
```php
$this->crud->setOperation('ListEntries');
```

### Actions

An action is the controller method that is currently being run.

- **getActionMethod()** - returns the method on the controller that was called by the route; ex: ```create()```, ```update()```, ```edit()``` etc;
```php
$this->crud->getActionMethod();
```

- **actionIs()** - checks if the given controller method is the one called by the route
```php
$this->crud->actionIs('create');
```

### Title, Heading, Subheading

Legend:
- _operation_ - a collection of functions in a CrudController, that together allow the admin to perform something on the current model;
- _action_ - a method (aka function) of an operation; it is the actual PHP function's name;

- **getTitle()** -  get the Title for the create action
```php
$this->crud->getTitle('create');
```

- **getHeading()** - get the Heading for the create action
```php
$this->crud->getHeading('create');
```

- **getSubheading()** - get the Subheading for the create action
```php
$this->crud->getSubheading('create');
```

- **setTitle()** -  set the Title for the create action
```php
$this->crud->setTitle('some string', 'create');
```

- **setHeading()** -  set the Heading for the create action
```php
$this->crud->setHeading('some string', 'create');
```

- **setSubheading()** -  set the Subheading for the create action
```php
$this->crud->setSubheading('some string', 'create');
```

### CrudPanel Basic Info

- **setModel()** - set the Eloquent object that should be used for all operations
```php
$this->crud->setModel("App\Models\Example");
```

- **setRoute()** - set the main route to this CRUD
```php
$this->crud->setRoute("admin/example");
// OR $this->crud->setRouteName("admin.example");
```

- **setEntityNameStrings()** - set how the entity name should be shown to the user, in singular and in plural
```php
$this->crud->setEntityNameStrings("example", "examples");
```
