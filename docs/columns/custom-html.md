### custom_html

Show the HTML that you provide in the page. You can optionally escape the text when displaying it on page, if you don't trust the value.

```php
[
    'name'     => 'my_custom_html',
    'label'    => 'Custom HTML',
    'type'     => 'custom_html',
    'value'    => '<span class="text-danger">Something</span>',

    // OPTIONALS
    // 'escaped' => true // echo using {{ }} instead of {!! !!}
],
```

> [IMPORTANT] As opposed to most other Backpack columns, the output of `custom_html` is **NOT escaped by default**. That means if the database value contains malicious JS, that JS might be run when the admin previews it. Make sure to purify the value of this column in an accessor on your Model. At a minimum, you can use `strip_tags()` (here's [an example](https://github.com/Laravel-Backpack/demo/commit/509c0bf0d8b9ee6a52c50f0d2caed65f1f986385)), but a lot better would be to use an [HTML Purifier package](https://github.com/mewebstudio/Purifier) (do that [manually](https://github.com/Laravel-Backpack/demo/commit/7342cffb418bb568b9e4ee279859685ddc0456c1) or by casting the attribute to `CleanHtmlOutput::class`).
