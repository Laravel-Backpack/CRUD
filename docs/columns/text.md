### text

The text column will just output the text value of a db column (or model attribute). Its definition is:
```php
[
   'name'      => 'name', // The db column name
   'label'     => 'Tag Name', // Table column heading
   // 'prefix' => 'Name: ',
   // 'suffix' => '(user)',
   // 'limit'  => 120, // character limit; default is 50,
],
```

**Advanced use case:** The ```text``` column type can also show the attribute of a 1-1 relationship. If you have a relationship (like ```parent()```) set up in your Model, you can use relationship and attribute in the ```name```, using dot notation:
```php
[
    'name'  => 'parent.title',
    'label' => 'Title',
    'type'  => 'text'
],
```
