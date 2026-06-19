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
