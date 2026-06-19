### markdown [PRO]

Convert a markdown string to HTML, using `Illuminate\Support\Str::markdown()`. Since Markdown is usually used for long texts, this column is most helpful in the "Show" operation - not so much in the "ListEntries" operation, where only short snippets make sense.

```php
[
   'name'  => 'text', // The db column name
   'label' => 'Text', // Table column heading
   'type'  => 'markdown',
],
```

By default, raw HTML embedded in the markdown is **stripped** and unsafe links (`javascript:`, `vbscript:`, `data:`) are **blocked**. This is a safe default for content that may have been entered by untrusted users.

You can customise the rendering behaviour by passing a `markdown_options` array, which is forwarded directly to `Str::markdown()`:

```php
[
   'name'             => 'text',
   'label'            => 'Text',
   'type'             => 'markdown',
   // OPTIONAL - override the CommonMark options (these are the defaults):
   'markdown_options' => [
       'html_input'         => 'strip',   // 'allow' | 'escape' | 'strip'
       'allow_unsafe_links' => false,     // disallow javascript:/vbscript: links
       // 'max_nesting_level' => 10,       // prevent deeply nested structures (DoS protection)
   ],
],
```

If you explicitly trust the markdown source (e.g. content only ever entered by admins) and need raw HTML to pass through, you can opt in:

```php
[
   'name'             => 'text',
   'label'            => 'Text',
   'type'             => 'markdown',
   'markdown_options' => [
       'html_input'         => 'allow', // ⚠ only use for fully trusted content
       'allow_unsafe_links' => true,
   ],
],
```

> [IMPORTANT] The output of `markdown` is **NOT escaped by default** — it is rendered as HTML. With the default `markdown_options`, raw HTML tags are stripped and unsafe links are blocked, which protects against the most common XSS vectors. If you override `html_input` to `'allow'`, you take full responsibility for sanitising the stored value. In that case, make sure to purify the value in an accessor on your Model using an [HTML Purifier package](https://github.com/mewebstudio/Purifier)
