## Overwriting Default Column Types

You can overwrite a column type by placing a file with the same name in your ```resources\views\vendor\backpack\crud\columns``` directory. When a file is there, Backpack will pick that one up, instead of the one in the package. You can do that from command line using ```php artisan backpack:column --from=column-file-name```

Examples:
- creating a ```resources\views\vendor\backpack\crud\columns\number.blade.php``` file would overwrite the ```number``` column functionality;
- ```php artisan backpack:column --from=text``` will take the view from the package and copy it to the directory above, so you can edit it;

>Keep in mind that when you're overwriting a default column type, you're forfeiting any future updates for that column. We can't push updates to a file that you're no longer using.

## Creating a Custom Column Type

Columns consist of only one file - a blade file with the same name as the column type (ex: ```text.blade.php```). You can create one by placing a new blade file inside ```resources\views\vendor\backpack\crud\columns```. Be careful to choose a distinctive name, otherwise you might be overwriting a default column type (see above).

For example, you can create a ```markdown.blade.php```:
```php
<span>{!! \Markdown::convertToHtml($entry->{$column['name']}) !!}</span>
```

The most useful variables you'll have in this file here are:
- ```$entry``` - the database entry you're showing (Eloquent object);
- ```$crud``` - the entire CrudPanel object, with settings, options and variables;

