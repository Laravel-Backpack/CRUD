### datetime

The date column will show a localized datetime in the default datetime format (as specified in the ```config/backpack/ui.php``` file), whether the attribute is cast as datetime in the model or not.

Note that the ```format``` attribute uses ISO date formatting parameters and not PHP ```date()``` formatters. See <https://carbon.nesbot.com/docs/#iso-format-available-replacements> for more information.

```php
[
    'name'  => 'name', // The db column name
    'label' => 'Tag Name', // Table column heading
    'type'  => 'datetime',
    // 'format' => 'l j F Y H:i:s', // use something else than the base.default_datetime_format config value
],
```
