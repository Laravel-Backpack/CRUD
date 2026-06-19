## Overwriting Default Field Types

The actual field types are stored in the Backpack/CRUD package in ```/resources/views/fields```. If you need to change an existing field, you don't need to modify the package, add a blade file in your application in ```/resources/views/vendor/backpack/crud/fields```, with the same name. The package checks there first, and only if there's no file there, will it load it from the package.

To publish a field blade file in your project, you can use ```php artisan backpack:field --from=field_name```. For example, to publish the number field type, you'd type ```php artisan backpack:field --from=number```

>Please keep in mind that if you're using _your_ file for a field type, you're not using the _package file_. So any updates we push to that file, you're not getting them. In most cases, it's recommended you create a custom field type for your use case, instead of overwriting default field types.

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

## Advanced Fields Use

### Manipulating Fields with JavaScript

When you need to add custom interactions (if field is X then do Y), we have just the thing for you. You can add custom interactions, using our **CrudField JavaScript API**. It's already loaded on our Create / Update pages, in the global `crud` object, and it makes it dead-simple to select a field - `crud.field('title')` - using a syntax that's very familiar to our PHP syntax, then do the most common things on it.

For more information, please see the dedicated page about our [CrudField Javascript API](/docs/{{version}}/crud-fields-javascript-api).

### Adding new methods to the CrudField class

You can add your own methods Backpack CRUD fields, so that you can do `CRUD::field('name')->customThing()`. You can do that, because the `CrudField` class is Macroable. It's as easy as this:

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
