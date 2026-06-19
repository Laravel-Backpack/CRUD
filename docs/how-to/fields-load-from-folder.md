### How to load fields from a different folder

If you're developing a package, you might need Backpack to pick up fields from your package folder, instead of having to publish them upon installation.

Fields, Columns and Filters all have a ```view_namespace``` parameter you can use. Type your folder there, and Backpack will check that folder first, then where the views are published, then Backpack's package folder. Example:

```php
CRUD::addFilter([ // add a "simple" filter called Draft
  'type'  => 'complex',
  'name'  => 'checkbox',
  'label' => 'Checked',
  'view_namespace' => 'custom_filters'
],
false, // the simple filter has no values, just the "Draft" label specified above
function () { // if the filter is active (the GET parameter "draft" exits)
    CRUD::addClause('where', 'checkbox', '1');
});
```
This will make Backpack look for the ```resources/views/custom_filters/complex.blade.php```, and pick that up before anything else.
