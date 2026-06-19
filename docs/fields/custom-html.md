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
