### number

The text column will just output the number value of a db column (or model attribute). Its definition is:
```php
[
   'name'  => 'name', // The db column name
   'label' => 'Tag Name', // Table column heading
   'type'  => 'number',
   // 'prefix'        => '$',
   // 'suffix'        => ' EUR',
   // 'decimals'      => 2,
   // 'dec_point'     => ',',
   // 'thousands_sep' => '.',
   // decimals, dec_point and thousands_sep are used to format the number;
   // for details on how they work check out PHP's number_format() method, they're passed directly to it;
   // https://www.php.net/manual/en/function.number-format.php
],
```
