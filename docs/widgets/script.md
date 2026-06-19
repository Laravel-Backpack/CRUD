### Script

Loads a JavaScript file from a location you specify using a `<script>` element.

```php
[
    'type'     => 'script',
    'content'  => 'assets/js/custom-script.js',
    // optional
    // 'stack'    => 'before_scripts', // default is after_scripts
]
```

You can also specify a link (`https://path-to-file.com/file.js`) as the content, and it will load the file from a CDN. In that case you might also want to add additional attributes, which you can do, for example:

```php
Widget::add()->type('script')
     ->content('https://code.jquery.com/ui/1.12.0/jquery-ui.min.js')
     ->integrity('sha256-0YPKAwZP7Mp3ALMRVB2i8GXeEndvCq3eSl/WsAl1Ryk=')
     ->crossorigin('anonymous');
```
