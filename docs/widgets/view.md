### View

Loads a blade view from a location you specify. Any attributes you give it will be available in the ```$widget``` variable inside that view.

```php
[
    'type'     => 'view',
    'view'     => 'path.to.custom.view',
    'someAttr' => 'some value',
]
```

It helps load blade files that are not specifically created to be widgets, that live in a different path than ```resources/views/vendor/backpack/ui/widgets```, as if they were widgets.
