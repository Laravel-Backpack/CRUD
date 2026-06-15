# Fields

---

<a name="about"></a>
## About

Field types define how the admin can manipulate an entry's values. They're used by the Create and Update operations.

Think of the field type as the type of input: ```<input type="text" />```. But for most entities, you won't just need text inputs - you'll need datepickers, upload buttons, 1-n relationship, n-n relationships, textareas, etc.

We have a lot of default field types, detailed below. If you don't find what you're looking for, you can [create a custom field type](/docs/{{version}}/crud-fields#creating-a-custom-field-type). Or if you just want to tweak a default field type a little bit, you can [overwrite default field types](/docs/{{version}}/crud-fields#overwriting-default-field-types).

> NOTE: If the _field name_ is the exact same as a relation method in the model, Backpack will assume you're adding a field for that relationship and infer relation attributes from it. To disable this behaviour, you can use `'entity' => false` in your field definition.

<a name="fields-api"></a>
### Fields API

To manipulate fields, you can use the methods below. The action will be performed on the currently running operation. So make sure you run these methods inside ```setupCreateOperation()```, ```setupUpdateOperation()``` or in ```setup()``` inside operation blocks:

```php
// to add a field with this name
CRUD::field('price');

 // or directly with the type attribute on it
CRUD::field('price')->type('number');

// or set multiple attributes in one go
CRUD::field([
    'name' => 'price',
    'type' => 'number',
]);

// to change an attribute on a field, you can target it at any point
CRUD::field('price')->prefix('$');

// to change the same attribute across multiple fields you can wrap them in a `group`
// this will add the '$' prefix to both fields
CRUD::group(
    CRUD::field('price'),
    CRUD::field('discount')
)->prefix('$');

// to move fields before or after other fields
CRUD::field('price')->before('name');
CRUD::field('price')->after('name');

// to remove a field from both operations
CRUD::field('name')->remove();

// to perform bulk actions
CRUD::removeAllFields();
CRUD::addFields([$field_definition_array_1, $field_definition_array_2]);

```

<a name="field-attributes"></a>
### Field Attributes

<a name="mandatory-field-attributes"></a>
#### Mandatory Field Attributes

**The only attribute that's mandatory when you define a field is its `name`**, which will be used:
- inside the inputs, as `<input name='your_db_column' />`;
- to store the information in the database, so your `name` should correspond to a database column (if the field type doesn't have different instructions);

This also means the `name` attribute is UNIQUE. You cannot add multiple fields with the same `name` - because if more inputs are added with the same name in an HTML form... only the last input will actually be submitted. That's just how HTML forms work.

<a name="recommended-field-attributes"></a>
#### Recommended Field Attributes

Usually developers define the following attributes for all fields:
- the ```name``` of the column in the database (ex: "title")
- the human-readable ```label``` for the input (ex: "Title")
- the ```type``` of the input (ex: "text")

So at minimum, a field definition array usually looks like:
```php
CRUD::field([
    'name'  => 'description',
    'label' => 'Article Description',
    'type'  => 'textarea',
]);
```

Please note that `label` and `type` are not _mandatory_, just _recommended_:
- `label` can be omitted, and Backpack will try to construct it from the `name`;
- `type` can be omitted, and Backpack will try to guess it from the database column type, or if there's a relationship on the Model with the same `name`;

<a name="optional-field-attributes-for-presentation-purposes"></a>
#### Optional - Field Attributes for Presentation Purposes

There are a few optional attributes on most default field types, that you can use to easily achieve a few common customisations:

```php
[
    'prefix'     => '',
    'suffix'     => '',
    'default'    => 'some value', // set a default value
    'hint'       => 'Some hint text', // helpful text, shows up after the input
    'attributes' => [
       'placeholder' => 'Some text when empty',
       'class'       => 'form-control some-class',
       'readonly'    => 'readonly',
       'disabled'    => 'disabled',
     ], // change the HTML attributes of your input
     'wrapper'   => [
        'class'      => 'form-group col-md-12'
     ], // change the HTML attributes for the field wrapper - mostly for resizing fields
]
```

These will help you:

- **prefix** - add a text or icon _before_ the actual input;
- **suffix** - add a text or icon _after_ the actual input;
- **default** - specify a default value for the input, on create;
- **hint** - add descriptive text for this input;
- **attributes** - change or add actual HTML attributes of the input (ex: readonly, disabled, class, placeholder, etc);
- **wrapper** - change or add actual HTML attributes to the div that contains the input;

<a name="optional-field-attributes-for-accessibility"></a>
#### Optional but Recommended - Field Attributes for Accessibility

By default, field labels are not directly associated with input fields. To improve the accessibility of CRUD fields for screen readers and other assistive technologies (ensuring that a user entering a field will be informed of the name of the field), you can use the ```aria-label``` attribute:

```php
CRUD::field([
    'name'  => 'email',
    'label' => 'Email Address',
    'type'  => 'email',
    'attributes' => [
        'aria-label' => 'Email Address',
    ],
]);
```

In most cases, the ```aria-label``` will be the same as the ```label``` but there may be times when it is helpful to provide more context to the user. For example, the field ```hint``` text appears _after_ the field itself and therefore a screen reader user will not encounter the ```hint``` until they leave the field. You might therefore want to provide a more descriptive ```aria-label```, for example:

```php
CRUD::field([
    'name'       => 'age',
    'label'      => 'Age',
    'type'       => 'number',
    'hint'       => 'Enter the exact age of the participant (as a decimal, e.g. 2.5)',
    'attributes' => [
        'step' => 'any',
        'aria-label' => 'Participant Age (as a decimal number)',
    ],
]);
```

<a name="fake-fields"></a>
#### Optional - Fake Field Attributes (stores fake attributes as JSON in the database)

In case you want to store information for an entry that doesn't need a separate database column, you can add any number of Fake Fields, and their information will be stored inside a column in the db, as JSON. By default, an ```extras``` column is used and assumed on the database table, but you can change that.

**Step 1.** Use the fake attribute on your field:
```php
CRUD::field([
    'name'     => 'name', // JSON variable name
    'label'    => "Tag Name", // human-readable label for the input

    'fake'     => true, // show the field, but don't store it in the database column above
    'store_in' => 'extras' // [optional] the database column name where you want the fake fields to ACTUALLY be stored as a JSON array
]);
```

**Step 2.** On your model, make sure the db columns where you store the JSONs (by default only ```extras```):
- are in your ```$fillable``` property;
- are on a new ```$fakeColumns``` property (create it now);
- are cast as array in ```$casts```;

>If you need your fakes to also be translatable, remember to also place ```extras``` in your model's ```$translatable``` property and remove it from ```$casts```.

Example:
```php
CRUD::field([
    'name'     => 'meta_title',
    'label'    => "Meta Title",
    'fake'     => true,
    'store_in' => 'metas' // [optional]
]);
CRUD::field([
    'name'     => 'meta_description',
    'label'    => "Meta Description",
    'fake'     => true,
    'store_in' => 'metas' // [optional]
]);
CRUD::field([
    'name'     => 'meta_keywords',
    'label'    => "Meta Keywords",
    'fake'     => true,
    'store_in' => 'metas' // [optional]
]);
```

In this example, these 3 fields will show up in the create & update forms, the CRUD will process as usual, but in the database these values won't be stored in the ```meta_title```, ```meta_description``` and ```meta_keywords``` columns. They will be stored in the ```metas``` column as a JSON array:

```php
{"meta_title":"title","meta_description":"desc","meta_keywords":"keywords"}
```

If the ```store_in``` attribute wasn't used, they would have been stored in the ```extras``` column.

<a name="split-fields-into-tabs"></a>
#### Optional - Tab Attribute Splits Forms into Tabs

You can now split your create/edit inputs into multiple tabs.

![CRUD Fields Tabs](https://backpackforlaravel.com/uploads/docs-4-0/operations/create_tabs.png)

To use this feature, specify the tab name for each of your fields. For example:

```php
CRUD::field('price')->tab('Tab name here');
```

If you don't specify a tab name for a field, then Backpack will place it above all of the tabs, for example:

```php
CRUD::field('product');
CRUD::field('description')->tab('Information');
CRUD::field('price')->tab('Prices');
```

<a name="entity-model-and-attribute"></a>
#### Optional - Attributes for Fields Containing Related Entries

When a field works with related entities (relationships like `BelongsTo`, `HasOne`, `HasMany`, `BelongsToMany`, etc), Backpack needs to know how the current model (being create/edited) and the other model (that shows up in the field) are related. And it stores that information in a few additional field attributes, right after you add the field.

*Normally, Backpack will guess all this relationship information for you.* If you have your relationships properly defined in your Models, you can just use a relationship field the same way you would a normal field. Pretend that _the method in your Model that defines your relationship_ is a real column, and Backpack will do all the work for you.

But if you want to overwrite any of the relationship attributes Backpack guesses, here they are:
- `entity` - points to the method on the model that contains the relationship; having this defined, Backpack will try to guess from it all other field attributes; ex: `category` or `tags`;
- `model` - the classname (including namespace) of the related model (ex: `App\Models\Category`); usually deduced from the relationship function in the model;
- `attribute` - the attribute on the related model (aka foreign attribute) that will be show to the user; for example, you wouldn't want a dropdown of categories showing IDs - no, you'd want to show the category names; in this case, the `attribute` will be `name`; usually deduced using the [identifiable attribute functionality explained below](#identifiable-attribute);
- `multiple` - boolean, allows the user to pick one or multiple items; usually deduced depending on whether it's a 1-to-n or n-n relationship;
- `pivot` - boolean, instructs Backpack to store the information inside a pivot table; usually deduced depending on whether it's a 1-to-n or n-n relationship;
- `relation_type` - text, deduced from `entity`; not a good idea to overwrite;

If you do need a field that contains relationships to behave a certain way, it's usually enough to just specify a different `entity`. However, you _can_ specify any of the attributes above, and Backpack will take your value for it, instead of trying to guess one.


<a name="identifiable-attribute"></a>
**Identifiable Attribute for Relationship Fields**

Fields that work with relationships will allow you to select which ```attribute``` on the related entry you want to show to the user. All relationship fields (relationship, select, select2, select_multiple, select2_multiple, select2_from_ajax, select2_from_ajax_multiple) let you define the ```attribute``` for this specific purpose.

For example, when the admin creates an ```Article``` they'll have to select a ```Category``` from a dropdown. It's important to show an attribute for ```Category``` that will help the admin easily identify the category, even if it's not the ID. In this example, it would probably be the category name - that's what you'd like the dropdown to show.

In Backpack, you can explicitly define this, by giving the field an ```attribute```. But you can also NOT explicitly define this - Backpack will try to guess it. If you don't like what Backpack guessed would be a good identifiable attribute, you can either:
- (A) explicitly define an ```attribute``` for that field

>**Note**: If the attribute you want to show is an acessor in Model, you need to add it to the `$appends` property of the said Model. https://laravel.com/docs/10.x/eloquent-serialization#appending-values-to-json

- (B) you can specify the identifiable attribute in your model, and all fields will pick this up:

```php

use Backpack\CRUD\app\Models\Traits\CrudTrait;

class Category
{
    use CrudTrait;

    // you can define this

    /**
     * Attribute shown on the element to identify this model.
     *
     * @var string
     */
    protected $identifiableAttribute = 'title';

    // or for more complicated use cases you can do

    /**
     * Get the attribute shown on the element to identify this model.
     *
     * @return string
     */
    public function identifiableAttribute()
    {
        // process stuff here
        return 'whatever_you_want_even_an_accessor';
    }
}
```
<a name="default-field-types"></a>
## FREE Field Types

<a name="checkbox"></a>
### checkbox

Checkbox for true/false.

```php
CRUD::field([   // Checkbox
    'name'  => 'active',
    'label' => 'Active',
    'type'  => 'checkbox'
]);
```

Input preview:

![CRUD Field - checkbox](https://backpackforlaravel.com/uploads/docs-4-2/fields/checkbox.png)

<hr>

<a name="checklist"></a>
### checklist

Show a list of checkboxes, for the user to check one or more of them.

```php
CRUD::field([   // Checklist
    'label'     => 'Roles',
    'type'      => 'checklist',
    'name'      => 'roles',
    'entity'    => 'roles',
    'attribute' => 'name',
    'model'     => "Backpack\PermissionManager\app\Models\Role",
    'pivot'     => true,
    'show_select_all' => true, // default false
    // 'number_of_columns' => 3,
    
]);
```

**Note: If you don't use a pivot table (pivot = false), you need to cast your db column as `array` in your model,by adding your column to your model's `$casts`. **

Input preview:

![CRUD Field - checklist](https://backpackforlaravel.com/uploads/docs-4-2/fields/checklist.png)

<hr>

<a name="checklist-dependency"></a>
### checklist_dependency

```php
CRUD::field([   // two interconnected entities
    'label'             => 'User Role Permissions',
    'field_unique_name' => 'user_role_permission',
    'type'              => 'checklist_dependency',
    'name'              => 'roles,permissions', // the methods that define the relationship in your Models
    'subfields'         => [
        'primary' => [
            'label'            => 'Roles',
            'name'             => 'roles', // the method that defines the relationship in your Model
            'entity'           => 'roles', // the method that defines the relationship in your Model
            'entity_secondary' => 'permissions', // the method that defines the relationship in your Model
            'attribute'        => 'name', // foreign key attribute that is shown to user
            'model'            => "Backpack\PermissionManager\app\Models\Role", // foreign key model
            'pivot'            => true, // on create&update, do you need to add/delete pivot table entries?]
            'number_columns'   => 3, //can be 1,2,3,4,6
            'options' => (function ($query) {
                return $query->where('name', '!=', 'admin');
            }), // force the related options to be a custom query, instead of all(); you can use this to filter the available options
        ],
        'secondary' => [
            'label'          => 'Permission',
            'name'           => 'permissions', // the method that defines the relationship in your Model
            'entity'         => 'permissions', // the method that defines the relationship in your Model
            'entity_primary' => 'roles', // the method that defines the relationship in your Model
            'attribute'      => 'name', // foreign key attribute that is shown to user
            'model'          => "Backpack\PermissionManager\app\Models\Permission", // foreign key model
            'pivot'          => true, // on create&update, do you need to add/delete pivot table entries?]
            'number_columns' => 3, //can be 1,2,3,4,6
            'options' => (function ($query) {
                return $query->where('name', '!=', 'admin');
            }), // force the related options to be a custom query, instead of all(); you can use this to filter the available options
        ],
    ],
]);
```

Input preview:

![CRUD Field - checklist_dependency](https://backpackforlaravel.com/uploads/docs-4-2/fields/checklist_dependency.png)

<hr>

<a name="ckeditor"></a>
### ckeditor

Show a WYSIWIG field to the user, powered by CKEditor. This field is provided as a first-party add-on - see instructions [here](https://github.com/Laravel-Backpack/ckeditor-field).

<hr>

<a name="color"></a>
### color

```php
CRUD::field([   // Color
    'name'    => 'background_color',
    'label'   => 'Background Color',
    'type'    => 'color',
    'default' => '#000000',
]);
```

Input preview:

![CRUD Field - color](https://backpackforlaravel.com/uploads/docs-4-2/fields/color.png)

<hr>

<a name="custom-html"></a>
### custom_html

Allows you to insert custom HTML in the create/update forms. Usually used in forms with a lot of fields, to separate them using h1-h5, hr, etc, but can be used for any HTML.

```php
CRUD::field([   // CustomHTML
    'name'  => 'separator',
    'type'  => 'custom_html',
    'value' => '<hr>'
]);
```
**NOTE** If you would like to disable the `wrapper` on this field, eg. when using a `<fieldset>` tag in your custom html, you can achieve it by using `wrapper => false` on field definition.

<a name="date"></a>
### date

```php
CRUD::field([   // Date
    'name'  => 'birthday',
    'label' => 'Birthday',
    'type'  => 'date'
]);
```

Input preview:

![CRUD Field - date](https://backpackforlaravel.com/uploads/docs-4-2/fields/date.png)

<hr>

<a name="datetime"></a>
### datetime

```php
CRUD::field([   // DateTime
    'name'  => 'start',
    'label' => 'Event start',
    'type'  => 'datetime'
]);
```

**Please note:** if you're using datetime [attribute casting](https://laravel.com/docs/5.3/eloquent-mutators#attribute-casting) on your model, you also need to place this mutator inside your model:
```php
	public function setDatetimeAttribute($value) {
		$this->attributes['datetime'] = \Carbon\Carbon::parse($value);
	}
```
Otherwise the input's datetime-local format will cause some errors.

Input preview:

![CRUD Field - datetime](https://backpackforlaravel.com/uploads/docs-4-2/fields/datetime.png)

<hr>

<a name="email"></a>
### email

```php
CRUD::field([   // Email
    'name'  => 'email',
    'label' => 'Email Address',
    'type'  => 'email'
]);
```

Input preview:

![CRUD Field - email](https://backpackforlaravel.com/uploads/docs-4-2/fields/email.png)


<hr>

<a name="enum"></a>
### enum

Show a select with the values for an ENUM database column, or an PHP enum (introduced in PHP 8.1).

##### Database ENUM
When used with a database enum it requires that the database column type is `enum`. In case it's nullable it will also show `-` (empty) option.

PLEASE NOTE the `enum` field using database enums only works for MySQL.

```php
CRUD::field([
    'name'  => 'status',
    'label' => 'Status',
    'type'  => 'enum',
    // optional, specify the enum options with custom display values
    'options' => [
        'DRAFT' => 'Is Draft',
        'PUBLISHED' => 'Is Published'
    ]
]);
```

##### PHP enum

If you are using a `BackedEnum` your best option is to cast it in your model, and Backpack know how to handle it without aditional configuration.

```php
// in your model (eg. Article)

protected $casts = ['status' => \App\Enums\StatusEnum::class]; //assumes you have this enum created

// and in your controller
CRUD::field([
    'name'  => 'status',
    'label' => 'Status',
    'type'  => 'enum'
    // optional
    //'enum_class' => 'App\Enums\StatusEnum',
    //'enum_function' => 'readableStatus',
]);
```

In case it's not a `BackedEnum` or you don't want to cast it in your Model, you should provide the enum class to the field:

```php
CRUD::field([
    'name'  => 'status',
    'label' => 'Status',
    'type'  => 'enum',
    'enum_class' => \App\Enums\StatusEnum::class
]);
```

Input preview:

![CRUD Field - enum](https://backpackforlaravel.com/uploads/docs-4-2/fields/enum.png)

<hr>

<a name="hidden"></a>
### hidden

Include an `<input type="hidden">` in the form.

```php
CRUD::field([   // Hidden
    'name'  => 'status',
    'type'  => 'hidden',
    'value' => 'active',
]);
```

<hr>

<a name="month"></a>
### month

Include an `<input type="month">` in the form.

**Important**: This input type is supported by most modern browsers, but not all. [See compatibility table here](https://caniuse.com/mdn-html_elements_input_type_month). We have a workaround below.

```php
CRUD::field([   // Month
    'name'  => 'month',
    'label' => 'Month',
    'type'  => 'month'
]);
```

Input preview:

![CRUD Field - month](https://backpackforlaravel.com/uploads/docs-4-2/fields/month.png)

**Workaround**

Since not all browsers support this input type, if you are using [Backpack PRO](https://backpackforlaravel.com/products/pro-for-one-project) you can customize the `date_picker` field to have a similar behavior:
```php
CRUD::field([
    'name'  => 'month',
    'type'  => 'date_picker',
    'date_picker_options' => [
        'format'   => 'yyyy-mm',
        'minViewMode' => 'months'
    ],
]);
```
**Important**: you should be using a date/datetime column as database column type if using `date_picker`.

<hr>

<a name="number"></a>
### number

Shows an input type=number to the user, with optional prefix and suffix:

```php
CRUD::field([   // Number
    'name' => 'number',
    'label' => 'Number',
    'type' => 'number',

    // optionals
    // 'attributes' => ["step" => "any"], // allow decimals
    // 'prefix'     => "$",
    // 'suffix'     => ".00",
]);
```

Input preview:

![CRUD Field - number](https://backpackforlaravel.com/uploads/docs-4-2/fields/number.png)

<hr>

<a name="password"></a>
### password

```php
CRUD::field([   // Password
    'name'  => 'password',
    'label' => 'Password',
    'type'  => 'password'
]);
```

Input preview:

![CRUD Field - password](https://backpackforlaravel.com/uploads/docs-4-2/fields/password.png)


Please note that this will NOT hash/encrypt the string before it stores it to the database. You need to hash the password manually. The most popular way to do that are:

1. Using [a mutator on your Model](https://laravel.com/docs/7.x/eloquent-mutators#defining-a-mutator). For example:

```php
public function setPasswordAttribute($value) {
    $this->attributes['password'] = Hash::make($value);
}
```

2. By overwriting the Create/Update operation methods, inside the Controller. There's a working example [in our PermissionManager package](https://github.com/Laravel-Backpack/PermissionManager/blob/master/src/app/Http/Controllers/UserCrudController.php#L103-L124) but the gist of it is this:

```php
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation { store as traitStore; }

    public function store()
    {
        CRUD::setRequest(CRUD::validateRequest());

        /** @var \Illuminate\Http\Request $request */
        $request = CRUD::getRequest();

        // Encrypt password if specified.
        if ($request->input('password')) {
            $request->request->set('password', Hash::make($request->input('password')));
        } else {
            $request->request->remove('password');
        }

        CRUD::setRequest($request);
        CRUD::unsetValidation(); // Validation has already been run

        return $this->traitStore();
    }
```


<hr>

<a name="radio"></a>
### radio

Show radios according to an associative array you give the input and let the user pick from them. You can choose for the radio options to be displayed inline or one-per-line.

```php
CRUD::field([   // radio
    'name'        => 'status', // the name of the db column
    'label'       => 'Status', // the input label
    'type'        => 'radio',
    'options'     => [
        // the key will be stored in the db, the value will be shown as label;
        0 => "Draft",
        1 => "Published"
    ],
    // optional
    //'inline'      => false, // show the radios all on the same line?
]);
```

Input preview:

![CRUD Field - radio](https://backpackforlaravel.com/uploads/docs-4-2/fields/radio.png)

<hr>

<a name="range"></a>
### range

Shows an HTML5 range element, allowing the user to drag a cursor left-right, to pick a number from a defined range. The current value is displayed in a "bubble" that follows the slider thumb, and the min/max values are shown on either side of the slider.

```php
CRUD::field([   // Range
    'name'  => 'range',
    'label' => 'Range',
    'type'  => 'range',
    // optional
    'show_min_max' => true, // default true; set to false to hide the min/max labels on either side of the slider
    'attributes' => [
        'min' => 0,
        'max' => 10,
    ],
]);
```

Input preview:

![CRUD Field - range](https://backpackforlaravel.com/uploads/docs-4-2/fields/range.png)

<hr>

<a name="select"></a>
### select (1-n relationship)

Show a Select with the names of the connected entity and let the user select one of them.
Your relationships should already be defined on your models as hasOne() or belongsTo().

```php
CRUD::field([  // Select
   'label'     => "Category",
   'type'      => 'select',
   'name'      => 'category_id', // the db column for the foreign key

   // optional
   // 'entity' should point to the method that defines the relationship in your Model
   // defining entity will make Backpack guess 'model' and 'attribute'
   'entity'    => 'category',

   // optional - manually specify the related model and attribute
   'model'     => "App\Models\Category", // related model
   'attribute' => 'name', // foreign key attribute that is shown to user

   // optional - force the related options to be a custom query, instead of all();
   'options'   => (function ($query) {
        return $query->orderBy('name', 'ASC')->where('depth', 1)->get();
    }), //  you can use this to filter the results show in the select
]);
```

For more information about the optional attributes that fields use when they interact with related entries - [look here](#optional-attributes-for-fields-containing-related-entries).

Input preview:

![CRUD Field - select](https://backpackforlaravel.com/uploads/docs-4-2/fields/select.png)


<hr>


<a name="select-grouped"></a>
### select_grouped

Display a select where the options are grouped by a second entity (like Categories).

```php
CRUD::field([   // select_grouped
    'label'     => 'Articles grouped by categories',
    'type'      => 'select_grouped', //https://github.com/Laravel-Backpack/CRUD/issues/502
    'name'      => 'article_id',
    'entity'    => 'article',
    'attribute' => 'title',
    'group_by'  => 'category', // the relationship to entity you want to use for grouping
    'group_by_attribute' => 'name', // the attribute on related model, that you want shown
    'group_by_relationship_back' => 'articles', // relationship from related model back to this model
]);
```

For more information about the optional attributes that fields use when they interact with related entries - [look here](#optional-attributes-for-fields-containing-related-entries).

Input preview:

![CRUD Field - select_grouped](https://backpackforlaravel.com/uploads/docs-4-2/fields/select_grouped.png)

<hr>

<a name="select-multiple"></a>
### select_multiple (n-n relationship)

Show a Select with the names of the connected entity and let the user select any number of them.
Your relationship should already be defined on your models as belongsToMany().

```php
CRUD::field([   // SelectMultiple = n-n relationship (with pivot table)
    'label'     => "Tags",
    'type'      => 'select_multiple',
    'name'      => 'tags', // the method that defines the relationship in your Model

    // optional
    'entity'    => 'tags', // the method that defines the relationship in your Model
    'model'     => "App\Models\Tag", // foreign key model
    'attribute' => 'name', // foreign key attribute that is shown to user
    'pivot'     => true, // on create&update, do you need to add/delete pivot table entries?

    // also optional
    'options'   => (function ($query) {
        return $query->orderBy('name', 'ASC')->where('depth', 1)->get();
    }), // force the related options to be a custom query, instead of all(); you can use this to filter the results show in the select
]);
```

For more information about the optional attributes that fields use when they interact with related entries - [look here](#optional-attributes-for-fields-containing-related-entries).

Input preview:

![CRUD Field - select_multiple](https://backpackforlaravel.com/uploads/docs-4-2/fields/select_multiple.png)


<hr>

<a name="select-from-array"></a>
### select_from_array

Display a select with the values you want:

```php
CRUD::field([   // select_from_array
    'name'        => 'template',
    'label'       => "Template",
    'type'        => 'select_from_array',
    'options'     => ['one' => 'One', 'two' => 'Two'],
    'allows_null' => false,
    'default'     => 'one',
    // 'allows_multiple' => true, // OPTIONAL; needs you to cast this to array in your model;
]);
```

Input preview:

![CRUD Field - select_from_array](https://backpackforlaravel.com/uploads/docs-4-2/fields/select_from_array.png)

<hr>

<a name="summernote"></a>
### summernote

Show a [Summernote WYSIWYG editor](http://summernote.org/) to the user.

```php
CRUD::field([   // Summernote
    'name'  => 'description',
    'label' => 'Description',
    'type'  => 'summernote',
    'options' => [],
]);

// the summernote field works with the default configuration options but allow developer to configure to his needs
// optional configuration check https://summernote.org/deep-dive/ for a list of available configs
CRUD::field([
    'name'  => 'description',
    'label' => 'Description',
    'type'  => 'summernote',
    'options' => [
        'toolbar' => [
            ['font', ['bold', 'underline', 'italic']]
        ]
    ],
]);
```

> NOTE: Summernote does NOT sanitize the input. If you do not trust the users of this field, you should sanitize the input or output using something like HTML Purifier. Personally we like to use install [mewebstudio/Purifier](https://github.com/mewebstudio/Purifier) and add an [accessor or mutator](https://laravel.com/docs/8.x/eloquent-mutators#accessors-and-mutators) on the Model, so that wherever the model is created from (admin panel or app), the output will always be clean. [Example here](https://github.com/Laravel-Backpack/demo/commit/7342cffb418bb568b9e4ee279859685ddc0456c1).

#### Uploading files with summernote

Summernote saves images as base64 encoded strings in the database. If you want to save them as files on the server, you can use the [Summernote Uploader](https://backpackforlaravel.com/docs/7.x/crud-uploaders). Please note that the Summernote Uploader is part of the `backpack/pro` package. 
Input preview:

![CRUD Field - summernote](https://backpackforlaravel.com/uploads/docs-4-2/fields/summernote.png)

<hr>

<a name="switch"></a>
### switch

Show a switch (aka toggle) for boolean attributes (true/false). It's an alternative to the `checkbox` field type - prettier and more customizable: it allows the dev to choose the background color and what shows up on the on/off sides of the switch.

```php
CRUD::field([   // Switch
    'name'  => 'switch',
    'type'  => 'switch',
    'label'    => 'I have not read the terms and conditions and I never will',

    // optional
    'color'    => '#232323', // in CoreUI v2 theme you can also specify bootstrap colors, like `primary`, `danger`, `success`, etc You can also overwrite the `--bg-switch-checked-color` css variable to change the color of the switch when it's checked
    'onLabel' => '✓',
    'offLabel' => '✕',
]);
```

Input preview:

![CRUD Field - switch](https://backpackforlaravel.com/uploads/docs-5-0/fields/switch.png)

<hr>

<a name="text"></a>
### text

The basic field type, all it needs is the two mandatory parameters: name and label.

```php
CRUD::field([   // Text
    'name'  => 'title',
    'label' => "Title",
    'type'  => 'text',

    // OPTIONAL
    //'prefix'     => '',
    //'suffix'     => '',
    //'default'    => 'some value', // default value
    //'hint'       => 'Some hint text', // helpful text, show up after input
    //'attributes' => [
       //'placeholder' => 'Some text when empty',
       //'class' => 'form-control some-class',
       //'readonly'  => 'readonly',
       //'disabled'  => 'disabled',
     //], // extra HTML attributes and values your input might need
     //'wrapper'   => [
       //'class' => 'form-group col-md-12'
     //], // extra HTML attributes for the field wrapper - mostly for resizing fields
]);
```

You can use the optional 'prefix' and 'suffix' attributes to display something before and after the input, like icons, path prefix, etc:

Input preview:

![CRUD Field - text](https://backpackforlaravel.com/uploads/docs-4-2/fields/text.png)

<hr>

<a name="textarea"></a>
### textarea

Show a textarea to the user.

```php
CRUD::field([   // Textarea
    'name'  => 'description',
    'label' => 'Description',
    'type'  => 'textarea'
]);
```

Input preview:

![CRUD Field - textarea](https://backpackforlaravel.com/uploads/docs-4-2/fields/textarea.png)

<hr>

<a name="time"></a>
### time

```php
CRUD::field([   // Time
    'name'  => 'start',
    'label' => 'Start time',
    'type'  => 'time'
]);
```

<hr>

<a name="tinymce"></a>
### tinymce

Show a WYSIWYG editor powered by TinyMCE to the admin. This is provided using a first-party add-on - see instructions [here](https://github.com/Laravel-Backpack/tinymce-field).

<hr>

<a name="upload"></a>
### upload

**Step 1.** Show a file input to the user:
```php
CRUD::field([   // Upload
    'name'      => 'image',
    'label'     => 'Image',
    'type'      => 'upload',
]);
```

**Step 2.** Choose how to handle the file upload process. Starting v6, you have two options:
- **Option 1.** Let Backpack handle the upload process for you. This is by far the most convenient option, because it's the easiest to implement and fully customizable. All you have to do is add the `withFiles => true` attribute to your field definition:
```php
CRUD::field([   // Upload
    'name'      => 'image',
    'label'     => 'Image',
    'type'      => 'upload',
    'withFiles' => true
]);
```
To know more about the `withFiles`, how it works and how to configure it, [ click here to read the documentation ](https://backpackforlaravel.com/docs/6.x/crud-uploaders).

- **Option 2.** Handle the upload process yourself. This is what happened in v5, so if you want to handle the upload by yourself you can [read the v5 upload docs here](https://backpackforlaravel.com/docs/5.x/crud-fields#upload-1).

**Upload Field Validation**

You can use standard Laravel validation rules. But we've also made it easy for you to validate the `upload` fields, using a [Custom Validation Rule](/docs/{{version}}/custom-validation-rules). The `ValidUpload` validation rule allows you to define two sets of rules:
- `::field()` - the field rules (independent of the file content);
- `->file()` - rules that apply to the sent file;

This helps you avoid most quirks when validating file uploads using Laravel's validation rules.

```php
use Backpack\CRUD\app\Library\Validation\Rules\ValidUpload;

'image' => ValidUpload::field('required')
                ->file('file|mimes:jpeg,png,jpg,gif,svg|max:2048'),
```

Input preview:

![CRUD Field - upload](https://backpackforlaravel.com/uploads/docs-4-2/fields/upload.png)

<hr>

<a name="upload-multiple"></a>
### upload_multiple

Shows a multiple file input to the user and stores the values as a JSON array in the database.

**Step 0.** Make sure the db column can hold the amount of text this field will have. For example, for MySQL, `VARCHAR(255)` might not be enough all the time (for 3+ files), so it's better to go with `TEXT`. Make sure you're using a big column type in your migration or db.

**Step 1.** Show a multiple file input to the user:
```php
CRUD::field([
    'name'      => 'photos',
    'label'     => 'Photos',
    'type'      => 'upload_multiple',
]);
```

**Step 2.** Choose how to handle the file upload process. Starting v6, you have two options:
- **Option 1.** Let Backpack handle the upload process for you. This is by far the most convenient option, because it's the easiest to implement and fully customizable. All you have to do is add the `withFiles => true` attribute to your field definition:
```php
CRUD::field([
    'name'      => 'photos',
    'label'     => 'Photos',
    'type'      => 'upload_multiple',
    'withFiles' => true
]);
```
To know more about the `withFiles`, how it works and how to configure it, [ click here to read the documentation ](https://backpackforlaravel.com/docs/6.x/crud-uploaders).

- **Option 2.** Handle the upload process yourself. This is what happened in v5, so if you want to handle the upload by yourself you can [read the v5 upload docs here](https://backpackforlaravel.com/docs/5.x/crud-fields#upload_multiple).

**Validation**

You can use standard Laravel validation rules. But we've also made it easy for you to validate the `upload` fields, using a [Custom Validation Rule](/docs/{{version}}/custom-validation-rules). The `ValidUploadMultiple` validation rule allows you to define two sets of rules:
- `::field()` - the input rules, independant of the content;
- `file()` - rules that apply to each file that gets sent;

This will help you avoid most quirks of using Laravel's standard validation rules alone.

```php
use Backpack\CRUD\app\Library\Validation\Rules\ValidUploadMultiple;

'photos' => ValidUploadMultiple::field('required|min:2|max:5')
                ->file('file|mimes:jpeg,png,jpg,gif,svg|max:2048'),
```

**NOTE**: This field uses a `clear_{fieldName}` input to send the deleted files from the frontend to the backend. In case you are using `$guarded` add it there.
Eg: `protected $guarded = ['id', 'clear_photos'];`

Input preview:

![CRUD Field - upload_multiple](https://backpackforlaravel.com/uploads/docs-4-2/fields/upload_multiple.png)

<hr>

<a name="url"></a>
### url

```php
CRUD::field([   // URL
    'name'  => 'link',
    'label' => 'Link to video file',
    'type'  => 'url'
]);
```

<hr>

<a name="view"></a>
### view

Load a custom view in the form.

```php
CRUD::field([   // view
    'name' => 'custom-ajax-button',
    'type' => 'view',
    'view' => 'partials/custom-ajax-button'
]);
```

**Note:** the same functionality can be achieved using a [custom field type](/docs/{{version}}/crud-fields#creating-a-custom-field-type), or using the [custom_html field type](/docs/{{version}}/crud-fields#custom-html) (if the content is really simple).

**NOTE** If you would like to disable the `wrapper` on this field, you can achieve it by using `wrapper => false` on field definition.

<hr>

<a name="week"></a>
### week

Include an `<input type="week">` in the form.

**Important**: This input type is supported by most modern browsers, not all. [See compatibility table here](https://caniuse.com/mdn-html_elements_input_type_week).

```php
CRUD::field([   // Week
    'name'  => 'first_week',
    'label' => 'First week',
    'type'  => 'week'
]);
```

Input preview:

![CRUD Field - week](https://backpackforlaravel.com/uploads/docs-4-2/fields/week.png)



<a name="pro-field-types"></a>
## PRO Field Types


<a name="address_google"></a>
### address_google <span class="badge badge-pill badge-info">PRO</span>

Use [Google Places Search](https://developers.google.com/places/web-service/search) to help users type their address faster. With the ```store_as_json``` option, it will store the address, postcode, city, country, latitude and longitude in a JSON in the database. Without it, it will just store the complete address string.

```php
CRUD::field([   // Address google
    'name'          => 'address',
    'label'         => 'Address',
    'type'          => 'address_google',
    // optional
    'store_as_json' => true
]);
```

Using Google Places API is dependent on using an API Key. Please [get an API key](https://console.cloud.google.com/apis/credentials) - you do have to configure billing, but you qualify for $200/mo free usage, which covers most use cases. Then copy-paste that key as your ```services.google_places.key``` value. 

**IMPORTANT NOTE**: Your key needs access to the following APIS:
- Maps JavaScript API;
- Places API;
- Geocoding API.

While developing you can use an "unrestricted key" (no restrictions for where the key is used), but for production you should use a separate key, and **MAKE SURE** you restrict the usage of that key to your own domain. 

So inside your ```config/services.php``` please add the items below:
```php
'google_places' => [
    'key' => 'the-key-you-got-from-google-places'
],
```
Alternatively you can set the key in your field definition, but we do **not recommend** it: 
```php
[
    'name' => 'google_field',
    'api_key' => 'the-key-you-got-from-google-places'
]
```

> **Use attribute casting.** For information stored as JSON in the database, it's recommended that you use [attribute casting](https://mattstauffer.com/blog/laravel-5.0-eloquent-attribute-casting) to ```array``` or ```object```. That way, every time you get the info from the database you'd get it in a usable format. Also, it is heavily recommended that your database column can hold a large JSON - so use `text` rather than `string` in your migration (in MySQL this translates to `text` instead of `varchar`).

Input preview:

![CRUD Field - address](https://backpackforlaravel.com/uploads/docs-4-2/fields/address_google.png)

<hr>

<a name="base64-image"></a>
### base64_image <span class="badge badge-pill badge-info">PRO</span>

Upload an image and store it in the database as Base64. Notes:
- make sure the column type is LONGBLOB;
- detailed [instructions and customisations here](https://github.com/Laravel-Backpack/CRUD/pull/56#issue-164712261);

```php
// base64_image
CRUD::field([
    'label'        => "Profile Image",
    'name'         => "image",
    'filename'     => "image_filename", // set to null if not needed
    'type'         => 'base64_image',
    'aspect_ratio' => 1, // set to 0 to allow any aspect ratio
    'crop'         => true, // set to true to allow cropping, false to disable
    'src'          => NULL, // null to read straight from DB, otherwise set to model accessor function
]);
```

Input preview:

![CRUD Field - base64_image](https://backpackforlaravel.com/uploads/docs-4-2/fields/base64_image.png)

<hr>

<a name="code_mirror"></a>
### code_mirror <span class="badge badge-pill badge-info">PRO</span>

Show a [CodeMirror](https://codemirror.net/) code editor. It supports syntax highlighting for multiple languages and themes.

```php
CRUD::field([
    'name' => 'code',
    'type' => 'code_mirror',
    'label' => 'Code',
    // optional
    'configuration' => [
        'theme' => 'monokai', // options: monokai, dracula, material, eclipse, idea
        'mode' => 'javascript', // options: javascript, xml, css, htmlmixed, php, sql, python
        'height' => '300px',
    ]
]);
```

Input preview:

![CRUD Field - Code_Mirror_Editor](https://backpackforlaravel.com/uploads/docs/code_mirror_field.png)

<hr>

<a name="date-range"></a>
### date_range <span class="badge badge-pill badge-info">PRO</span>

Show a DateRangePicker and let the user choose a start date and end date.

```php
CRUD::field([   // date_range
    'name'  => 'start_date,end_date', // db columns for start_date & end_date
    'label' => 'Event Date Range',
    'type'  => 'date_range',

    // OPTIONALS
    // default values for start_date & end_date
    'default'            => ['2019-03-28 01:01', '2019-04-05 02:00'],
    // options sent to daterangepicker.js
    'date_range_options' => [
        'drops' => 'down', // can be one of [down/up/auto]
        'timePicker' => true,
        'locale' => ['format' => 'DD/MM/YYYY HH:mm']
    ]
]);
```

Please note it is recommended that you use [attribute casting](https://laravel.com/docs/5.3/eloquent-mutators#attribute-casting) on your model (cast to date).

Your end result will look like this:

Input preview:

![CRUD Field - date_range](https://backpackforlaravel.com/uploads/docs-4-2/fields/date_range.png)



<hr>

<a name="date-picker"></a>
### date_picker <span class="badge badge-pill badge-info">PRO</span>

Show a pretty [Bootstrap Datepicker](http://bootstrap-datepicker.readthedocs.io/en/latest/).

```php
CRUD::field([   // date_picker
   'name'  => 'date',
   'type'  => 'date_picker',
   'label' => 'Date',

   // optional:
   'date_picker_options' => [
      'todayBtn' => 'linked',
      'format'   => 'dd-mm-yyyy',
      'language' => 'fr'
   ],
]);
```

Please note it is recommended that you use [attribute casting](https://laravel.com/docs/5.3/eloquent-mutators#attribute-casting) on your model (cast to date).


Input preview:

![CRUD Field - date_picker](https://backpackforlaravel.com/uploads/docs-4-2/fields/date_picker.png)

<hr>

<a name="datetime-picker"></a>
### datetime_picker <span class="badge badge-pill badge-info">PRO</span>

Show a [Bootstrap Datetime Picker](https://eonasdan.github.io/bootstrap-datetimepicker/).

```php
CRUD::field([   // DateTime
    'name'  => 'start',
    'label' => 'Event start',
    'type'  => 'datetime_picker',

    // optional:
    'datetime_picker_options' => [
        'format' => 'DD/MM/YYYY HH:mm',
        'language' => 'pt',
        'tooltips' => [ //use this to translate the tooltips in the field
                'today' => 'Hoje',
                'selectDate' => 'Selecione a data',
                // available tooltips: today, clear, close, selectMonth, prevMonth, nextMonth, selectYear, prevYear, nextYear, selectDecade, prevDecade, nextDecade, prevCentury, nextCentury, pickHour, incrementHour, decrementHour, pickMinute, incrementMinute, decrementMinute, pickSecond, incrementSecond, decrementSecond, togglePeriod, selectTime, selectDate
        ]
    ],
    'allows_null' => true,
    // 'default' => '2017-05-12 11:59:59',
]);
```

**Please note:** if you're using date [attribute casting](https://laravel.com/docs/5.3/eloquent-mutators#attribute-casting) on your model, you may also need to place this mutator inside your model:
```php
    public function setDatetimeAttribute($value) {
        $this->attributes['datetime'] = \Carbon\Carbon::parse($value);
    }
```
Otherwise the input's datetime-local format will cause some errors. Remember to change "datetime" with the name of your attribute (column name).

Input preview:

![CRUD Field - datetime_picker](https://backpackforlaravel.com/uploads/docs-4-2/fields/datetime_picker.png)

<hr>

<a name="dropzone"></a>
### dropzone <span class="badge badge-pill badge-info">PRO</span>

Show a [Dropzone JS Input](https://docs.dropzone.dev/).

**Step 0.** Make sure the model attribute can hold all the information needed. Ideally, your model should cast this attribute as `array` and your migration should make the db column either `TEXT` or `JSON`. Other db column types such as `VARCHAR(255)` might not be enough all the time (for 3+ files).

**Step 1:** Add the `AjaxUploadOperation` to your `EntityCrudController` [read more about the ajax upload operation](https://backpackforlaravel.com/docs/crud-operation-ajax-upload).

```php
class EntityCrudController extends CrudController
{
    // ... other operations
    use \Backpack\Pro\Http\Controllers\Operations\AjaxUploadOperation;
}
```

**Step 2:** Add the field in EntityCrudController

```php
CRUD::field([
    'name'  => 'photos',
    'label' => 'Photos',
    'type'  => 'dropzone',
    'withFiles' => true,
    // optional configuration.
    // check available options in https://docs.dropzone.dev/configuration/basics/configuration-options
    // 'configuration' => [
    //     'parallelUploads' => 2,
    // ]
]);
```

Alternatively, you can manually implement the saving process yourself using model events, mutators or any other solution that suits you. To know more about the `withFiles`, how it works and how to configure it, [read its documentation](https://backpackforlaravel.com/docs/crud-uploaders).


**Step 4:** **VALIDATE YOUR INPUT**. Yes you can do some basic validation in Javascript, but we highly advise you to prioritize server-side validation. To make validation easy we created `ValidDropzone` validation rule. It allows you to define two set of rules:
- `::field()` - the field rules (independent of the file content);
- `->file()` - rules that apply to the sent files;

```php
use Backpack\Pro\Uploads\Validation\ValidDropzone;

'photos' => ValidDropzone::field('required|min:2|max:5')
                ->file('file|mimes:jpeg,png,jpg,gif,svg|max:2048'),
```

Input preview:

![CRUD Field - dropzone](https://user-images.githubusercontent.com/7188159/236273902-ca7fb5a5-e7ce-4a03-91a7-2af81598331c.png)

<hr>

<a name="easymde"></a>
### easymde <span class="badge badge-pill badge-info">PRO</span>

Show an [EasyMDE - Markdown Editor](https://github.com/Ionaru/easy-markdown-editor) to the user. EasyMDE is a well-maintained fork of SimpleMDE.

```php
CRUD::field([   // easymde
    'name'  => 'description',
    'label' => 'Description',
    'type'  => 'easymde',
    // optional
    // 'easymdeAttributes' => [
    //   'promptURLs'   => true,
    //   'status'       => false,
    //   'spellChecker' => false,
    //   'forceSync'    => true,
    // ],
    // 'easymdeAttributesRaw' => $some_json
]);
```

> NOTE: The contents displayed in this editor are NOT stripped, sanitized or escaped by default. Whenever you store Markdown or HTML inside your database, it's HIGHLY recommended that you sanitize the input or output. Laravel makes it super-easy to do that on the model using [accessors](https://laravel.com/docs/8.x/eloquent-mutators#accessors-and-mutators). If you do NOT trust the admins who have access to this field (or end-users can also store information to this db column), please make sure this attribute is always escaped, before it's shown. You can do that by running the value through `strip_tags()` in an accessor on the model (here's [an example](https://github.com/Laravel-Backpack/demo/commit/509c0bf0d8b9ee6a52c50f0d2caed65f1f986385)) or better yet, using an [HTML Purifier package](https://github.com/mewebstudio/Purifier) (here's [an example](https://github.com/Laravel-Backpack/demo/commit/7342cffb418bb568b9e4ee279859685ddc0456c1)).

#### Uploading images using EasyMDE drag & drop

Starting Backpack 6.7 you can now upload images using drag & drop directly into the EasyMDE editor. To enable this feature you need to follow these steps:

**Step 1:** Add the `AjaxUploadOperation` to your `EntityCrudController` where you defined your easyMDE field.
**Step 2:** Add the `withFiles => true` attribute to your field definition. You can check other available options in the [uploaders documentation](https://backpackforlaravel.com/docs/crud-uploaders).

**Note:** EasyMDE provides some basic javascript file validation. By default only `jpg, jpeg, png, gif, svg, webp` are allowed and files up to 2MB. You can change this by setting the `imageMaxSize` and `imageAccept` options in the `easymdeAttributes` attribute. Eg:

```php
'easymdeAttributes' => [
    'imageMaxSize' =>  1024 * 5, // up to 5MB
    'imageAccept' => ['image/gif'], // to only accept gifs 
]
```

Input preview:

![CRUD Field - easymde](https://backpackforlaravel.com/uploads/docs-4-2/fields/easymde.png)

<hr>

<a name="google-map"></a>
### google_map <span class="badge badge-pill badge-info">PRO</span>

Shows a map and allows the user to navigate and select a position on that map (using the Google Places API). The field stores the latitude, longitude and the address string as a JSON in the database ( eg. `{lat: 123, lng: 456, formatted_address: 'Lisbon, Portugal'}`). If you want to save the info in separate db columns, continue reading below.

```php
CRUD::field([
    'name' => 'location',
    'type' => 'google_map',
    // optionals
    'map_options' => [
        'default_lat' => 123,
        'default_lng' => 456,
        'locate' => false, // when false, only a map is displayed. No value for submition.
        'height' => 400 // in pixels
    ]
]);
```

Using Google Places API is dependent on using an API Key. Please [get an API key](https://console.cloud.google.com/apis/credentials) - you do have to configure billing, but you qualify for $200/mo free usage, which covers most use cases. Then copy-paste that key as your ```services.google_places.key``` value. So inside your ```config/services.php``` please add the items below:

```php
'google_places' => [
    'key' => 'the-key-you-got-from-google-places'
],
```

**IMPORTANT NOTE**: Your key needs access to the following APIS:
- Maps JavaScript API;
- Places API;
- Geocoding API.

While developing you can use an "unrestricted key" (no restrictions for where the key is used), but for production you should use a separate key, and **MAKE SURE** you restrict the usage of that key to your own domain. 

**How to save in multiple inputs?**

There are cases where you rather save the information on separate inputs in the database. In that scenario you should use [Laravel mutators and accessors](https://laravel.com/docs/10.x/eloquent-mutators). Using the same field as previously shown (**field name is `location`**), and having `latitude`, `longitude`, `full_address` as the database columns, we can save and retrieve them separately too:
```php

//add all the fields to model fillable property, including the one that we are not going to save (location in the example)
$fillable = ['location', 'latitude', 'longitude', 'full_address'];

//
protected function location(): \Illuminate\Database\Eloquent\Casts\Attribute
{
    return \Illuminate\Database\Eloquent\Casts\Attribute::make(
        get: function($value, $attributes) {
            return json_encode([
            'lat' => $attributes['lat'],
            'lng' => $attributes['lng'],
            'formatted_address' => $attributes['full_address'] ?? ''
            ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_THROW_ON_ERROR);
        },
        set: function($value) {
            $location = json_decode($value);
            return [
                'lat' => $location->lat,
                'lng' => $location->lng,
                'full_address' => $location->formatted_address ?? ''
            ];
        }
    );
}

```

Input preview:

![image](https://user-images.githubusercontent.com/7188159/208295372-f2dcbe71-73b7-452d-9904-428f725cdbce.png)

<hr>

<a name="icon-picker"></a>
### icon_picker <span class="badge badge-pill badge-info">PRO</span>

Show an icon picker. Supported icon sets are fontawesome, glyphicon, ionicon, weathericon, mapicon, octicon, typicon, elusiveicon, materialdesign as per the jQuery plugin, [bootstrap-iconpicker](http://victor-valencia.github.io/bootstrap-iconpicker/).

The stored value will be the class name (ex: fa-home).

```php
CRUD::field([   // icon_picker
    'label'   => "Icon",
    'name'    => 'icon',
    'type'    => 'icon_picker',
    'iconset' => 'fontawesome' // options: fontawesome, glyphicon, ionicon, weathericon, mapicon, octicon, typicon, elusiveicon, materialdesign
]);
```

Your input will look like button, with a dropdown where the user can search or pick an icon:

Input preview:

![CRUD Field - icon_picker](https://backpackforlaravel.com/uploads/docs-4-2/fields/icon_picker.png)

<hr>

<a name="image"></a>
### image <span class="badge badge-pill badge-info">PRO</span>

Upload an image and store it on the disk.

**Step 1.** Show the field.
```php
// image
CRUD::field([
    'label' => 'Profile Image',
    'name' => 'image',
    'type' => 'image',
    'crop' => true, // set to true to allow cropping, false to disable
    'aspect_ratio' => 1, // omit or set to 0 to allow any aspect ratio
]);
```
**NOTE:** `aspect_ratio` is a float that represents the ratio of the cropping rectangle height and width. Eg: Square = 1, Landscape = 2, Portrait = 0.5. You can, of course, use any value for more extreme rectangles.

**Step 2.** Choose how to handle the file upload process. Starting v6, you have two options:
- **Option 1.** Let Backpack handle the upload process for you. This is by far the most convenient option, because it's the easiest to implement and fully customizable. All you have to do is add the `withFiles => true` attribute to your field definition:
```php
CRUD::field([
    'name' => 'image',
    'label' => 'Profile Image',
    'type' => 'image',
    'withFiles' => true
]);
```
To know more about the `withFiles`, how it works and how to configure it, [ click here to read the documentation ](https://backpackforlaravel.com/docs/6.x/crud-uploaders).

- **Option 2.** Handle the upload process yourself. This is what happened in v5, so if you want to handle the upload by yourself you can [read the v5 upload docs here](https://backpackforlaravel.com/docs/5.x/crud-fields#image-pro).

Input preview:

![CRUD Field - image](https://backpackforlaravel.com/uploads/docs-4-2/fields/image.png)

> NOTE: if you are having trouble uploading big images, please check your php extensions **apcu** and/or **opcache**, users have reported some issues with these extensions when trying to upload very big images. REFS: https://github.com/Laravel-Backpack/CRUD/issues/3457

<hr>

<a name="phone"></a>
### phone <span class="badge badge-pill badge-info">PRO</span>

Show a telephone number input. Lets the user choose the prefix using a flag from dropdown.

```php
CRUD::field([   // phone
    'name'  => 'phone', // db column for phone
    'label' => 'Phone',
    'type'  => 'phone',

    // OPTIONALS
    // most options provided by intlTelInput.js are supported, you can try them out using the `config` attribute;
    //  take note that options defined in `config` will override any default values from the field;
    'config' => [
        'onlyCountries' => ['bd', 'cl', 'in', 'lv', 'pt', 'ro'],
        'initialCountry' => 'cl', // this needs to be in the allowed country list, either in `onlyCountries` or NOT in `excludeCountries`
        'separateDialCode' => true,
        'nationalMode' => true,
        'autoHideDialCode' => false,
        'placeholderNumberType' => 'MOBILE',
    ]
]);
```

For more info about parameters please see this JS plugin's [official documentation](https://github.com/jackocnr/intl-tel-input).

Your end result will look like this:

![CRUD Field - phone](https://user-images.githubusercontent.com/1032474/204588174-48935030-54e6-4a30-b34c-7e94220ae242.png)

> NOTE: you can validate this using Laravel's default **numeric** or if you want something advanced, we recommend [Laravel Phone](https://github.com/Propaganistas/Laravel-Phone)

<hr>

<a name="relationship"></a>
### relationship <span class="badge badge-pill badge-info">PRO</span>

Shows the user a `select2` input, allowing them to choose one/more entries of a related Eloquent Model. In order to work, the relationships need to be properly defined on the Model.

Input preview (for both 1-n and n-n relationships):

![CRUD Field - relationship](https://backpackforlaravel.com/uploads/docs-4-2/fields/relationship.png)

To achieve the above, you just need to point the field to the relationship method on your Model (eg. `category`, not `category_id`):

```php
CRUD::field([   // relationship
    'name' => 'category', // the method on your model that defines the relationship
    'type' => "relationship",

    // OPTIONALS:
    // 'label' => "Category",
    // 'attribute' => "title", // attribute on model that is shown to user
    // 'placeholder' => "Select a category", // placeholder for the select2 input
 ]);
```

More more optional attributes on relationship fields [look here](#optional-attributes-for-fields-containing-related-entries).

Out of the box, it supports all common relationships:
- ✅ `hasOne` (1-1) - shows subform if you define `subfields`
- ✅ `belongsTo` (n-1) - shows a select2 (single)
- ✅ `hasMany` (1-n) - shows a select2_multiple OR a subform if you define `subfields`
- ✅ `belongsToMany` (n-n) - shows a select2_multiple OR a subform if you define `subfields` for pivot extras
- ✅ `morphOne` (1-1) - shows a subform if you define `subfields`
- ✅ `morphMany` (1-n) - shows a select2_multiple OR a subform if you define `subfields`
- ✅ `morphToMany` (n-n) - shows a select2_multiple OR a subform if you define `subfields` for pivot extras
- ✅ `morphTo` (n-1) - shows the `_type` and `_id` selects for morphTo relations

It does NOT support the following Eloquent relationships, since they don't make sense in this context:
- ❌ `hasOneThrough` (1-1-1) - it's read-only, no sense having a field for it;
- ❌ `hasManyThrough` (1-1-n) - it's read-only, no sense having a field for it;
- ❌ Has One Of Many (1-n turned into 1-1) - it's read-only, no sense having a field for it;
- ❌ Morph One Of Many (1-n turned into 1-1) - it's read-only, no sense having a field for it;
- ❌ `morphedByMany` (n-n inverse) - never needed, UI would be very difficult to understand & use at this moment.

The relationship field is a plug-and-play solution, 90% of the time it will cover all you need by just pointing it to a relationship on the model. But it also has a few optional features, that will greatly help you out in more complex use cases:

<a name="load-entries-from-ajax-calls-using-the-fetch-operation"></a>
#### Load entries from AJAX calls - using the Fetch Operation

If your related entry has hundreds, thousands or millions of entries, it's not practical to load the options using an onpage Eloquent query, because the Create/Update page would be very slow to load. In this case, you should instruct the `relationship` field to fetch the entries using AJAX calls.

**Step 1.** Add `'ajax' => true` to your relationship field definition:

```php
CRUD::field([   // relationship
    'type' => "relationship",
    'name' => 'category', // the method on your model that defines the relationship
    'ajax' => true,

    // OPTIONALS:
    // 'label'       => "Category",
    // 'attribute'   => "name", // foreign key attribute that is shown to user (identifiable attribute)
    // 'entity'      => 'category', // the method that defines the relationship in your Model
    // 'model'       => "App\Models\Category", // foreign key Eloquent model
    // 'placeholder' => "Select a category", // placeholder for the select2 input

    // AJAX OPTIONALS:
    // 'delay'                   => 500, // the minimum amount of time between ajax requests when searching in the field
    // 'data_source'             => url("fetch/category"), // url to controller search function (with /{id} should return model)
    // 'minimum_input_length'    => 2, // minimum characters to type before querying results
    // 'dependencies'            => ['category'], // when a dependency changes, this select2 is reset to null
    // 'method'                  => 'POST', // optional - HTTP method to use for the AJAX call (GET, POST)
    // 'include_all_form_fields' => false, // optional - only send the current field through AJAX (for a smaller payload if you're not using multiple chained select2s)
 ]);
```

**Step 2.** Create the route and method that responds to the AJAX calls. Fortunately, the `FetchOperation` allows you to easily do just that. Inside the same CrudController where you've defined the `relationship` field, use the `FetchOperation` trait, and define a new method that will respond to AJAX queries for that entity:

```php
    use \Backpack\CRUD\app\Http\Controllers\Operations\FetchOperation;

    public function fetchCategory()
    {
        return $this->fetch(\App\Models\Category::class);
    }
```

This will set up a ```/fetch/category``` route, which points to ```fetchCategory()```, which returns the search results. For more on how this operation works (and how you can customize it), see the [FetchOperation docs](/docs/{{version}}/crud-operation-fetch).


<a name="create-related-entries-in-a-modal-using-the-inlinecreate-operation"></a>
#### Create related entries in a modal - using the InlineCreate Operation

Works for the `BelongsTo`, `BelongsToMany` and `MorphToMany` relationships.

Searching with AJAX provides a great UX. But what if the user doesn't find what they're looking for? In that case, it would be useful to add a related entry on-the-fly, without leaving the main form:

![CRUD Field - relationship fetch with inline create](https://backpackforlaravel.com/uploads/docs-4-2/fields/relationship_inlineCreate.gif)

If you are using the Fetch operation to get the entries, you're already halfway there. In addition to using Fetch as instructed in the section above, you should perform two additional steps:

**Step 1.** Add `inline_create` to your field definition in **the current CrudController**:

```php
// for 1-n relationships (ex: category)
CRUD::field([
    'type'          => "relationship",
    'name'          => 'category',
    'ajax'          => true,
    'inline_create' => true, // <--- THIS
]);
// for n-n relationships (ex: tags)
CRUD::field([
    'type'          => "relationship",
    'name'          => 'tags', // the method on your model that defines the relationship
    'ajax'          => true,
    'inline_create' => [ 'entity' => 'tag' ]  // <--- OR THIS
]);
// in this second example, the relation is called `tags` (plural),
// but we need to define the entity as "tag" (singural)
```

**Step 2.** On the CrudController of that secondary entity (that the user will be able to create on-the-fly, eg. `CategoryCrudController` or `TagCrudController`), you'll need to enable the InlineCreate operation:
```php
class CategoryCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\InlineCreateOperation;

   // ...
}
```

This ```InlineCreateOperation``` will allow us to show _the same fields that are inside the Create operation_, inside a new operation _InlineCreate_, that is available in a modal. For more information, check out the [InlineCreate Operation docs](/docs/{{version}}/crud-operation-inline-create).

Remember, ```FetchOperation``` is still needed on the main crud (ex: ```ArticleCrudController```) so that the entries are fetched by ```select2``` using AJAX.

#### Save additional data to pivot table

For relationships with a pivot table (n-n relationships: `BelongsToMany`, `MorphToMany`), that contain other columns other than the foreign keys themselves, the `relationship` field provides a quick way for your admin to edit those "extra attributes on the pivot table". For example, if you have these database tables:

```php
// - companies: id, name
// - company_person: company_id, person_id, job_title, job_description
// - persons: id, first_name, last_name
```

You might want the admin to define the `job_title` and `job_description` of a person, when creating/editing a company. So instead of this:


![CRUD Field - belongsToMany relationship without custom pivot table attributes](https://backpackforlaravel.com/uploads/docs-4-2/fields/relationship_belongsToMany_noPivot.png)

you might your admin to see this:

![CRUD Field - belongsToMany relationship with custom pivot table attributes](https://backpackforlaravel.com/uploads/docs-4-2/fields/relationship_belongsToMany_withPivot.png)



The `relationship` field allows you to easily do that. Here's how:

**Step 1.** On your models, make sure the extra database columns are defined, using `withPivot()`:

```php
// inside the Company model
public function people()
{
    return $this->belongsToMany(\App\Models\Person::class)
                ->withPivot('job_title', 'job_description');
}
// inside the Person model
public function companies()
{
    return $this->belongsToMany(\App\Models\Company::class)
                ->withPivot('job_title', 'job_description');
}
```

**Step 2.** Inside your `relationship` field definition, add `subfields` for those two db columns:

```php
// Inside PersonCrudController
CRUD::field([
    'name'          => 'companies',
    'type'          => "relationship",
     // ..
    'subfields'   => [
        [
            'name' => 'job_title',
            'type' => 'text',
            'wrapper' => [
                'class' => 'form-group col-md-3',
            ],
        ],
        [
            'name' => 'job_description',
            'type' => 'text',
            'wrapper' => [
                'class' => 'form-group col-md-9',
            ],
        ],
    ],
]);
```

**That's it.** Backpack now will save those additional inputs on the pivot table.

#### Allow user to select multiple times the same pivot

By default Backpack does not allow you to select the same pivot twice. If you want to allow the selection of the same pivot more than once you should take some setup steps before. Follow along with the steps below:

**1)** Make sure your pivot table has a unique key, usually an auto-increment id. If you don't have one, you can add it with a migration. 

**2)** Add the `id` to your `->withPivot()` fields on your relation. Eg:
```php

$this->belongsToMany(\App\Models\Company::class)
                ->withPivot('job_title', 'job_description', 'id');
```

If you unique identifier is not called `id`, you can tell Backpack the appropriate name using `pivot_key_name` in your field definition. Eg:
```php
CRUD::field([
    'name'          => 'companies',
    'type'          => 'relationship',
    'pivot_key_name' => 'uuid',
    // ...
]);
```

**3)** Add the `allow_duplicate_pivots` attribute to your relationship field definition. Eg:
```php
CRUD::field([
    'name'          => 'companies',
    'type'          => 'relationship',
    'allow_duplicate_pivots' => true,
    'subfields' => // your subfields (do not add `id` as a subfield. That's done automatically by Backpack). 
]);
```

#### Configuring the Pivot Select field
If you want to change something about the primary select (the pivot select field created by Backpack), you can do that using the `pivotSelect` attribute:

```php
CRUD::field([
    'name'          => 'companies',
    'type'          => "relationship",
    'subfields'   => [ ... ],
     // you can use the same configurations you use in any relationship select
     // like making it an ajax, constraining the options etc.
    'pivotSelect'=> [
        // 'attribute' => "title", // attribute on model that is shown to user
        'placeholder' => 'Pick a company',
        'wrapper' => [
            'class' => 'col-md-6',
        ],
        // by default we call a $model->all(). you can configure it like in any other select
        'options' => function($model) {
            return $model->where('type', 'primary');
        },
        // note that just like any other select, enabling ajax will require you to provide an url for the field
        // to fetch available options. You can use the FetchOperation or manually create the enpoint.
        'ajax' => true,
        'data_source' => backpack_url('fetch'),
    ],
]);
```

#### Manage related entries in the same form (create, update, delete)

Sometimes for `hasMany` and `morphMany` relationships, the secondary entry cannot stand on its own. It's so dependent on the primary entry, that you don't want it to have a Create/Update form of its own - you just want it to be managed inside its parent. Let's take `Invoice` and `InvoiceItem` as an example, with the following database structure:

```php
// - invoices: id, number, due_date, payment_status
// - invoice_items: id, order, description, unit, quantity, unit_price
```

Most likely, what you _really_ want is to be able to create/update/delete `InvoiceItems` right inside the `Invoice` form:

![CRUD Field - hasMany relationship editing the items on-the-fly](https://backpackforlaravel.com/uploads/docs-4-2/fields/repeatable_hasMany_entries.png)

To achieve the above, you just need to add a `relationship` field for your `hasMany` or `morphMany` relationship and define the `subfields` you want for that secondary Model:

```php
CRUD::field([
    'name'          => 'items',
    'type'          => "relationship",
    'subfields'   => [
        [
            'name' => 'order',
            'type' => 'number',
            'wrapper' => [
                'class' => 'form-group col-md-1',
            ],
        ],
        [
            'name' => 'description',
            'type' => 'text',
            'wrapper' => [
                'class' => 'form-group col-md-6',
            ],
        ],
        [
            'name' => 'unit',
            'label' => 'U.M.',
            'type' => 'text',
            'wrapper' => [
                'class' => 'form-group col-md-1',
            ],
        ],
        [
            'name' => 'quantity',
            'type' => 'number',
            'attributes' => ["step" => "any"],
            'wrapper' => [
                'class' => 'form-group col-md-2',
            ],
        ],
        [
            'name' => 'unit_price',
            'type' => 'number',
            'attributes' => ["step" => "any"],
            'wrapper' => [
                'class' => 'form-group col-md-2',
            ],
        ],
    ],
]);
```

Backpack will then take care of the creating/updating/deleting of the secondary model, after the "Save" button is clicked on the form.


<a name="create-related-entries-in-a-modal-using-the-inlinecreate-operation"></a>
#### Delete related entries or fall back to default

Normally, when the admin removes a relationship from the "select", only the relationship gets deleted, _not_ the related entry. But for the `hasMany` and `morphMany` relationships, it can also make sense to want to remove the related entry entirely. That's why for those relationships, you can also instruct Backpack to remove the _related entry_ upon saving OR change the foreign key to a default value (fallback).

```php
// Inside ArticleCrudController
CRUD::field([
    'type'          => "relationship",
    'name'          => 'comments',
    // when removed, use fallback_id
    'fallback_id'   => 3, // will relate to the comment with ID "3"
    // when removed, delete the entry
    'force_delete'  => true, // will delete that comment
]);
```

<hr>

<a name="repeatable"></a>
### repeatable <span class="badge badge-pill badge-info">PRO</span>

Shows a group of inputs to the user, and allows the user to add or remove groups of that kind:

![CRUD Field - repeatable](https://backpackforlaravel.com/uploads/docs-4-2/fields/repeatable.png)

**Since v5**: repeatable returns an array when the form is submitted instead of the already parsed json. **You must cast** the repeatable field to **ARRAY** or **JSON** in your model.

Clicking on the "New Item" button will add another group with the same subfields (in the example, another Testimonial).

You can use most field types inside the field groups, add as many subfields you need, and change their width using ```wrapper``` like you would do outside the repeatable field. But please note that:
- **all subfields defined inside a field group need to have their definition valid and complete**; you can't use shorthands, you shouldn't assume fields will guess attributes for you;
- some field types do not make sense as subfields inside repeatable (for example, relationship fields might not make sense; they will work if the relationship is defined on the main model, but upon save the selected entries will NOT be saved as relationships, they will be saved as JSON; you can intercept the saving if you want and do whatever you want);
- a few fields _make sense_, but _cannot_ work inside repeatable (ex: upload, upload_multiple); [see the notes inside the PR](https://github.com/Laravel-Backpack/CRUD/pull/2266#issuecomment-559436214) for more details, and a complete list of the fields; the few fields that do not work inside repeatable have sensible alternatives;
- **VALIDATION**: you can validate subfields the same way you validate [nested arrays in Laravel](https://laravel.com/docs/8.x/validation#validating-nested-array-input) Eg: `testimonial.*.name => 'required'`
- **FIELD USAGE AND RELATIONSHIPS**: note that it's not possible to use a repeatable field inside other repeatable field. Relationships that use `subfields` are under the hood repeatable fields, so the relationship subfields cannot include other repeatable field.

```php
CRUD::field([   // repeatable
    'name'  => 'testimonials',
    'label' => 'Testimonials',
    'type'  => 'repeatable',
    'subfields' => [ // also works as: "fields"
        [
            'name'    => 'name',
            'type'    => 'text',
            'label'   => 'Name',
            'wrapper' => ['class' => 'form-group col-md-4'],
        ],
        [
            'name'    => 'position',
            'type'    => 'text',
            'label'   => 'Position',
            'wrapper' => ['class' => 'form-group col-md-4'],
        ],
        [
            'name'    => 'company',
            'type'    => 'text',
            'label'   => 'Company',
            'wrapper' => ['class' => 'form-group col-md-4'],
        ],
        [
            'name'  => 'quote',
            'type'  => 'ckeditor',
            'label' => 'Quote',
        ],
    ],

    // optional
    'new_item_label'  => 'Add Group', // customize the text of the button
    'init_rows' => 2, // number of empty rows to be initialized, by default 1
    'min_rows' => 2, // minimum rows allowed, when reached the "delete" buttons will be hidden
    'max_rows' => 2, // maximum rows allowed, when reached the "new item" button will be hidden
    // allow reordering?
    'reorder' => false, // hide up&down arrows next to each row (no reordering)
    'reorder' => true, // show up&down arrows next to each row
    'reorder' => 'order', // show arrows AND add a hidden subfield with that name (value gets updated when rows move)
    'reorder' => ['name' => 'order', 'type' => 'number', 'attributes' => ['data-reorder-input' => true]], // show arrows AND add a visible number subfield
]);
```

<hr>

<a name="select2"></a>
### select2 (1-n relationship) <span class="badge badge-pill badge-info">PRO</span>

Works just like the SELECT field, but prettier. Shows a Select2 with the names of the connected entity and let the user select one of them.
Your relationships should already be defined on your models as hasOne() or belongsTo().

```php
CRUD::field([  // Select2
   'label'     => "Category",
   'type'      => 'select2',
   'name'      => 'category_id', // the db column for the foreign key

   // optional
   'entity'    => 'category', // the method that defines the relationship in your Model
   'model'     => "App\Models\Category", // foreign key model
   'attribute' => 'name', // foreign key attribute that is shown to user
   'default'   => 2, // set the default value of the select2

    // also optional
   'options'   => (function ($query) {
        return $query->orderBy('name', 'ASC')->where('depth', 1)->get();
    }), // force the related options to be a custom query, instead of all(); you can use this to filter the results show in the select
]);
```

For more information about the optional attributes that fields use when they interact with related entries - [look here](#optional-attributes-for-fields-containing-related-entries).

Input preview:

![CRUD Field - select2](https://backpackforlaravel.com/uploads/docs-4-2/fields/select2_nested.png)


<hr>

<a name="select2-multiple"></a>
### select2_multiple (n-n relationship) <span class="badge badge-pill badge-info">PRO</span>

[Works just like the SELECT field, but prettier]

Shows a Select2 with the names of the connected entity and let the user select any number of them.
Your relationship should already be defined on your models as belongsToMany().

```php
CRUD::field([    // Select2Multiple = n-n relationship (with pivot table)
     'label'     => "Tags",
     'type'      => 'select2_multiple',
     'name'      => 'tags', // the method that defines the relationship in your Model

     // optional
     'entity'    => 'tags', // the method that defines the relationship in your Model
     'model'     => "App\Models\Tag", // foreign key model
     'attribute' => 'name', // foreign key attribute that is shown to user
     'pivot'     => true, // on create&update, do you need to add/delete pivot table entries?
     // 'select_all' => true, // show Select All and Clear buttons?

     // optional
     'options'   => (function ($query) {
         return $query->orderBy('name', 'ASC')->where('depth', 1)->get();
     }), // force the related options to be a custom query, instead of all(); you can use this to filter the results show in the select
]);
```

For more information about the optional attributes that fields use when they interact with related entries - [look here](#optional-attributes-for-fields-containing-related-entries).

Input preview:

![CRUD Field - select2_multiple](https://backpackforlaravel.com/uploads/docs-4-2/fields/select2_multiple.png)

<hr>

<a name="select2-nested"></a>
### select2_nested <span class="badge badge-pill badge-info">PRO</span>

Display a select2 with the values ordered hierarchically and indented, for an entity where you use Reorder. Please mind that the connected model needs:
- a ```children()``` relationship pointing to itself;
- the usual ```lft```, ```rgt```, ```depth``` attributes;

```php
CRUD::field([   // select2_nested
    'name'      => 'category_id',
    'label'     => "Category",
    'type'      => 'select2_nested',
    'entity'    => 'category', // the method that defines the relationship in your Model
    'attribute' => 'name', // foreign key attribute that is shown to user

    // optional
    'model'     => "App\Models\Category", // force foreign key model
]);
```

For more information about the optional attributes that fields use when they interact with related entries - [look here](#optional-attributes-for-fields-containing-related-entries).

Input preview:

![CRUD Field - select2_nested](https://backpackforlaravel.com/uploads/docs-4-2/fields/select2_nested.png)

<hr>

<a name="select2-grouped"></a>
### select2_grouped <span class="badge badge-pill badge-info">PRO</span>

Display a select2 where the options are grouped by a second entity (like Categories).

```php
CRUD::field([   // select2_grouped
    'label'     => 'Articles grouped by categories',
    'type'      => 'select2_grouped', //https://github.com/Laravel-Backpack/CRUD/issues/502
    'name'      => 'article_id',
    'entity'    => 'article', // the method that defines the relationship in your Model
    'attribute' => 'title',
    'group_by'  => 'category', // the relationship to entity you want to use for grouping
    'group_by_attribute' => 'name', // the attribute on related model, that you want shown
    'group_by_relationship_back' => 'articles', // relationship from related model back to this model
]);
```

For more information about the optional attributes that fields use when they interact with related entries - [look here](#optional-attributes-for-fields-containing-related-entries).

Input preview:

![CRUD Field - select2_grouped](https://backpackforlaravel.com/uploads/docs-4-2/fields/select2_grouped.png)


<hr>

<a name="select_and_order"></a>
### select_and_order <span class="badge badge-pill badge-info">PRO</span>

Display items on two columns and let the user drag&drop between them to choose which items are selected and which are not, and reorder the selected items with drag&drop.

Its definition is exactly as ```select_from_array```, but the value will be stored as JSON in the database: ```["3","5","7","6"]```, so it needs the attribute to be cast to array on the Model:

```php
protected $casts = [
    'featured' => 'array'
];
```

Definition:

```php
CRUD::field([   // select_and_order
    'name'  => 'featured',
    'label' => "Featured",
    'type'  => 'select_and_order',
    'options' => [
        1 => "Option 1",
        2 => "Option 2"
    ]
]);
```

Also possible:

```php
CRUD::field([   // select_and_order
    'name'    => 'featured',
    'label'   => 'Featured',
    'type'    => 'select_and_order',
    'options' => Product::get()->pluck('title','id')->toArray(),
]);
```

Input preview:

![CRUD Field - select_and_order](https://backpackforlaravel.com/uploads/docs-4-2/fields/select_and_order.png)


<hr>

<a name="select2-from-array"></a>
### select2_from_array <span class="badge badge-pill badge-info">PRO</span>

Display a select2 with the values you want:

```php
CRUD::field([   // select2_from_array
    'name'        => 'template',
    'label'       => "Template",
    'type'        => 'select2_from_array',
    'options'     => ['one' => 'One', 'two' => 'Two'],
    'allows_null' => false,
    'default'     => 'one',
    // 'multiple' => true, // allows multiple selections 
    // 'allows_multiple' => true, // OPTIONAL; needs you to cast this to array in your model;
    // 'sortable' => true, // requires the field to accept multiple values, and allow the selected options to be sorted.
    // 'tagging' => true, // allow users to type and create new options.
]);
```

Input preview:

![CRUD Field - select2_from_array](https://backpackforlaravel.com/uploads/docs-4-2/fields/select2_from_array.png)

<hr>

<a name="select2-from-ajax"></a>
### select2_from_ajax <span class="badge badge-pill badge-info">PRO</span>

Display a select2 that takes its values from an AJAX call.

```php
CRUD::field([   // 1-n relationship
    'label'       => "End", // Table column heading
    'type'        => "select2_from_ajax",
    'name'        => 'category_id', // the column that contains the ID of that connected entity
    'entity'      => 'category', // the method that defines the relationship in your Model
    'attribute'   => "name", // foreign key attribute that is shown to user
    'data_source' => url("api/category"), // url to controller search function (with /{id} should return model)

    // OPTIONAL
    // 'delay'                   => 500, // the minimum amount of time between ajax requests when searching in the field
    // 'placeholder'             => "Select a category", // placeholder for the select
    // 'minimum_input_length'    => 2, // minimum characters to type before querying results
    // 'model'                   => "App\Models\Category", // foreign key model
    // 'dependencies'            => ['category'], // when a dependency changes, this select2 is reset to null
    // 'method'                  => 'POST', // optional - HTTP method to use for the AJAX call (GET, POST)
    // 'include_all_form_fields' => false, // optional - only send the current field through AJAX (for a smaller payload if you're not using multiple chained select2s)
]);
```

For more information about the optional attributes that fields use when they interact with related entries - [look here](#optional-attributes-for-fields-containing-related-entries).

Of course, you also need to create make the data_source above respond to AJAX calls. You can use the [FetchOperation](https://backpackforlaravel.com/docs/{{version}}/crud-operation-fetch) to quickly do that in your current CrudController, or you can set up your custom API by creating a custom Route and Controller. Here's an example:

```php
Route::post('/api/category', 'Api\CategoryController@index');
```

```php
<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Backpack\NewsCRUD\app\Models\Category;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $search_term = $request->input('q');

        if ($search_term)
        {
            $results = Category::where('name', 'LIKE', '%'.$search_term.'%')->paginate(10);
        }
        else
        {
            $results = Category::paginate(10);
        }

        return $results;
    }
}
```

**Note:** If you want to also make this field work inside `repeatable` too, your API endpoint will also need to respond to the `keys` parameter, with the actual items that have those keys. For example:

```php
        if ($request->has('keys')) {
            return Category::findMany($request->input('keys'));
        }
```

Input preview:

![CRUD Field - select2_from_array](https://backpackforlaravel.com/uploads/docs-4-2/fields/select2_from_array.png)

<hr>

<a name="select2-from-ajax-multiple"></a>
### select2_from_ajax_multiple <span class="badge badge-pill badge-info">PRO</span>

Display a select2 that takes its values from an AJAX call. Same as [select2_from_ajax](#section-select2_from_ajax) above, but allows for multiple items to be selected. The only difference in the field definition is the "pivot" attribute.

```php
CRUD::field([   // n-n relationship
    'label'       => "Cities", // Table column heading
    'type'        => "select2_from_ajax_multiple",
    'name'        => 'cities', // a unique identifier (usually the method that defines the relationship in your Model)
    'entity'      => 'cities', // the method that defines the relationship in your Model
    'attribute'   => "name", // foreign key attribute that is shown to user
    'data_source' => url("api/city"), // url to controller search function (with /{id} should return model)
    'pivot'       => true, // on create&update, do you need to add/delete pivot table entries?

    // OPTIONAL
    'delay'                      => 500, // the minimum amount of time between ajax requests when searching in the field
    'model'                      => "App\Models\City", // foreign key model
    'placeholder'                => "Select a city", // placeholder for the select
    'minimum_input_length'       => 2, // minimum characters to type before querying results
    // 'method'                  => 'POST', // optional - HTTP method to use for the AJAX call (GET, POST)
    // 'include_all_form_fields' => false, // optional - only send the current field through AJAX (for a smaller payload if you're not using multiple chained select2s)
]);
```

For more information about the optional attributes that fields use when they interact with related entries - [look here](#optional-attributes-for-fields-containing-related-entries).

Of course, you also need to create a controller and routes for the data_source above. Here's an example:

```php
Route::post('/api/city', 'Api\CityController@index');
Route::post('/api/city/{id}', 'Api\CityController@show');
```

```php
<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\City;

class CityController extends Controller
{
    public function index(Request $request)
    {
        $search_term = $request->input('q');
        $page = $request->input('page');

        if ($search_term)
        {
            $results = City::where('name', 'LIKE', '%'.$search_term.'%')->paginate(10);
        }
        else
        {
            $results = City::paginate(10);
        }

        return $results;
    }

    public function show($id)
    {
        return City::find($id);
    }
}
```

Input preview:

![CRUD Field - select2_from_ajax_multiple](https://backpackforlaravel.com/uploads/docs-4-2/fields/select2_from_ajax_multiple.png)

**Note:** If you want to also make this field work inside `repeatable` too, your API endpoint will also need to respond to the `keys` parameter, with the actual items that have those keys. For example:

```php
        if ($request->has('keys')) {
            return City::findMany($request->input('keys'));
        }
```

<hr>

### select2_json_from_api  <span class="badge badge-pill badge-info">PRO</span>

Display a select2 that takes its values from an AJAX call.
Similar to [select2_from_ajax](#section-select2_from_ajax) above, but this one is not limited to a single entity. It can be used to select any JSON object from an API.

```php
CRUD::field([
    'label'                   => 'Airports', // Displayed column label
    'type'                    => 'select2_json_from_api',
    'name'                    => 'airports', // the column where this field will be stored
    'data_source'             => url('airports/fetch/list'), // the endpoint used by this field

    // OPTIONAL
    'delay'                   => 500, // the minimum amount of time between ajax requests when searching in the field
    'method'                  => 'POST', // route method, either GET or POST
    'placeholder'             => 'Select an airport', // placeholder for the select
    'minimum_input_length'    => 2, // minimum characters to type before querying results
    'multiple'                => true, // allow multiple selections
    'include_all_form_fields' => false, // only send the current field through AJAX (for a smaller payload if you're not using multiple chained select2s)
    
    // OPTIONAL - if the response is a list of objects (and not a simple array)
    'attribute'               => 'title', // attribute to show in the select2
    'attributes_to_store'       => ['id', 'title'], // attributes to store in the database
]);
```

For more information about the optional attributes that fields use when they interact with related entries - [look here](#optional-attributes-for-fields-containing-related-entries).

You may create a controller and routes for the data_source above. Here's an example using the FetchOperation, including a search term:
Note that this example is for a non paginated response, but `select2_json_from_api` also accepts a paginated response.

```php
// use the FetchOperation to quickly create an endpoint
use \Backpack\CRUD\app\Http\Controllers\Operations\FetchOperation;
```

```php
public function fetchAirports()
{
    $types = [
        ['id' => 'OPO', 'city' => 'Porto', 'title' => 'Francisco Sá Carneiro Airport'],
        ['id' => 'LIS', 'city' => 'Lisbon', 'title' => 'Humberto Delgado Airport'],
        ['id' => 'FAO', 'city' => 'Faro', 'title' => 'Faro Airport'],
        ['id' => 'FNC', 'city' => 'Funchal', 'title' => 'Cristiano Ronaldo Airport'],
        ['id' => 'PDL', 'city' => 'Ponta Delgada', 'title' => 'João Paulo II Airport'],
    ];

    return collect($types)->filter(fn(array $value): bool => str_contains(strtolower($value['title']), strtolower(request('q'))));
}
```

A simple array with a key value pair will also work:

```php
public function fetchAirports()
{
    $types = [
        'OPO' => 'Francisco Sá Carneiro Airport',
        'LIS' => 'Humberto Delgado Airport',
        'FAO' => 'Faro Airport',
        'FNC' => 'Cristiano Ronaldo Airport',
        'PDL' => 'João Paulo II Airport',
    ];

    return collect($types)->filter(fn(string $value): bool => str_contains(strtolower($value), strtolower(request('q'))));
}
```

#### Storing only one the id in the database

A very common use case you may have is to store only the id of the selected item in the database instead of a `json` string. For those cases you can achieve that by setting the `attributes_to_store` attribute to an array with only one item, the id of the selected item and do a little trick with the model events to store the id you want, and to give the field that id in a way it understands. 

```php

CRUD::field([
    'label'               => 'Airports',
    'type'                => 'select2_json_from_api',
    'name'                => 'airport_id', // dont make your column json if not storing json on it!
     // .... the rest your field configuration
    'attribute'           => 'id', 
    'attributes_to_store' => ['id'],
    'events'              => [
        'saving' => function($entry) {
            $entry->airport_id = json_decode($entry->airport_id ?? [], true)['id'] ?? null;
        'retrieved' => function($entry) {
            $entry->airport_id = json_encode(['id' => $entry->airport_id]);
        }
    ]
]);

```

<hr>

### slug  <span class="badge badge-pill badge-info">PRO</span>

Track the value of a different text input and turn it into a valid URL segment (aka. slug), as you type, using Javascript. Under the hood it uses [slugify](https://github.com/simov/slugify/blob/master/README.md) to generate the slug with some sensible defaults. 

```php
CRUD::field([   // Text
    'name'  => 'slug',
    'target'  => 'title', // will turn the title input into a slug
    'label' => "Slug",
    'type'  => 'slug',

    // optional
    'locale' => 'pt', // locale to use, defaults to app()->getLocale()
    'separator' => '', // separator to use
    'trim' => true, // trim whitespace
    'lower' => true, // convert to lowercase
    'strict' => true, // strip special characters except replacement
    'remove' => '/[*+~.()!:@]/g', // remove characters to match regex, defaults to null
    ]);
```

Input preview:
![CleanShot 2022-06-04 at 13 13 40](https://user-images.githubusercontent.com/1032474/171994919-cbdd8b9d-6823-4b26-82ed-7c2868c0cee8.gif)


By default, it will also slugify when the target input is edited. If you want to stop that behaviour, you can do that by removing the `target` on your edit operation. For example:

```php
    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();

        // disable editing the slug when editing
        CRUD::field('slug')->target('')->attributes(['readonly' => 'readonly']);
    }
```

<hr>

<a name="table"></a>
### table <span class="badge badge-pill badge-info">PRO</span>

Show a table with multiple inputs per row and store the values as JSON array of objects in the database. The user can add more rows and reorder the rows as they please.

```php
CRUD::field([   // Table
    'name'            => 'options',
    'label'           => 'Options',
    'type'            => 'table',
    'entity_singular' => 'option', // used on the "Add X" button
    'columns'         => [
        'name'  => 'Name',
        'desc'  => 'Description',
        'price' => 'Price'
    ],
    'max' => 5, // maximum rows allowed in the table
    'min' => 0, // minimum rows allowed in the table
]);
```

>It's highly recommended that you use [attribute casting](https://mattstauffer.com/blog/laravel-5.0-eloquent-attribute-casting) on your model when working with JSON arrays stored in database columns, and cast this attribute to either ```object``` or ```array``` in your Model.

##### Using the table in a repeatable field

When using this field in a [repeatable field](#repeatable) as subfield, you need to take ensure this field is not double encoded. For that you can overwrite the store and update methods in your CrudController. Here's an example:

```php
use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation {
        store as traitStore;
}
use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation {
    update as traitUpdate;
}

public function update($id)
{
    $this->decodeTableFieldsFromRequest();
    return $this->traitUpdate($id);
}

public function store()
{
    $this->decodeTableFieldsFromRequest();
    return $this->traitStore();
}

private function decodeTableFieldsFromRequest()
{
    $request = $this->crud->getRequest();
    $repeatable = $request->get('repeatable'); // change to your repeatable field name

    if(is_array($repeatable)) {
        array_map(function($item) {
            $item['table_field_name'] = json_decode($item['table_field_name'] ?? '', true); // change to your table field name
            return $item;
        }, $repeatable);
    }
    $request->request->set('repeatable', $repeatable); // change to your repeatable field name
    $this->crud->setRequest($request);
}

```

Input preview:

![CRUD Field - table](https://backpackforlaravel.com/uploads/docs-4-2/fields/table.png)


<hr>

<a name="video"></a>
### video <span class="badge badge-pill badge-info">PRO</span>

Allow the user to paste a YouTube/Vimeo link. That will get the video information with JavaScript and store it as a JSON in the database.

Field definition:
```php
CRUD::field([   // URL
    'name'            => 'video',
    'label'           => 'Link to video file on YouTube or Vimeo',
    'type'            => 'video',
    'youtube_api_key' => 'AIzaSycLRoVwovRmbIf_BH3X12IcTCudAErRlCE',
]);
```

An entry stored in the database will look like this:
```
$video = {
    id: 234324,
    title: 'my video title',
    image: 'https://provider.com/image.jpg',
    url: 'http://provider.com/video',
    provider: 'youtube'
}
```

So you should use [attribute casting](https://mattstauffer.com/blog/laravel-5.0-eloquent-attribute-casting) in your model, to cast the video as ```array``` or ```object```.

Vimeo does not require an API key in order to query their DB, but YouTube does, even though their free quota is generous. You can get a free YouTube API Key inside [Google Developers Console](https://console.developers.google.com/) ([video tutorial here](https://www.youtube.com/watch?v=pP4zvduVAqo)). Please DO NOT use our API Key - create your own. The key above is there just for your convenience, to easily try out the field. As soon as you decide to use this field type, create an API Key and use _your_ API Key. Our key hits its ceiling every month, so if you use our key most of the time it won't work.


<a name="overwriting-default-field-types"></a>
## Overwriting Default Field Types

The actual field types are stored in the Backpack/CRUD package in ```/resources/views/fields```. If you need to change an existing field, you don't need to modify the package, you just need to add a blade file in your application in ```/resources/views/vendor/backpack/crud/fields```, with the same name. The package checks there first, and only if there's no file there, will it load it from the package.

To quickly publish a field blade file in your project, you can use ```php artisan backpack:field --from=field_name```. For example, to publish the number field type, you'd type ```php artisan backpack:field --from=number```

>Please keep in mind that if you're using _your_ file for a field type, you're not using the _package file_. So any updates we push to that file, you're not getting them. In most cases, it's recommended you create a custom field type for your use case, instead of overwriting default field types.

<a name="creating-a-custom-field-type"></a>
## Creating a Custom Field Type

If you need to extend the CRUD with a new field type, you create a new file in your application in ```/resources/views/vendor/backpack/crud/fields```. Use a name that's different from all default field types.

```bash
// to create one using Backpack\Generators, run:
php artisan backpack:field new_field_name

// alternatively, to create a new field similar an existing field, run:
php artisan backpack:field new_field_name --from=old_field_name
```


That's it, you'll now be able to use it just like a default field type.

Your field definition will be something like:

```php
CRUD::field([   // Custom Field
    'name'  => 'address',
    'label' => 'Home address',
    'type'  => 'address'
    /// 'view_namespace' => 'yourpackage' // use a custom namespace of your package to load views within a custom view folder.
]);
```

And your blade file something like:
```php
<!-- field_type_name -->
@include('crud::fields.inc.wrapper_start')
    <label>{!! $field['label'] !!}</label>
    <input
        type="text"
        name="{{ $field['name'] }}"
        value="{{ old($field['name']) ? old($field['name']) : (isset($field['value']) ? $field['value'] : (isset($field['default']) ? $field['default'] : '' )) }}"
        @include('crud::fields.inc.attributes')
    >

    {{-- HINT --}}
    @if (isset($field['hint']))
        <p class="help-block">{!! $field['hint'] !!}</p>
    @endif
@include('crud::fields.inc.wrapper_end')

{{-- FIELD EXTRA CSS  --}}
{{-- push things in the after_styles section --}}
@push('crud_fields_styles')
    <!-- @basset('public_path_to_file') -->
    <!-- @basset(base_path('path_to_file')) -->
@endpush


{{-- FIELD EXTRA JS --}}
{{-- push things in the after_scripts section --}}
@push('crud_fields_scripts')
    <!-- @basset('public_path_to_file') -->
    <!-- @basset(base_path('path_to_file')) -->
@endpush
```

Inside your custom field type, you can use these variables:
- ```$crud``` - all the CRUD Panel settings, options and variables;
- ```$entry``` - in the Update operation, the current entry being modified (the actual values);
- ```$field``` - all attributes that have been passed for this field;

If your field type uses JavaScript, we recommend you:
- put a ```data-init-function="bpFieldInitMyCustomField"``` attribute on your input;
- place your logic inside the scripts section mentioned above, inside ```function bpFieldInitMyCustomField(element) {}```; of course, you choose the name of the function but it has to match whatever you specified as data attribute on the input, and it has to be pretty unique; inside this method, you'll find that ```element``` is jQuery-wrapped object of the element where you specified ```data-init-function```; this should be enough for you to not have to use IDs, or any other tricks, to determine other elements inside the DOM - determine them in relation to the main element; if you want, you can choose to put the ```data-init-function``` attribute on a different element, like the wrapping div;

<hr>

<a name="advanced-fields-use"></a>
## Advanced Fields Use

<a name="manipulating-fields-with-javascript"></a>
### Manipulating Fields with JavaScript

When you need to add custom interactions (if field is X then do Y), we have just the thing for you. You can easily add custom interactions, using our **CrudField JavaScript API**. It's already loaded on our Create / Update pages, in the global `crud` object, and it makes it dead-simple to select a field - `crud.field('title')` - using a syntax that's very familiar to our PHP syntax, then do the most common things on it.

For more information, please see the dedicated page about our [CrudField Javascript API](/docs/{{version}}/crud-fields-javascript-api).


<a name="adding-new-methods-to-the-crudfield-class"></a>
### Adding new methods to the CrudField class

You can add your own methods Backpack CRUD fields, so that you can do `CRUD::field('name')->customThing()`. You can easily do that, because the `CrudField` class is Macroable. It's as easy as this:

```php
use Backpack\CRUD\app\Library\CrudPanel\CrudField;

// register media upload macros on CRUD fields
if (! CrudField::hasMacro('customThing')) {
    CrudField::macro('customThing', function ($firstParamExample = [], $secondParamExample = null) {
        /** @var CrudField $this */

        // TODO: do anything you want to $this

        return $this;
    });
}
```

A good place to do this would be in your AppServiceProvider, in a custom service provider. That way you have it across all your CRUD panels.
