### row_number

Show the row number (index). The number depends strictly on the result set (x records per page, pagination, search, filters, etc). It does not get any information from the database. It is not searchable. It is only useful to show the current row number.

```php
$this->crud->addColumn([
    'name'      => 'row_number',
    'type'      => 'row_number',
    'label'     => '#',
    'orderable' => false,
])->makeFirstColumn();
```

Notes:
- you can have a different ```name```; just make sure your model doesn't have that attribute;
- you can have a different label;
- you can place the column as second / third / etc if you remove ```makeFirstColumn()```;
- this column type allows the use of suffix/prefix just like the text column type;
- if upon placement you notice it always shows ```false``` then please note there have been changes in the ```search()``` method - you need to add another parameter to your ```getEntriesAsJsonForDatatables()``` call;
