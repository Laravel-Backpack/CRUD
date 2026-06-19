# Columns

## About

A column shows the information of an Eloquent attribute, in a user-friendly format.

It's used inside default operations to:
- show a table cell in **ListEntries**;
- show an attribute value in **Show**;

A column consists of only one file - a blade file with the same name as the column type (ex: ```text.blade.php```). Backpack provides you with [default column types](#default-column-types) for the common use cases, but you can [change how a default field type works](#overwriting-default-column-types), or [create an entirely new field type](#creating-a-custom-column-type).

### Mandatory Attributes

When passing a column array, you need to specify at least these attributes:
```php
[
   'name' => 'options', // the db column name (attribute name)
   'label' => "Options", // the human-readable label for it
   'type' => 'text' // the kind of column to show
],
```

### Optional Attributes

- [```searchLogic```](#custom-search-logic)
- [```orderLogic```](#custom-order-logic)
- [```orderable```](#custom-order-logic)
- [```wrapper```](#custom-wrapper-for-columns)
- [```visibleInTable```](#choose-where-columns-are-visible)
- [```visibleInModal```](#choose-where-columns-are-visible)
- [```visibleInExport```](#choose-where-columns-are-visible)
- [```visibleInShow```](#choose-where-columns-are-visible)
- [```priority```](#define-which-columns-to-hide-in-responsive-table)
- [```escaped```](#escape-column-output)

### Columns API

Inside your ```setupListOperation()``` or ```setupShowOperation()``` method, there are a few calls you can make to configure or manipulate columns:

```php
// add a column, at the end of the stack
$this->crud->addColumn($column_definition_array);

// add multiple columns, at the end of the stack
$this->crud->addColumns([$column_definition_array, $another_column_definition_array]);

// to change the same attribute across multiple columns you can wrap them in a `group`
// this will add the '$' prefix to both columns
CRUD::group(
    CRUD::column('price'),
    CRUD::column('discount')
)->prefix('$');

// remove a column from the stack
$this->crud->removeColumn('column_name');

// remove an array of columns from the stack
$this->crud->removeColumns(['column_name_1', 'column_name_2']);

// change the attributes of a column
$this->crud->modifyColumn($name, $modifs_array);
$this->crud->setColumnDetails('column_name', ['attribute' => 'value']);

// change the attributes of multiple columns
$this->crud->setColumnsDetails(['column_1', 'column_2'], ['attribute' => 'value']);

// forget what columns have been previously defined, only use these columns
$this->crud->setColumns([$column_definition_array, $another_column_definition_array]);

// -------------------
// New in Backpack 4.1
// -------------------
// add a column with this name
$this->crud->column('price');

// change the type and prefix attributes on the 'price' column
$this->crud->column('price')->type('number')->prefix('$');
```

In addition, to manipulate the order columns are shown in, you can:

```php
// add this column before a given column
$this->crud->addColumn('text')->beforeColumn('name');

// add this column after a given column
$this->crud->addColumn()->afterColumn('name');

// make this column the first one in the list
$this->crud->addColumn()->makeFirstColumn();
```
