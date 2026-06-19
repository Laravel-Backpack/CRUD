### code_mirror [PRO]

Show a [CodeMirror](https://codemirror.net/) code editor. It supports syntax highlighting for multiple languages and themes.

```php
CRUD::field([
    'name' => 'code',
    'type' => 'code_mirror',
    'label' => 'Code',
    // optional
    'configuration' => [
        'theme' => 'monokai', // options: monokai, dracula, material, eclipse, idea
        'mode' => 'javascript', // options: javascript, xml, css, htmlmixed, php, sql, python
        'height' => '300px',
    ]
]);
```
