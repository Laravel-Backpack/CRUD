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
