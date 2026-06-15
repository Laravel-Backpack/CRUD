# Columns

---

<a name="about"></a>
## About

A column shows the information of an Eloquent attribute, in a user-friendly format.

It's used inside default operations to:
- show a table cell in **ListEntries**;
- show an attribute value in **Show**;

A column consists of only one file - a blade file with the same name as the column type (ex: ```text.blade.php```). Backpack provides you with [default column types](#default-column-types) for the common use cases, but you can easily [change how a default field type works](#overwriting-default-column-types), or [create an entirely new field type](#creating-a-custom-column-type).

<a name="mandatory-attributes"></a>
### Mandatory Attributes

When passing a column array, you need to specify at least these attributes:
```php
[
   'name' => 'options', // the db column name (attribute name)
   'label' => "Options", // the human-readable label for it
   'type' => 'text' // the kind of column to show
],
```

<a name="optional-attributes"></a>
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

<a name="columns-api"></a>
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

<a name="default-column-types"></a>
## FREE Column Types

<a name="boolean"></a>
### boolean

Show Yes/No (or custom text) instead of 1/0.

```php
[
    'name'  => 'name',
    'label' => 'Status',
    'type'  => 'boolean',
    // optionally override the Yes/No texts
    // 'options' => [0 => 'Active', 1 => 'Inactive']
],
```

<hr>

<a name="check"></a>
### check

Show a favicon with a checked or unchecked box, depending on the given boolean.
```php
[
   'name'  => 'featured', // The db column name
   'label' => 'Featured', // Table column heading
   'type'  => 'check'
],
```

<hr>

<a name="checkbox"></a>
### checkbox

Shows a checkbox (the form element), and inserts the js logic needed to select/deselect multiple entries. It is mostly used for [the Bulk Delete action](/docs/{{version}}/crud-operation-delete#delete-multiple-items-bulk-delete), and [custom bulk actions](/docs/{{version}}/crud-operations#creating-a-new-operation-with-a-bulk-action-no-interface).

Shorthand:
```php
$this->crud->enableBulkActions();
```
(will also add an empty custom_html column)

Verbose:
```php
$this->crud->addColumn([
    'type'           => 'checkbox',
    'name'           => 'bulk_actions',
    'label'          => ' <input type="checkbox" class="crud_bulk_actions_main_checkbox" style="width: 16px; height: 16px;" />',
    'priority'       => 1,
    'searchLogic'    => false,
    'orderable'      => false,
    'visibleInModal' => false,
])->makeFirstColumn();
```

<hr>

<a name="checklist"></a>
### checklist

The checklist column will output its connected entity. Used for relationships like hasOne() and belongsTo(). Its name and definition is the same as for the checklist field type:

```php
[
   // 1-n relationship
   'label'     => 'Parent', // Table column heading
   'type'      => 'checklist',
   'name'      => 'parent_id', // the column that contains the ID of that connected entity;
   'entity'    => 'parent', // the method that defines the relationship in your Model
   'attribute' => 'name', // foreign key attribute that is shown to user
   'model'     => "App\Models\Category", // foreign key model
   // OPTIONALS
   // 'limit' => 32, // Limit the number of characters shown
   // 'escaped' => false, // echo using {!! !!} instead of {{ }}, in order to render HTML
   // 'prefix' => 'Foo: ',
   // 'suffix' => '(bar)',
],
```

<hr>

<a name="checklist_dependency"></a>
### checklist_dependency

Show connected items selected via checklist_dependency field. It's definition is totally similar to the [checklist_dependency *field type*](/docs/{{version}}/crud-fields#checklist_dependency).

```php
[
    'label'             => 'User Role Permissions',
    'type'              => 'checklist_dependency',
    'name'              => 'roles,permissions',
    'subfields'         => [
        'primary' => [
            'name'             => 'roles', // the method that defines the relationship in your Model
            'entity'           => 'roles', // the method that defines the relationship in your Model
            'attribute'        => 'name', // foreign key attribute that is shown to user
        ],
        'secondary' => [
            'name'           => 'permissions', // the method that defines the relationship in your Model
            'entity'         => 'permissions', // the method that defines the relationship in your Model
            'attribute'      => 'name', // foreign key attribute that is shown to user
        ],
    ],
]
```

<hr>

<a name="ckeditor"></a>
### ckeditor <span class="badge badge-pill badge-info">PRO</span>

The perfect match for the [CKEditor field](https://github.com/Laravel-Backpack/ckeditor-field). The CKEditor column will just output the non-escaped text value of a db column (or model attribute). Its definition is simple:

```php
[
   'name'      => 'info', // The db column name
   'label'     => 'Info', // Table column heading
   'type'      => 'ckeditor',
],
```

For more information, please see [backpack/ckeditor-field](https://github.com/Laravel-Backpack/ckeditor-field) on Github - that is the add-on that provides this functionality.

<hr>

<a name="closure"></a>
### closure

Show custom HTML based on a closure you specify in your EntityCrudController.

```php
[
    'name'     => 'created_at',
    'label'    => 'Created At',
    'type'     => 'closure',
    'function' => function($entry) {
        return 'Created on '.$entry->created_at;
    }
],
```

> **DEPRECATED**: closure column will be removed in a future version of Backpack, since the same thing can now be achieved using any column (including the `text` column) and the `value` attribute - just pass the same closure to the `value` attribute of any column type.

<hr>

<a name="color"></a>
### color

Show color with hex code.

```php
[
    'name'  => 'color',
    'type'  => 'color',
    'label' => 'Color',
    // OPTIONALS
    // 'showColorHex' => false //show or hide hex code
]
```

<hr>

<a name="custom_html"></a>
### custom_html

Show the HTML that you provide in the page. You can optionally escape the text when displaying it on page, if you don't trust the value.

```php
[
    'name'     => 'my_custom_html',
    'label'    => 'Custom HTML',
    'type'     => 'custom_html',
    'value'    => '<span class="text-danger">Something</span>',

    // OPTIONALS
    // 'escaped' => true // echo using {{ }} instead of {!! !!}
],
```

> <span class="badge badge-danger text-white" style="text-decoration: none;">IMPORTANT</span> As opposed to most other Backpack columns, the output of `custom_html` is **NOT escaped by default**. That means if the database value contains malicious JS, that JS might be run when the admin previews it. Make sure to purify the value of this column in an accessor on your Model. At a minimum, you can use `strip_tags()` (here's [an example](https://github.com/Laravel-Backpack/demo/commit/509c0bf0d8b9ee6a52c50f0d2caed65f1f986385)), but a lot better would be to use an [HTML Purifier package](https://github.com/mewebstudio/Purifier) (do that [manually](https://github.com/Laravel-Backpack/demo/commit/7342cffb418bb568b9e4ee279859685ddc0456c1) or by casting the attribute to `CleanHtmlOutput::class`).

<hr>

<a name="date"></a>
### date


The date column will show a localized date in the default date format (as specified in the ```config/backpack/ui.php``` file), whether the attribute is cast as date in the model or not.

Note that the ```format``` attribute uses ISO date formatting parameters and not PHP ```date()``` formatters.  See <https://carbon.nesbot.com/docs/#iso-format-available-replacements> for more information.

```php
[
    'name'  => 'name', // The db column name
    'label' => 'Tag Name', // Table column heading
    'type'  => 'date',
    // 'format' => 'l j F Y', // use something else than the base.default_date_format config value
],
```

<hr>

<a name="datetime"></a>
### datetime


The date column will show a localized datetime in the default datetime format (as specified in the ```config/backpack/ui.php``` file), whether the attribute is cast as datetime in the model or not.

Note that the ```format``` attribute uses ISO date formatting parameters and not PHP ```date()``` formatters.  See <https://carbon.nesbot.com/docs/#iso-format-available-replacements> for more information.


```php
[
    'name'  => 'name', // The db column name
    'label' => 'Tag Name', // Table column heading
    'type'  => 'datetime',
    // 'format' => 'l j F Y H:i:s', // use something else than the base.default_datetime_format config value
],
```

<hr>

<a name="email"></a>
### email

The email column will output the email address in the database (truncated to 254 characters if needed), with a ```mailto:``` link towards the full email. Its definition is:
```php
[
   'name'  => 'email', // The db column name
   'label' => 'Email Address', // Table column heading
   'type'  => 'email',
   // 'limit' => 500, // if you want to truncate the text to a different number of characters
],
```

<hr>

<a name="enum"></a>
### enum

The enum column will output the value of your database ENUM column or your PHP enum attribute.
```php
[
   'name'  => 'status',
   'label' => 'Status',
   'type'  => 'enum',
],
```

By default, in case it's a `BackedEnum` it will show the `value` of the enum (when casted), in `database` or `UnitEnum` it will show the the enum value without parsing the value.

If you want to output something different than what your enum stores you have two options:
- For `database enums` you need to provide the `options` that translates the enums you store in database.
- For PHP enums you can provide the same `options` or provide a `enum_function` from the enum to gather the final result.

```php
// for database enums
[
   'name'  => 'status',
   'label' => 'Status',
   'type'  => 'enum',
   'options' => [
       'DRAFT' => 'Is draft',
       'PUBLISHED' => 'Is published'
   ]
],

// for PHP enums, given the following enum example

enum StatusEnum
{
    case DRAFT;
    case PUBLISHED;

    public function readableText(): string
    {
        return match ($this) {
            StatusEnum::DRAFT => 'Is draft',
            StatusEnum::PUBLISHED => 'Is published',
        };
    }
}

[
   'name'  => 'status',
   'label' => 'Status',
   'type'  => 'enum',
   'enum_function' => 'readableText',
   'enum_class' => 'App\Enums\StatusEnum'
],
```

<hr>

<a name="hidden"></a>
### hidden

The text column will output the text value of a db column (or model attribute). Its definition is:

```php
[
   'name'      => 'name', // The db column name
   'label'     => 'Tag Name', // Table column heading
   'type'      => 'hidden',
   // 'prefix' => 'Name: ',
   // 'suffix' => '(user)',
   // 'limit'  => 120, // character limit; default is 50,
],
```

<hr>

<a name="image"></a>
### image


Show a thumbnail image.

```php
[
    'name'      => 'profile_image', // The db column name
    'label'     => 'Profile image', // Table column heading
    'type'      => 'image',
    // 'prefix' => 'folder/subfolder/',
    // image from a different disk (like s3 bucket)
    // 'disk'   => 'disk-name',
    // optional width/height if 25px is not ok with you
    // 'height' => '30px',
    // 'width'  => '30px',
],
```

<hr>

<a name="json"></a>
### json


Display database stored JSON in a prettier way to your users.

```php
[
    'name'     => 'my_json_column_name',
    'label'    => 'JSON',
    'type'     => 'json',
    // 'escaped' => false, // echo using {!! !!} instead of {{ }}, in order to render HTML
    'toggle' => true //show a toggle button on the column that show/hide the json contents
],
```

<hr>

<a name="model_function"></a>
### model_function


The model_function column will output a function on your main model. Its definition is:
```php
[
   // run a function on the CRUD model and show its return value
   'name'  => 'url',
   'label' => 'URL', // Table column heading
   'type'  => 'model_function',
   'function_name' => 'getSlugWithLink', // the method in your Model
   // 'function_parameters' => [$one, $two], // pass one/more parameters to that method
   // 'limit' => 100, // Limit the number of characters shown
   // 'escaped' => false, // echo using {!! !!} instead of {{ }}, in order to render HTML
],
```
For this example, if your model would feature this method, it would return the link to that entity:
```php
public function getSlugWithLink() {
    return '<a href="'.url($this->slug).'" target="_blank">'.$this->slug.'</a>';
}
```

<hr>

<a name="model_function_attribute"></a>
### model_function_attribute


If the function you're trying to use returns an object, not a string, you can use the model_function_attribute column, which will output the attribute on the function result. Its definition is:
```php
[
   'name'  => 'url',
   'label' => 'URL', // Table column heading
   'type'  => 'model_function_attribute',
   'function_name' => 'getSlugWithLink', // the method in your Model
   // 'function_parameters' => [$one, $two], // pass one/more parameters to that method
   'attribute' => 'route',
   // 'limit' => 100, // Limit the number of characters shown
   // 'escaped' => false, // echo using {!! !!} instead of {{ }}, in order to render HTML
],
```

<hr>

<a name="month"></a>
### month

Show month and year with default format `MMMM Y`. You can also change to your format using `format` attribute.

```php
[
    'name'  => 'month',
    'type'  => 'month',
    'label' => 'Month',
    //OPTIONAL
    'format' => 'MMMM Y'
],
```

<hr>

<a name="multidimensional_array"></a>
### multidimensional_array


Enumerate the values in a multidimensional array, stored in the db as JSON.

```php
[
   'name'        => 'options', // The db column name
   'label'       => 'Options', // Table column heading
   'type'        => 'multidimensional_array',
   'visible_key' => 'name' // The key to the attribute you would like shown in the enumeration
],
```

<hr>

<a name="number"></a>
### number


The text column will just output the number value of a db column (or model attribute). Its definition is:
```php
[
   'name'  => 'name', // The db column name
   'label' => 'Tag Name', // Table column heading
   'type'  => 'number',
   // 'prefix'        => '$',
   // 'suffix'        => ' EUR',
   // 'decimals'      => 2,
   // 'dec_point'     => ',',
   // 'thousands_sep' => '.',
   // decimals, dec_point and thousands_sep are used to format the number;
   // for details on how they work check out PHP's number_format() method, they're passed directly to it;
   // https://www.php.net/manual/en/function.number-format.php
],
```

<hr>

<a name="password"></a>
### password

Show asterisk symbols `******` representing hidden value.

```php
[
    'name'  => 'password',
    'label' => 'Password',
    'type'  => 'password'
    //'limit' => 4, // limit number of asterisk symbol
],
```
<hr>

<a name="phone"></a>
### phone

The phone column will output the phone number from the database (truncated to 254 characters if needed), with a ```tel:``` link so that users on mobile can click them to call (or with Skype or similar browser extensions). Its definition is:
```php
[
   'name'     => 'phone', // The db column name
   'label'    => 'Phone number', // Table column heading
   'type'     => 'phone',
   // 'limit' => 10, // if you want to truncate the phone number to a different number of characters
],
```

<hr>

<a name="radio"></a>
### radio


Show a pretty text instead of the database value, according to an associative array. Usually used as a column for the "radio" field type.

```php
[
    'name'        => 'status',
    'label'       => 'Status',
    'type'        => 'radio',
    'options'     => [
        0 => 'Draft',
        1 => 'Published'
    ]
],
```

This example will show:
- "Draft" when the value stored in the db is 0;
- "Published" when the value stored in the db is 1;

<hr>

<a name="range"></a>
### range

Show progress bar

```php
[
    'name'  => 'range',
    'type'  => 'range',
    'label' => 'Range',
    //OPTIONALS
    'max' => 100,// change default max value
    'min' => 0, // change default min value
    'showMaxValue' => false, // show/hide max value
    'showValue' => false, // show only progress bar without value
    'progressColor' => 'bg-success', // change progress bar color using class
    'striped' => true, // set stripes to progress bar
]
```

<hr>

<a name="relationship_count"></a>
### relationship_count

Shows the number of items that are related to the current entry, for a particular relationship.

```php
[
   // relationship count
   'name'      => 'tags', // name of relationship method in the model
   'type'      => 'relationship_count',
   'label'     => 'Tags', // Table column heading
   // OPTIONAL
   // 'suffix' => ' tags', // to show "123 tags" instead of "123 items"

   // if you need that column to be orderable in table, you need to manually provide the orderLogic
   // 'orderable' => true,
   // 'orderLogic' => function ($query, $column, $columnDirection) {
                $query->orderBy('tags_count', $columnDirection);
            },
],
```

**Important Note:** This column will load ALL related items onto the page. Which is not a problem normally, for small tables. But if your related table has thousands or millions of entries, it will considerably slow down the page. For a much more performant option, with the same result, you can add a fake column to the results using Laravel's `withCount()` method, then use the `text` column to show that number. That will be a lot faster, and the end-result is identical from the user's perspective. For the same example above (number of tags) this is how it will look:
```
$this->crud->query->withCount('tags'); // this will add a tags_count column to the results
$this->crud->addColumn([
   'name'      => 'tags_count', // name of relationship method in the model
   'type'      => 'text',
   'label'     => 'Tags', // Table column heading
   'suffix'    => ' tags', // to show "123 tags" instead of "123"
]);
```

<hr>

<a name="row_number"></a>
### row_number


Show the row number (index). The number depends strictly on the result set (x records per page, pagination, search, filters, etc). It does not get any information from the database. It is not searchable. It is only useful to show the current row number.

```php
$this->crud->addColumn([
    'name'      => 'row_number',
    'type'      => 'row_number',
    'label'     => '#',
    'orderable' => false,
])->makeFirstColumn();
```

Notes:
- you can have a different ```name```; just make sure your model doesn't have that attribute;
- you can have a different label;
- you can place the column as second / third / etc if you remove ```makeFirstColumn()```;
- this column type allows the use of suffix/prefix just like the text column type;
- if upon placement you notice it always shows ```false``` then please note there have been changes in the ```search()``` method - you need to add another parameter to your ```getEntriesAsJsonForDatatables()``` call;

<hr>

<a name="select"></a>
### select

The select column will output its connected entity. Used for relationships like hasOne() and belongsTo(). Its name and definition is the same as for the select *field type*:
```php
[
   // 1-n relationship
   'label'     => 'Parent', // Table column heading
   'type'      => 'select',
   'name'      => 'parent_id', // the column that contains the ID of that connected entity;
   'entity'    => 'parent', // the method that defines the relationship in your Model
   'attribute' => 'name', // foreign key attribute that is shown to user
   'model'     => "App\Models\Category", // foreign key model
   // OPTIONAL
   // 'limit' => 32, // Limit the number of characters shown
],
```

<hr>
<a name="select_from_array"></a>
### select_from_array

Show a particular text depending on the value of the attribute.

```php
[
    // select_from_array
    'name'    => 'status',
    'label'   => 'Status',
    'type'    => 'select_from_array',
    'options' => ['draft' => 'Draft (invisible)', 'published' => 'Published (visible)'],
],
```

<hr>

<a name="select_grouped"></a>
### select_grouped

The `select_grouped` column will output its connected entity. Used for relationships like hasOne() and belongsTo(). Its name and definition is the same as for the select field type:

```php
[
   // 1-n relationship
   'label'     => 'Parent', // Table column heading
   'type'      => 'select_grouped',
   'name'      => 'parent_id', // the column that contains the ID of that connected entity;
   'entity'    => 'parent', // the method that defines the relationship in your Model
   'attribute' => 'name', // foreign key attribute that is shown to user
   'model'     => "App\Models\Category", // foreign key model
],
```

<hr>

<a name="select_multiple"></a>
### select_multiple

The select_multiple column will output a comma separated list of its connected entities. Used for relationships like hasMany() and belongsToMany(). Its name and definition is the same as the select_multiple field:
```php
[
   // n-n relationship (with pivot table)
   'label'     => 'Tags', // Table column heading
   'type'      => 'select_multiple',
   'name'      => 'tags', // the method that defines the relationship in your Model
   'entity'    => 'tags', // the method that defines the relationship in your Model
   'attribute' => 'name', // foreign key attribute that is shown to user
   'model'     => 'App\Models\Tag', // foreign key model
   // OPTIONAL
   'separator' => ',', // if you want to use a different separator than the default ','
],
```

<hr>

<a name="summernote"></a>
### summernote

The summernote column will output the non-escaped text value of a db column (or model attribute). Its definition is:

```php
[
   'name'      => 'name', // The db column name
   'label'     => 'Tag Name', // Table column heading
   'type'      => 'summernote',
],
```

<hr>

<a name="switch"></a>
### switch

Show a favicon with a checked or unchecked box, depending on the given boolean.

```php
[
   'name'  => 'featured', // The db column name
   'label' => 'Featured', // Table column heading
   'type'  => 'switch'
],
```

<hr>

<a name="text"></a>
### text

The text column will just output the text value of a db column (or model attribute). Its definition is:
```php
[
   'name'      => 'name', // The db column name
   'label'     => 'Tag Name', // Table column heading
   // 'prefix' => 'Name: ',
   // 'suffix' => '(user)',
   // 'limit'  => 120, // character limit; default is 50,
],
```

**Advanced use case:** The ```text``` column type can also show the attribute of a 1-1 relationship. If you have a relationship (like ```parent()```) set up in your Model, you can use relationship and attribute in the ```name```, using dot notation:
```php
[
    'name'  => 'parent.title',
    'label' => 'Title',
    'type'  => 'text'
],
```

<hr>

<a name="textarea"></a>
### textarea
The text column will just output the text value of a db column (or model attribute) in a textarea field. Its definition is:
```php
[
   'name'      => 'name', // The db column name
   'label'     => 'Tag Name', // Table column heading
   // 'prefix' => 'Name: ',
   // 'suffix' => '(user)',
   // 'limit'  => 120, // character limit; default is 50
   // 'escaped' => false, // echo using {!! !!} instead of {{ }}, in order to render HTML
],
```

<hr>

<a name="tinymce"></a>
### tinymce <span class="badge badge-pill badge-info">PRO</span>

The perfect match for the [`tinymce` field](https://github.com/Laravel-Backpack/tinymce-field). The tinymce column will just output the non-escaped text value of a db column (or model attribute). Its definition is simple:
```php
[
   'name'      => 'info', // The db column name
   'label'     => 'Info', // Table column heading
   'type'      => 'tinymce',
],
```

For more information on the TinyMCE field and column, see [backpack/tinymce-field](
The perfect match for the [`tinymce` field](https://github.com/Laravel-Backpack/tinymce-field)) on Github - that's the addon that provides this functionality.

<hr>

<a name="time"></a>
### time

Show time in 24-Hour format `H:mm` by default . You are also free to change the format.

```php
[
    'name'  => 'time', // The db column name
    'label' => 'time', // Table column heading
    'type'  => 'time',
    // 'format' => 'H:mm', // use something else than the default.
],
```

<hr>

<a name="upload"></a>
### upload

Show link to the file which let's you open it in the new tab.

```php
[
    'name'   => 'upload',
    'type'   => 'upload',
    'label'  => 'Upload',
    'disk'   => 'uploads',
]
```

<hr>

<a name="upload_multiple"></a>
### upload_multiple

The ```upload_multiple``` column will output a list of files and links, when used on an attribute that stores a JSON array of file paths. It is meant to be used inside the show functionality (not list, though it also works there), to preview files uploaded with the ```upload_multiple``` field type.

Its definition is very similar to the [upload_multiple *field type*](/docs/{{version}}/crud-fields#upload_multiple).

```php
[
    'name'    => 'photos',
    'label'   => 'Photos',
    'type'    => 'upload_multiple',
    // 'disk' => 'public', // filesystem disk if you're using S3 or something custom
],
```

<hr>

<a name="url"></a>
### url

Show a link which opens in the new tab by default.

```php
[
    'name'  => 'url',
    'type'  => 'url',
    'label' => 'URL',
    //'target' => '_blank' // let's you change link target window.
    //'element' => 'a' // let's you change the element of the link.
    //'rel' => false OR 'rel' => 'noopener' // let's you disable or change the rel attribute of the link.
],
```

<hr>

<a name="view"></a>
### view

Display any custom column type you want. Usually used by Backpack package developers, to use views from within their packages, instead of having to publish the views.

```php
[
   'name'  => 'name', // The db column name
   'label' => 'Tag Name', // Table column heading
   'type'  => 'view',
   'view'  => 'package::columns.column_type_name', // or path to blade file
],
```

<hr>

<a name="week"></a>
### week

Show the ISO-8601 week number of **year** (weeks starting on Monday). Example: `Week 25 2023`

```php
[
    'name'  => 'week',
    'type'  => 'week',
    'label' => 'Week',
],
```

<hr>

<a name="pro-column-types"></a>
## PRO Column Types

<a name="address_google"></a>
### address_google <span class="badge badge-pill badge-info">PRO</span>

Show `value` attribute as text stored in the db column as JSON array created by `address_google` field. Example: Jaipur, India.

```php
[
    'name'  => 'address_google',
    'type'  => 'address_google',
    'label' => 'Address Google',
],
```

<hr>

<a name="array"></a>
### array <span class="badge badge-pill badge-info">PRO</span>

Enumerate an array stored in the db column as JSON.
```php
[
   'name'  => 'options', // The db column name
   'label' => 'Options', // Table column heading
   'type'  => 'array'
],
```

<hr>

<a name="array_count"></a>
### array_count <span class="badge badge-pill badge-info">PRO</span>

Count the items in an array stored in the db column as JSON.

```php
[
   'name'  => 'options', // The db column name
   'label' => 'Options', // Table column heading
   'type'  => 'array_count',
   // 'suffix' => 'options', // if you want it to show "2 options" instead of "2 items"
],
```

<hr>

<a name="base64_image"></a>
### base64_image <span class="badge badge-pill badge-info">PRO</span>

Show a thumbnail image stored in the db column as `base64` image string.

```php
[
    'name'      => 'profile_image', // The db column name
    'label'     => 'Profile image', // Table column heading
    'type'      => 'base64_image',
    // optional width/height if 25px is not ok with you
    // 'height' => '30px',
    // 'width'  => '30px',
],
```

<hr>

<a name="code_mirror"></a>
### code_mirror <span class="badge badge-pill badge-info">PRO</span>

Show a preview of the code stored in the database, with a "View" button to show the full code in accordeon style (hidden div).

```php
[
    'name' => 'code',
    'type' => 'code_mirror',
    'label' => 'Code',
],
```

<hr>

<a name="date_picker"></a>
### date_picker <span class="badge badge-pill badge-info">PRO</span>

It's the same [date](#date-1) column with an alias, named after it's field name `date_picker`.

```php
[
    'name'  => 'name', // The db column name
    'label' => 'Tag Name', // Table column heading
    'type'  => 'date',
    // 'format' => 'l j F Y', // use something else than the base.default_date_format config value
],
```

<hr>

<a name="datetime_picker"></a>
### datetime_picker <span class="badge badge-pill badge-info">PRO</span>

It's the same [datetime](#datetime-1) column with an alias, named after [datetime_picker *field type*](/docs/{{version}}/crud-fields#datetime_picker-pro)..

```php
[
    'name'  => 'name', // The db column name
    'label' => 'Tag Name', // Table column heading
    'type'  => 'datetime',
    // 'format' => 'l j F Y H:i:s', // use something else than the base.default_datetime_format config value
],
```

<hr>

<a name="date_range"></a>
### date_range <span class="badge badge-pill badge-info">PRO</span>

Show two date columns in a single column as a date range. Example: `18 Mar 2000 - 30 Nov 1985`

Its definition is very similar to the [date_range *field type*](/docs/{{version}}/crud-fields#date_range-pro).

```php
[ // Date_range
    'name'       => 'start_date,end_date', // two columns with a comma
    'label'      => 'Date Range',
    'type'       => 'date_range',
]
```

<hr>

<a name="dropzone"></a>
### dropzone <span class="badge badge-pill badge-info">PRO</span>

The ```dropzone``` column will output a list of files and links, when used on an attribute that stores a JSON array of file paths. It is meant to be used inside the show functionality (not list, though it also works there), to preview files uploaded with the ```dropzone``` field type.

Its definition is very similar to the [dropzone *field type*](/docs/{{version}}/crud-fields#dropzone-pro).


```php
[
    'name'  => 'dropzone', // The db column name
    'label' => 'Images', // Table column heading
    'type'  => 'dropzone',
    // 'disk'  => 'public', specify disk name
]
```

<hr>

<a name="easymde"></a>
### easymde <span class="badge badge-pill badge-info">PRO</span>

Convert easymde generated markdown string to HTML, using `Illuminate\Support\Str::markdown()`. Since Markdown is usually used for long texts, this column is most helpful in the "Show" operation - not so much in the "ListEntries" operation, where only short snippets make sense.

It's the same [markdown](#markdown-pro) column with an alias, named after its field name. See the [markdown column](#markdown-pro) for the full list of options, including `markdown_options`.

```php
[
   'name'      => 'info', // The db column name
   'label'     => 'Info', // Table column heading
   'type'      => 'easymde',
],
```

<hr>

<a name="icon_picker"></a>
### icon_picker <span class="badge badge-pill badge-info">PRO</span>

Show the selected icon. Supported icon sets are fontawesome, lineawesome, glyphicon, ionicon, weathericon, mapicon, octicon, typicon, elusiveicon, materialdesign as per the jQuery plugin, [bootstrap-iconpicker](http://victor-valencia.github.io/bootstrap-iconpicker/).

It's definition is totally similar to the [icon_picker *field type*](/docs/{{version}}/crud-fields#icon_picker-pro).

```php
[
    'name'    => 'icon_picker',
    'type'    => 'icon_picker',
    'label'   => 'Icon Picker',
    'iconset' => 'fontawesome' // options: fontawesome, lineawesome, glyphicon, ionicon, weathericon, mapicon, octicon, typicon, elusiveicon, materialdesign
]
```

<hr>

<a name="markdown"></a>
### markdown <span class="badge badge-pill badge-info">PRO</span>


Convert a markdown string to HTML, using `Illuminate\Support\Str::markdown()`. Since Markdown is usually used for long texts, this column is most helpful in the "Show" operation - not so much in the "ListEntries" operation, where only short snippets make sense.

```php
[
   'name'  => 'text', // The db column name
   'label' => 'Text', // Table column heading
   'type'  => 'markdown',
],
```

By default, raw HTML embedded in the markdown is **stripped** and unsafe links (`javascript:`, `vbscript:`, `data:`) are **blocked**. This is a safe default for content that may have been entered by untrusted users.

You can customise the rendering behaviour by passing a `markdown_options` array, which is forwarded directly to `Str::markdown()`:

```php
[
   'name'             => 'text',
   'label'            => 'Text',
   'type'             => 'markdown',
   // OPTIONAL - override the CommonMark options (these are the defaults):
   'markdown_options' => [
       'html_input'         => 'strip',   // 'allow' | 'escape' | 'strip'
       'allow_unsafe_links' => false,     // disallow javascript:/vbscript: links
       // 'max_nesting_level' => 10,       // prevent deeply nested structures (DoS protection)
   ],
],
```

If you explicitly trust the markdown source (e.g. content only ever entered by admins) and need raw HTML to pass through, you can opt in:

```php
[
   'name'             => 'text',
   'label'            => 'Text',
   'type'             => 'markdown',
   'markdown_options' => [
       'html_input'         => 'allow', // ⚠ only use for fully trusted content
       'allow_unsafe_links' => true,
   ],
],
```

> <span class="badge badge-danger text-white" style="text-decoration: none;">IMPORTANT</span> The output of `markdown` is **NOT escaped by default** — it is rendered as HTML. With the default `markdown_options`, raw HTML tags are stripped and unsafe links are blocked, which protects against the most common XSS vectors. If you override `html_input` to `'allow'`, you take full responsibility for sanitising the stored value. In that case, make sure to purify the value in an accessor on your Model using an [HTML Purifier package](https://github.com/mewebstudio/Purifier)

<hr>

<a name="relationship"></a>
### relationship <span class="badge badge-pill badge-info">PRO</span>

Output the related entries, no matter the relationship:
- 1-n relationships - outputs the name of its one connected entity;
- n-n relationships - enumerates the names of all its connected entities;

Its name and definition is the same as for the relationship *field type*:
```php
[
   // any type of relationship
   'name'         => 'tags', // name of relationship method in the model
   'type'         => 'relationship',
   'label'        => 'Tags', // Table column heading
   // OPTIONAL
   // 'entity'    => 'tags', // the method that defines the relationship in your Model
   // 'attribute' => 'name', // foreign key attribute that is shown to user
   // 'model'     => App\Models\Category::class, // foreign key model
],
```

Backpack tries to guess which attribute to show for the related item. Something that the end-user will recognize as unique. If it's something common like "name" or "title" it will guess it. If not, you can manually specify the ```attribute``` inside the column definition, or you can add ```public $identifiableAttribute = 'column_name';``` to your model, and Backpack will use that column as the one the user finds identifiable. It will use it here, and it will use it everywhere you haven't explicitly asked for a different attribute.

<hr>

<a name="repeatable"></a>
### repeatable <span class="badge badge-pill badge-info">PRO</span>

Show stored JSON in a table. It's definition is similar to the [repeatable *field type*](/docs/{{version}}/crud-fields#repeatable-pro).

```php
[
    'name'      => 'features',
    'label'     => 'Features',
    'type'      => 'repeatable',
    'subfields' => [
        [
            'name'    => 'feature',
            'wrapper' => [
                'class' => 'col-md-3',
            ],
        ],
        [
            'name'    => 'value',
            'wrapper' => [
                'class' => 'col-md-6',
            ],
        ],
        [
            'name'    => 'quantity',
            'type'    => 'number',
            'wrapper' => [
                'class' => 'col-md-3',
            ],
        ],
    ],
]
```

<hr>

<a name="select2"></a>
### select2 <span class="badge badge-pill badge-info">PRO</span>

The select2 column will output its connected entity. Used for relationships like hasOne() and belongsTo(). Its name and definition is the same as for the select field type:
```php
[
   // 1-n relationship
   'label'     => 'Parent', // Table column heading
   'type'      => 'select2',
   'name'      => 'parent_id', // the column that contains the ID of that connected entity;
   'entity'    => 'parent', // the method that defines the relationship in your Model
   'attribute' => 'name', // foreign key attribute that is shown to user
   'model'     => "App\Models\Category", // foreign key model
],
```

<hr>

<a name="select2_multiple"></a>
### select2_multiple <span class="badge badge-pill badge-info">PRO</span>

The select2_multiple column will output a comma separated list of its connected entities. Used for relationships like hasMany() and belongsToMany(). Its name and definition is the same as the select2_multiple field:

```php
[
   // n-n relationship (with pivot table)
   'label'     => 'Tags', // Table column heading
   'type'      => 'select2_multiple',
   'name'      => 'tags', // the method that defines the relationship in your Model
   'entity'    => 'tags', // the method that defines the relationship in your Model
   'attribute' => 'name', // foreign key attribute that is shown to user
   'model'     => 'App\Models\Tag', // foreign key model
],
```

<hr>

<a name="select2_nested"></a>
### select2_nested <span class="badge badge-pill badge-info">PRO</span>

The select2_nested column will output its connected entity. Used for relationships like hasOne() and belongsTo(). Its name and definition is the same as for the select field type:
```php
[
   // 1-n relationship
   'label'     => 'Parent', // Table column heading
   'type'      => 'select2_nested',
   'name'      => 'parent_id', // the column that contains the ID of that connected entity;
   'entity'    => 'parent', // the method that defines the relationship in your Model
   'attribute' => 'name', // foreign key attribute that is shown to user
   'model'     => "App\Models\Category", // foreign key model
],
```
<hr>

<a name="select2_grouped"></a>
### select2_grouped <span class="badge badge-pill badge-info">PRO</span>

The select2_grouped column will output its connected entity. Used for relationships like hasOne() and belongsTo(). Its name and definition is the same as for the select field type:
```php
[
   // 1-n relationship
   'label'     => 'Parent', // Table column heading
   'type'      => 'select2_grouped',
   'name'      => 'parent_id', // the column that contains the ID of that connected entity;
   'entity'    => 'parent', // the method that defines the relationship in your Model
   'attribute' => 'name', // foreign key attribute that is shown to user
   'model'     => "App\Models\Category", // foreign key model
],
```
<hr>

<a name="select_and_order"></a>
### select_and_order <span class="badge badge-pill badge-info">PRO</span>

Show selected values in the order they are saved.

Its definition is very similar to the [select_and_order *field type*](/docs/{{version}}/crud-fields#select_and_order-pro).

```php
[
    // select_from_array
    'name'    => 'status',
    'label'   => 'Status',
    'type'    => 'select_and_order',
    'options' => ['draft' => 'Draft (invisible)', 'published' => 'Published (visible)'],
],
```

<hr>

<a name="select2_from_array"></a>
### select2_from_array <span class="badge badge-pill badge-info">PRO</span>

Show a particular text depending on the value of the attribute.

```php
[
    // select_from_array
    'name'    => 'status',
    'label'   => 'Status',
    'type'    => 'select2_from_array',
    'options' => ['draft' => 'Draft (invisible)', 'published' => 'Published (visible)'],
],
```

<hr>

<a name="select2_from_ajax"></a>
### select2_from_ajax <span class="badge badge-pill badge-info">PRO</span>

The select2_from_ajax column will output its connected entity. Used for relationships like hasOne() and belongsTo(). Its name and definition is the same as for the select field type:
```php
[
   // 1-n relationship
   'label'     => 'Parent', // Table column heading
   'type'      => 'select2_from_ajax',
   'name'      => 'parent_id', // the column that contains the ID of that connected entity;
   'entity'    => 'parent', // the method that defines the relationship in your Model
   'attribute' => 'name', // foreign key attribute that is shown to user
   'model'     => "App\Models\Category", // foreign key model
],
```

<hr>

<a name="select2_from_ajax_multiple"></a>
### select2_from_ajax_multiple <span class="badge badge-pill badge-info">PRO</span>

The select2_from_ajax_multiple column will output a comma separated list of its connected entities. Used for relationships like hasMany() and belongsToMany(). Its name and definition is the same as the select2_multiple field:

```php
[
   // n-n relationship (with pivot table)
   'label'     => 'Tags', // Table column heading
   'type'      => 'select2_from_ajax_multiple',
   'name'      => 'tags', // the method that defines the relationship in your Model
   'entity'    => 'tags', // the method that defines the relationship in your Model
   'attribute' => 'name', // foreign key attribute that is shown to user
   'model'     => 'App\Models\Tag', // foreign key model
],
```

<hr>

<a name="slug"></a>
### slug <span class="badge badge-pill badge-info">PRO</span>

Show stored text value of slug.

```php
[
    'name'  => 'slug',
    'type'  => 'slug',
    'label' => 'Slug',
]
```

<hr>

<a name="table"></a>
### table <span class="badge badge-pill badge-info">PRO</span>


The ```table``` column will output a condensed table, when used on an attribute that stores a JSON array or object. It is meant to be used inside the show functionality (not list, though it also works there).

Its definition is very similar to the [table *field type*](/docs/{{version}}/crud-fields#table).

```php
[
    'name'  => 'features',
    'label' => 'Features',
    'type'  => 'table',
    'columns' => [
        'name'        => 'Name',
        'description' => 'Description',
        'price'       => 'Price',
        'obs'         => 'Observations'
    ]
],
```

<hr>

<a name="video"></a>
### video <span class="badge badge-pill badge-info">PRO</span>


Display a small screenshot for a YouTube or Vimeo video, stored in the database as JSON using the "video" field type.

```php
[
   'name'  => 'name', // The db column name
   'label' => 'Tag Name', // Table column heading
   'type'  => 'video',
],
```

<hr>

<a name="overwriting-default-column-types"></a>
## Overwriting Default Column Types

You can overwrite a column type by placing a file with the same name in your ```resources\views\vendor\backpack\crud\columns``` directory. When a file is there, Backpack will pick that one up, instead of the one in the package. You can do that from command line using ```php artisan backpack:column --from=column-file-name```

Examples:
- creating a ```resources\views\vendor\backpack\crud\columns\number.blade.php``` file would overwrite the ```number``` column functionality;
- ```php artisan backpack:column --from=text``` will take the view from the package and copy it to the directory above, so you can edit it;

>Keep in mind that when you're overwriting a default column type, you're forfeiting any future updates for that column. We can't push updates to a file that you're no longer using.

<hr>

<a name="creating-a-custom-column-type"></a>
## Creating a Custom Column Type

Columns consist of only one file - a blade file with the same name as the column type (ex: ```text.blade.php```). You can create one by placing a new blade file inside ```resources\views\vendor\backpack\crud\columns```. Be careful to choose a distinctive name, otherwise you might be overwriting a default column type (see above).

For example, you can create a ```markdown.blade.php```:
```php
<span>{!! \Markdown::convertToHtml($entry->{$column['name']}) !!}</span>
```

The most useful variables you'll have in this file here are:
- ```$entry``` - the database entry you're showing (Eloquent object);
- ```$crud``` - the entire CrudPanel object, with settings, options and variables;

By default, custom columns are not searchable. In order to make your column searchable you need to [specify a custom ```searchLogic``` in your declaration](#custom-search-logic).


<hr>

<a name="advanced-columns-use"></a>
## Advanced Columns Use

<a name="custom-search-logic"></a>
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

<a name="custom-order-logic"></a>
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


<a name="custom-wrapper-for-columns"></a>
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

Let's take another example, and wrap a boolean column into a green/red span:

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


<a name="link-column-to-routes"></a>
### Link Column To Route

To make a column link to a route URL, you can use the `linkTo($routeNameOrClosure, $parameters = [])` helper. Behind the scenes, this helper will use the `wrapper` helper to set up a link towards the route you want. See the section above for details on the `wrapper` helper.

It's dead-simple to use the `linkTo()` helper to point to a route name:
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


<a name="choose-where-columns-are-visible"></a>
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

<a name="multiple-columns-with-the-same-name"></a>
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

<a name="escape-column-output"></a>
### Escape column output

For security purposes, Backpack escapes the output of all column types except for `markdown` and `custom_html` (those columns would be useless escaped). That means it uses `{{ }}` to echo the output, not `{!! !!}`. If you have any HTML inside a db column, it will be shown as HTML instead of interpreted. It does that because, if the value was added by a malicious user (not admin), it could contain malicious JS code.

However, if you trust that a certain column contains _safe_ HTML, you can disable this behaviour by setting the `escaped` attribute to `false`.

Our recommendation, in order to trust the output of a column, is to either:
- (a) only allow the admin to add/edit that column;
- (b) purify the value in an accessor on the Model, so that every time you get it, it's cleaned; you can use an [HTML Purifier package](https://github.com/mewebstudio/Purifier) for that (do it [manually](https://github.com/Laravel-Backpack/demo/commit/7342cffb418bb568b9e4ee279859685ddc0456c1) or by casting the attribute to `CleanHtmlOutput::class`);

<a name="define-which-columns-to-hide-in-responsive-table"></a>
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

<a name="adding-new-methods-to-the-crudcolumn-class"></a>
### Adding new methods to the CrudColumn class

You can add your own methods Backpack CRUD columns, so that you can do `CRUD::column('name')->customThing()`. You can easily do that, because the `CrudColumn` class is Macroable. It's as easy as this:

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
