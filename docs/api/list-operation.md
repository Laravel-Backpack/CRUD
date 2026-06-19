### List Operation

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

#### Export Buttons

Please note it will only export the current _page_ of results. So to export all entries the user needs to make the current page show "All" entries from the top-left picker.

- **enableExportButtons()** - Show export to PDF, CSV, XLS and Print buttons on the table view
```php
$this->crud->enableExportButtons();
```

#### Responsive Table

- **disableResponsiveTable()** - stop the listEntries view from showing/hiding columns depending on viewport width
```php
$this->crud->disableResponsiveTable();
```

- **enableResponsiveTable()** - make the listEntries view show/hide columns depending on viewport width
```php
$this->crud->enableResponsiveTable();
```

#### Persistent Table

- **enablePersistentTable()** - make the listEntries remember the filters, search and pagination for a user, even if he leaves the page, for 2 hours
```php
$this->crud->enablePersistentTable();
```

- **disablePersistentTable()** - stop the listEntries from remembering the filters, search and pagination for a user, even if he leaves the page
```php
$this->crud->disablePersistentTable();
```

#### Page Length

- **setDefaultPageLength()** - change the number of items per page in the list view
```php
$this->crud->setDefaultPageLength(10);
```

- **setPageLengthMenu()** - change the entire page length menu in the list view
```php
$this->crud->setPageLengthMenu([100, 200, 300]);
```

#### Actions Column

- **setActionsColumnPriority()** - make the actions column (in the table view) hide when not enough space is available, by giving it an unreasonable priority
```php
$this->crud->setActionsColumnPriority(10000);
```

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
