### repeatable [PRO]

Shows a group of inputs to the user, and allows the user to add or remove groups of that kind:

**Since v5**: repeatable returns an array when the form is submitted instead of the already parsed json. **You must cast** the repeatable field to **ARRAY** or **JSON** in your model.

Clicking on the "New Item" button will add another group with the same subfields (in the example, another Testimonial).

You can use most field types inside the field groups, add as many subfields you need, and change their width using ```wrapper``` like you would do outside the repeatable field. But please note that:
- **all subfields defined inside a field group need to have their definition valid and complete**; you can't use shorthands, you shouldn't assume fields will guess attributes for you;
- some field types do not make sense as subfields inside repeatable (for example, relationship fields might not make sense; they will work if the relationship is defined on the main model, but upon save the selected entries will NOT be saved as relationships, they will be saved as JSON; you can intercept the saving if you want and do whatever you want);
- a few fields _make sense_, but _cannot_ work inside repeatable (ex: upload, upload_multiple); [see the notes inside the PR](https://github.com/Laravel-Backpack/CRUD/pull/2266#issuecomment-559436214) for more details, and a complete list of the fields; the few fields that do not work inside repeatable have sensible alternatives;
- **VALIDATION**: you can validate subfields the same way you validate [nested arrays in Laravel](https://laravel.com/docs/8.x/validation#validating-nested-array-input) Eg: `testimonial.*.name => 'required'`
- **FIELD USAGE AND RELATIONSHIPS**: note that it's not possible to use a repeatable field inside other repeatable field. Relationships that use `subfields` are under the hood repeatable fields, so the relationship subfields cannot include other repeatable field.

```php
CRUD::field([   // repeatable
    'name'  => 'testimonials',
    'label' => 'Testimonials',
    'type'  => 'repeatable',
    'subfields' => [ // also works as: "fields"
        [
            'name'    => 'name',
            'type'    => 'text',
            'label'   => 'Name',
            'wrapper' => ['class' => 'form-group col-md-4'],
        ],
        [
            'name'    => 'position',
            'type'    => 'text',
            'label'   => 'Position',
            'wrapper' => ['class' => 'form-group col-md-4'],
        ],
        [
            'name'    => 'company',
            'type'    => 'text',
            'label'   => 'Company',
            'wrapper' => ['class' => 'form-group col-md-4'],
        ],
        [
            'name'  => 'quote',
            'type'  => 'ckeditor',
            'label' => 'Quote',
        ],
    ],

    // optional
    'new_item_label'  => 'Add Group', // customize the text of the button
    'init_rows' => 2, // number of empty rows to be initialized, by default 1
    'min_rows' => 2, // minimum rows allowed, when reached the "delete" buttons will be hidden
    'max_rows' => 2, // maximum rows allowed, when reached the "new item" button will be hidden
    // allow reordering?
    'reorder' => false, // hide up&down arrows next to each row (no reordering)
    'reorder' => true, // show up&down arrows next to each row
    'reorder' => 'order', // show arrows AND add a hidden subfield with that name (value gets updated when rows move)
    'reorder' => ['name' => 'order', 'type' => 'number', 'attributes' => ['data-reorder-input' => true]], // show arrows AND add a visible number subfield
]);
```
