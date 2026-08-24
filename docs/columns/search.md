### search

The List operation comes with a search bar. By default, when the admin uses it:

- **text-like columns** (```text```, ```email```, ```textarea```) get searched using a simple ```LIKE '%term%'``` query on that database column;
- **date** and **datetime** columns get searched using ```orWhereDate()```, if the search term is a valid date;
- **select** and **select_multiple** columns get searched inside their related entity (using the relationship's attribute);
- **all other column types are not searchable by default** (```model_function```, ```view```, ```number```, ```boolean```, etc).

So if you want an ```email``` column to be searchable - no configuration is needed, it already is. If the search isn't working on a particular column though, you can define exactly what (and how) it should search - using a custom ```searchLogic```.

## Custom Search Logic for Columns

If your column points to something atypical (not a value that is stored as plain text in the database column - maybe a model function, a JSON, or something else), you might find that the search doesn't work for that column. You can choose which columns are searchable, and what those columns actually search, by using the column's ```searchLogic``` attribute:

```php
// column with custom search logic
$this->crud->addColumn([
    'name'        => 'slug_or_title',
    'label'       => 'Title',
    'searchLogic' => function ($query, $column, $searchTerm) {
        $query->orWhere('title', 'like', '%'.$searchTerm.'%');
    }
]);


// 1-n relationship column with custom search logic
$this->crud->addColumn([
    'label'       => 'Cruise Ship',
    'type'        => 'select',
    'name'        => 'cruise_ship_id',
    'entity'      => 'cruise_ship',
    'attribute'   => 'cruise_ship_name_date', // combined name & date column
    'model'       => 'App\Models\CruiseShip',
    'searchLogic' => function ($query, $column, $searchTerm) {
        $query->orWhereHas('cruise_ship', function ($q) use ($column, $searchTerm) {
            $q->where('name', 'like', '%'.$searchTerm.'%')
              ->orWhereDate('depart_at', '=', date($searchTerm));
        });
    }
]);
```

## How to Make a Column Searchable

If you need a column to be searchable, but it isn't by default (for example a ```model_function``` column that outputs an email address), add a ```searchLogic``` to its definition, pointing to the database column that should actually be searched:

```php
// model_function column that outputs an email - search the email db column
$this->crud->addColumn([
    'name'          => 'contact_email',
    'type'          => 'model_function',
    'function_name' => 'getContactEmailAttribute',
    'label'         => 'Email',
    'searchLogic'   => function ($query, $column, $searchTerm) {
        $query->orWhere('email', 'like', '%'.$searchTerm.'%');
    },
]);
```

## Disabling Search for a Column

If you want a column to not be searched at all (for example to keep the search query fast), use:

```php
$this->crud->addColumn([
    'name'        => 'email',
    'label'       => 'Email',
    'searchLogic' => false, // don't search this column
]);
```

## Searching Like Another Column Type

You can tell Backpack to search a column as if it were another type, by passing a string as ```searchLogic```:

```php
// column whose search logic should behave like it were a 'text' column type
$this->crud->addColumn([
    'name'        => 'slug_or_title',
    'label'       => 'Title',
    'searchLogic' => 'text',
]);
```

## Changing the Search Operator

By default, Backpack uses ```like``` to search text-like columns. You can change the operator used for all searches using the config option ```backpack.operations.list.searchOperator``` (defaults to ```like```):

```php
// in config/backpack/operations/list.php
'searchOperator' => 'ilike', // PostgreSQL case-insensitive search
```

## Fluent Syntax

You can also define custom search logic using the fluent syntax:

```php
CRUD::column('text_and_email')
    ->type('model_function')
    ->label('Text and Email')
    ->function_name('getTextAndEmailAttribute')
    ->searchLogic(function ($query, $column, $searchTerm) {
        $query->orWhere('email', 'like', '%'.$searchTerm.'%');
        $query->orWhere('text', 'like', '%'.$searchTerm.'%');
    });
```

>Note: the search only applies to the columns defined for the List operation. Columns not shown in the table view are not searched.
