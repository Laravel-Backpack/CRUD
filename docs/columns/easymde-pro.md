### easymde [PRO]

Convert easymde generated markdown string to HTML, using `Illuminate\Support\Str::markdown()`. Since Markdown is usually used for long texts, this column is most helpful in the "Show" operation - not so much in the "ListEntries" operation, where only short snippets make sense.

It's the same [markdown](#markdown-pro) column with an alias, named after its field name. See the [markdown column](#markdown-pro) for the full list of options, including `markdown_options`.

```php
[
   'name'      => 'info', // The db column name
   'label'     => 'Info', // Table column heading
   'type'      => 'easymde',
],
```
