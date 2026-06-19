### Style

Loads a CSS file from a location you specify using a `<link>` element.

```php
[
    'type'     => 'style',
    'content'  => 'assets/css/custom-script.css',
    // optional
    // 'stack'    => 'before_styles', // default is after_styles
]
```

You can also specify a link (`https://path-to-file.com/file.css`) as the content, and it will load the file from a CDN. In that case you might also want to add additional attributes, which you can do, for example:

```php
Widget::add()->type('style')
     ->content('https://cdn.jsdelivr.net/npm/@shoelace-style/shoelace@2.0.0-beta.58/dist/themes/light.css')
     ->integrity('sha256-0YPKAwZP7Mp3ALMRVB2i8GXeEndvCq3eSl/WsAl1Ryk=')
     ->crossorigin('anonymous');
```
