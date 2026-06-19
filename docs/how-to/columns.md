## Columns

### How to use the same column name multiple times in a CRUD

If you try to add multiple columns with the same ```name```, by default Backpack will only show the last one. That's because ```name``` is also used as a key in the ```$column``` array. So when you ```addColumn()``` with the same name twice, it just overwrites the previous one.

To insert two columns with the same name, use the ```key``` attribute on the second column (or both columns). If this attribute is present for a column, Backpack will use ```key``` instead of ```name```. Example:

```diff
        CRUD::column([
           'label' => "Location",
           'type' => 'select',
           'name' => 'location_id',
           'entity' => 'location',
           'attribute' => 'title',
           'model' => "App\Models\Location"
        ]);

        CRUD::column([
           'label' => "Location Type",
           'type' => 'radio_location_type',
           'options' => [
               1 => "Country",
               2 => "Region"
           ],
           'name' => 'location_id',
+          'key' => 'location_type',
           'entity' => 'location',
           'attribute' => 'type',
           'model' => "App\Models\Location"
        ]);
```