By default, custom columns are not searchable. To make your column searchable you need to [specify a custom ```searchLogic``` in your declaration](#custom-search-logic).

## Advanced Columns Use

### Custom Search Logic for Columns

If your column points to something atypical (not a value that is stored as plain text in the database column, maybe a model function, or a JSON, or something else), you might find that the search doesn't work for that column. You can choose which columns are searchable, and what those columns actually search, by using the column's ```searchLogic``` attribute:

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

// column that doesn't need to be searchable
$this->crud->addColumn([
    'name'        => 'slug_or_title',
    'label'       => 'Title',
    'searchLogic' => false
]);

// column whose search logic should behave like it were a 'text' column type
$this->crud->addColumn([
    'name'        => 'slug_or_title',
    'label'       => 'Title',
    'searchLogic' => 'text'
]);
```

### Custom Order Logic for Columns

If your column points to something atypical (not a value that is stored as plain text in the database column, maybe a model function, or a JSON, or something else), you might find that the ordering doesn't work for that column. You can choose which columns are orderable, and how those columns actually get ordered, by using the column's ```orderLogic``` attribute.

For example, to order Articles not by its Category ID (as default, but by the Category Name), you can do:

```php
$this->crud->addColumn([
    // Select
   'label'      => 'Category',
   'type'       => 'select',
   'name'       => 'category_id', // the db column for the foreign key
   'entity'     => 'category', // the method that defines the relationship in your Model
   'attribute'  => 'name', // foreign key attribute that is shown to user
   'orderable'  => true,
   'orderLogic' => function ($query, $column, $columnDirection) {
        return $query->leftJoin('categories', 'categories.id', '=', 'articles.category_id')
            ->orderBy('categories.name', $columnDirection)->select('articles.*');
    }
]);
```

### Wrap Column Text in an HTML Element

Sometimes the text that the column echoes is not enough. You want to add interactivity to it, by adding a link to that column. Or you want to show the value in a green/yellow/red badge so it stands out. You can do both of that - with the ```wrapper``` attribute, which most columns support.

```php
$this->crud->column([
  // Select
  'label'     => 'Category',
  'type'      => 'select',
  'name'      => 'category_id', // the db column for the foreign key
  'entity'    => 'category', // the method that defines the relationship in your Model
  'attribute' => 'name', // foreign key attribute that is shown to user
  'wrapper'   => [
      // 'element' => 'a', // the element will default to "a" so you can skip it here
      'href' => function ($crud, $column, $entry, $related_key) {
          return backpack_url('article/'.$related_key.'/show');
      },
      // 'target' => '_blank',
      // 'class' => 'some-class',
  ],
]);
```

If you specify ```wrapper``` to a column, the entries in that column will be wrapped in the element you specify. Note that:
- To get an HTML anchor (a link), you can specify ```a``` for the element (but that's also the default); to get a paragraph you'd specify ```p``` for the element; to get an inline element you'd specify ```span``` for the element; etc;
- Anything you declare in the ```wrapper``` array (other than ```element```) will be used as HTML attributes for that element (ex: ```class```, ```style```, ```target``` etc);
- Each wrapper attribute, including the element itself, can be declared as a `string` OR as a `callback`;

Example: wrap a boolean column into a green/red span:

```php
$this->crud->column([
    'name'    => 'published',
    'label'   => 'Published',
    'type'    => 'boolean',
    'options' => [0 => 'No', 1 => 'Yes'], // optional
    'wrapper' => [
        'element' => 'span',
        'class' => function ($crud, $column, $entry, $related_key) {
            if ($column['text'] == 'Yes') {
                return 'badge badge-success';
            }

            return 'badge badge-default';
        },
    ],
]);
```

### Link Column To Route

To make a column link to a route URL, you can use the `linkTo($routeNameOrClosure, $parameters = [])` helper. Behind the scenes, this helper will use the `wrapper` helper to set up a link towards the route you want. See the section above for details on the `wrapper` helper.

Use `linkTo()` helper to point to a route name:
```php
// you can do:
$this->crud->column('category')->linkTo('category.show');

// instead of:
$this->crud->column('category')->wrapper([
    'href' => function ($crud, $column, $entry, $related_key) {
        return backpack_url('category/'.$related_key.'/show');
    },
]);

// or as a closure shortcut:
$this->crud->column('category')->linkTo(fn($entry, $related_key) => backpack_url('category/'.$related_key.'/show'));
```

You can also link to non-related urls, as long as the route has a name.

```php
$this->crud->column('my_column')->linkTo('my.route.name');

// you can also add additional parameters in your urls
$this->crud->column('my_column')->linkTo('my.route.name', ['myParameter' => 'value']);

// you can use the closure in the parameters too
$this->crud->column('my_column')
    ->linkTo('my.route.name', [
        'myParameter' => fn($entry, $related_key) => $entry->something ? 'value' : $related_key ?? 'fallback_value',
    ]);

// array syntax is also supported
$this->crud->column([
    'name' => 'category',
    // simple route name
    'linkTo' => 'category.show',

    // alternatively with additional parameters
    'linkTo' => [
        'route' => 'category.show',
        'parameters' => ['myParameter' => 'value'],
    ],

    // or as closure
    'linkTo' => fn($entry, $related_key) => route('category.show', ['id' => $related_key]),
]);
```

If you want to have it simple and just link to the show route, you can use the `linkToShow()` helper. 
It's just a shortcut for `linkTo('entity.show')`.

```php
$this->crud->column('category')
    ->linkToShow();
```

If you want to open the link in a new tab, you can use the `linkTarget()` helper.

```php
$this->crud->column('category')
    ->linkToShow()
    ->linkTarget('_blank');
```

For more complex use-cases, we recommend you use the `wrapper` attribute directly. It accepts an array of HTML attributes which will be applied to the column text. You can also use callbacks to generate the attributes dynamically.

### Choose Where Columns are Visible

Starting with Backpack\CRUD 3.5.0, you can choose to show/hide columns in different contexts. You can pass ```true``` / ```false``` to the column attributes below, and Backpack will know to show the column or not, in different contexts:

```php
$this->crud->addColumn([
   'name'            => 'description',
   'visibleInTable'  => false, // no point, since it's a large text
   'visibleInModal'  => false, // would make the modal too big
   'visibleInExport' => false, // not important enough
   'visibleInShow'   => true, // boolean or closure - function($entry) { return $entry->isAdmin(); }
]);
```

This also allows you to do tricky things like:
- add a column that's hidden from the table view, but WILL get exported;
- adding a column that's hidden everywhere, but searchable (even with a custom ```searchLogic```);

### Multiple Columns With the Same Name

Starting with Backpack\CRUD 3.3 (Nov 2017), you can have multiple columns with the same name, by specifying a unique ```key``` property. So if you want to use the same column name twice, you can do that. Notice below we have the same name for both columns, but one of them has a ```key```. This additional key will be used as an array key, if provided.

```php
// column that shows the parent's first name
$this->crud->addColumn([
   'label'     => 'Parent First Name', // Table column heading
   'type'      => 'select',
   'name'      => 'parent_id', // the column that contains the ID of that connected entity;
   'entity'    => 'parent', // the method that defines the relationship in your Model
   'attribute' => 'first_name', // foreign key attribute that is shown to user
   'model'     => 'App\Models\User', // foreign key model
]);

// column that shows the parent's last name
$this->crud->addColumn([
   'label'     => 'Parent Last Name', // Table column heading
   'type'      => 'select',
   'name'      => 'parent_id', // the column that contains the ID of that connected entity;
   'key'       => 'parent_last_name', // the column that contains the ID of that connected entity;
   'entity'    => 'parent', // the method that defines the relationship in your Model
   'attribute' => 'last_name', // foreign key attribute that is shown to user
   'model'     => 'App\Models\User', // foreign key model
]);
```

### Escape column output

For security purposes, Backpack escapes the output of all column types except for `markdown` and `custom_html` (those columns would be useless escaped). That means it uses `{{ }}` to echo the output, not `{!! !!}`. If you have any HTML inside a db column, it will be shown as HTML instead of interpreted. It does that because, if the value was added by a malicious user (not admin), it could contain malicious JS code.

However, if you trust that a certain column contains _safe_ HTML, you can disable this behaviour by setting the `escaped` attribute to `false`.

Our recommendation, to trust the output of a column, is to either:
- (a) only allow the admin to add/edit that column;
- (b) purify the value in an accessor on the Model, so that every time you get it, it's cleaned; you can use an [HTML Purifier package](https://github.com/mewebstudio/Purifier) for that (do it [manually](https://github.com/Laravel-Backpack/demo/commit/7342cffb418bb568b9e4ee279859685ddc0456c1) or by casting the attribute to `CleanHtmlOutput::class`);

### Define which columns to show or hide in the responsive table

By default, DataTables-responsive will try his best to show:
- **the first column** (since that usually is the most important for the user, plus it holds the modal button and the details_row button so it's crucial for usability);
- **the last column** (the actions column, where the action buttons reside);

When giving priorities, lower is better. So a column with priority 4 will be hidden BEFORE a column with priority 2. The first and last columns have a priority of 1. You can define a different priority for a column using the ```priority``` attribute. For example:

```php
$this->crud->addColumn([
    'name'     => 'details',
    'type'     => 'text',
    'label'    => 'Details',
    'priority' => 2,
]);
$this->crud->addColumn([
    'name'     => 'obs',
    'type'     => 'text',
    'label'    => 'Observations',
    'priority' => 3,
]);
```
In the example above, depending on how much space it's got in the viewport, DataTables will first hide the ```obs``` column, then ```details```, then the last column, then the first column.

You can make the last column be less important (and hide) by giving it an unreasonable priority:

```php
$this->crud->setActionsColumnPriority(10000);
```

>Note that responsive tables adopt special behavior if the table is not able to show all columns. This includes displaying a vertical ellipsis to the left of the row, and making the row clickable to reveal more detail. This behavior is automatic and is not manually controllable via a field property.

### Adding new methods to the CrudColumn class

You can add your own methods Backpack CRUD columns, so that you can do `CRUD::column('name')->customThing()`. You can do that, because the `CrudColumn` class is Macroable. It's as easy as this:

```php
use Backpack\CRUD\app\Library\CrudPanel\CrudColumn;

// register media upload macros on CRUD columns
if (! CrudColumn::hasMacro('customThing')) {
    CrudColumn::macro('customThing', function ($firstParamExample = [], $secondParamExample = null) {
        /** @var CrudColumn $this */

        // TODO: do anything you want to $this

        return $this;
    });
}
```

A good place to do this would be in your AppServiceProvider, in a custom service provider. That way you have it across all your CRUD panels.
