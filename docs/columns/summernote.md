### summernote

The summernote column will output the non-escaped text value of a db column (or model attribute). Its definition is:

```php
[
   'name'      => 'name', // The db column name
   'label'     => 'Tag Name', // Table column heading
   'type'      => 'summernote',
],
```

> **Security warning:** this column renders the stored HTML as-is, so any `<script>` tag or event handler (eg. `onerror`) in the value will run in the admin's browser. Backpack does NOT sanitize it. Use this column only for content that you trust, or sanitize the HTML before it reaches the page — for example with [HTML Purifier](https://github.com/mewebstudio/Purifier) in a model accessor/mutator, so it's clean no matter where the value was saved from (admin panel, API, imports, front-end forms). If the value can come from users you don't trust and you can't sanitize it, add `'escaped' => true` to the column definition to show the HTML as text instead.
