### relationship_count

Shows the number of items that are related to the current entry, for a particular relationship.

```php
[
   // relationship count
   'name'      => 'tags', // name of relationship method in the model
   'type'      => 'relationship_count',
   'label'     => 'Tags', // Table column heading
   // OPTIONAL
   // 'suffix' => ' tags', // to show "123 tags" instead of "123 items"

   // if you need that column to be orderable in table, you need to manually provide the orderLogic
   // 'orderable' => true,
   // 'orderLogic' => function ($query, $column, $columnDirection) {
                $query->orderBy('tags_count', $columnDirection);
            },
],
```

**Important Note:** This column will load ALL related items onto the page. Which is not a problem normally, for small tables. But if your related table has thousands or millions of entries, it will considerably slow down the page. For a much more performant option, with the same result, you can add a fake column to the results using Laravel's `withCount()` method, then use the `text` column to show that number. That will be a lot faster, and the end-result is identical from the user's perspective. For the same example above (number of tags) this is how it will look:
```
$this->crud->query->withCount('tags'); // this will add a tags_count column to the results
$this->crud->addColumn([
   'name'      => 'tags_count', // name of relationship method in the model
   'type'      => 'text',
   'label'     => 'Tags', // Table column heading
   'suffix'    => ' tags', // to show "123 tags" instead of "123"
]);
```
