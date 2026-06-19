### Div

Allows you to include multiple widgets within a "div" element with the attributes of your choice. For example, you can include multiple widgets within a ```<div class="row"></div>``` with the code below:

```php
[
    'type'    => 'div',
    'class'   => 'row',
    'content' => [ // widgets
        [ 'type' => 'card', 'content' => ['body' => 'One'] ],
        [ 'type' => 'card', 'content' => ['body' => 'Two'] ],
        [ 'type' => 'card', 'content' => ['body' => 'Three'] ],
    ]
]
```

Anything you specify on this widget, other than ```type``` and ```content```, has to be a string, and will be considered an attribute of the "div" element.
For example, in the following snippet, ```class``` and ```custom-attribute``` are attributes of the "div" element:

```php
[
    'type'    => 'div',
    'class'   => 'row my-custom-widget-class',
    'custom-attribute'   => 'my-custom-value',
    'content' => [ // widgets
        [ 'type' => 'card', 'content' => ['body' => 'One'] ],
        [ 'type' => 'card', 'content' => ['body' => 'Two'] ],
        [ 'type' => 'card', 'content' => ['body' => 'Three'] ],
    ]
]
```

and the generated output will be:

```html
    <div custom-attribute="my-custom-value" class="row my-custom-widget-class">
        // The HTML code of the three card widgets will be here
    </div>
```
