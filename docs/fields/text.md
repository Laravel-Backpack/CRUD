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
