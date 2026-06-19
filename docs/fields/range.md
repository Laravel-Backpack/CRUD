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
