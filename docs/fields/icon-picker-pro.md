### icon_picker [PRO]

Show an icon picker. Supported icon sets are fontawesome, glyphicon, ionicon, weathericon, mapicon, octicon, typicon, elusiveicon, materialdesign as per the jQuery plugin, [bootstrap-iconpicker](http://victor-valencia.github.io/bootstrap-iconpicker/).

The stored value will be the class name (ex: fa-home).

```php
CRUD::field([   // icon_picker
    'label'   => "Icon",
    'name'    => 'icon',
    'type'    => 'icon_picker',
    'iconset' => 'fontawesome' // options: fontawesome, glyphicon, ionicon, weathericon, mapicon, octicon, typicon, elusiveicon, materialdesign
]);
```

Your input will look like button, with a dropdown where the user can search or pick an icon:
