### select_grouped

Display a select where the options are grouped by a second entity (like Categories).

```php
CRUD::field([   // select_grouped
    'label'     => 'Articles grouped by categories',
    'type'      => 'select_grouped', //https://github.com/Laravel-Backpack/CRUD/issues/502
    'name'      => 'article_id',
    'entity'    => 'article',
    'attribute' => 'title',
    'group_by'  => 'category', // the relationship to entity you want to use for grouping
    'group_by_attribute' => 'name', // the attribute on related model, that you want shown
    'group_by_relationship_back' => 'articles', // relationship from related model back to this model
]);
```
