### date

The date column will show a localized date in the default date format (as specified in the ```config/backpack/ui.php``` file), whether the attribute is cast as date in the model or not.

Note that the ```format``` attribute uses ISO date formatting parameters and not PHP ```date()``` formatters. See <https://carbon.nesbot.com/docs/#iso-format-available-replacements> for more information.

```php
[
    'name'  => 'name', // The db column name
    'label' => 'Tag Name', // Table column heading
    'type'  => 'date',
    // 'format' => 'l j F Y', // use something else than the base.default_date_format config value
],
```
