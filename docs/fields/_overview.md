# Fields

## About

Field types define how the admin can manipulate an entry's values. They're used by the Create and Update operations.

Think of the field type as the type of input: ```<input type="text" />```. But for most entities, you won't just need text inputs - you'll need datepickers, upload buttons, 1-n relationship, n-n relationships, textareas, etc.

We have a lot of default field types, detailed below. If you don't find what you're looking for, you can [create a custom field type](/docs/{{version}}/crud-fields#creating-a-custom-field-type). Or if you just want to tweak a default field type a little bit, you can [overwrite default field types](/docs/{{version}}/crud-fields#overwriting-default-field-types).

> NOTE: If the _field name_ is the exact same as a relation method in the model, Backpack will assume you're adding a field for that relationship and infer relation attributes from it. To disable this behaviour, you can use `'entity' => false` in your field definition.

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

### Field Attributes

#### Mandatory Field Attributes

**The only attribute that's mandatory when you define a field is its `name`**, which will be used:
- inside the inputs, as `<input name='your_db_column' />`;
- to store the information in the database, so your `name` should correspond to a database column (if the field type doesn't have different instructions);

This also means the `name` attribute is UNIQUE. You cannot add multiple fields with the same `name` - because if more inputs are added with the same name in an HTML form... only the last input will actually be submitted. That's just how HTML forms work.

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

#### Optional - Field Attributes for Presentation Purposes

There are a few optional attributes on most default field types, that you can use to achieve a few common customisations:

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

#### Optional - Tab Attribute Splits Forms into Tabs

```php
CRUD::field('price')->tab('Tab name here');
```

If you don't specify a tab name for a field, then Backpack will place it above all of the tabs, for example:

```php
CRUD::field('product');
CRUD::field('description')->tab('Information');
CRUD::field('price')->tab('Prices');
```

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
- `relation_options_query` - a closure that limits which related entries are **valid at save time**, acting as a server-side authorization guard against manipulated requests (IDOR). For page-rendered selects, Backpack automatically uses your `options` closure for this purpose. For ajax fields powered by the [FetchOperation](/docs/{{version}}/crud-operation-fetch) whose entity matches the `fetchXxx()` naming convention, the fetch `query` is used automatically. For fields backed by a **fully custom endpoint** (no FetchOperation), declare the allowed set here yourself — without it, any key can be submitted and persisted:

```php
CRUD::field([
    'type'                   => 'select2_from_ajax',
    'name'                   => 'category_id',
    'data_source'            => url('api/category'),
    'relation_options_query' => function ($query) {
        return $query->where('active', true); // only these IDs will be accepted at save time
    },
]);
```

Out-of-scope keys are silently dropped for `HasMany`, `MorphMany`, `BelongsToMany`, and `MorphToMany` relationships. For `BelongsTo`, an out-of-scope value aborts the save with a validation error on the field.
- `relation_options_query_source` - the **name of the FetchOperation method** (eg. `'fetchCategory'`) whose `query` defines this field's allowed options. Use this instead of `relation_options_query` when the field is powered by the [FetchOperation](/docs/{{version}}/crud-operation-fetch) but you had to set `data_source` **manually** (eg. the field entity does not match the fetch method name). Backpack reuses that method's `query` closure to enforce submitted values at save time, so you don't have to duplicate it:

```php
CRUD::field([
    'type'                          => 'select2_from_ajax',
    'name'                          => 'category_id',
    'data_source'                   => backpack_url('article/fetch/product-category'),
    'relation_options_query_source' => 'fetchProductCategory', // reuse this fetch method's query
]);
```

If you do need a field that contains relationships to behave a certain way, it's usually enough to just specify a different `entity`. However, you _can_ specify any of the attributes above, and Backpack will take your value for it, instead of trying to guess one.

**Identifiable Attribute for Relationship Fields**

Fields that work with relationships will allow you to select which ```attribute``` on the related entry you want to show to the user. All relationship fields (relationship, select, select2, select_multiple, select2_multiple, select2_from_ajax, select2_from_ajax_multiple) let you define the ```attribute``` for this specific purpose.

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
