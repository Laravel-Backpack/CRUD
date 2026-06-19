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
