### What field should I use for a relationship?

With so many field types, it can be a little overwhelming to understand what field type to use for a _particular_ Eloquent relationship. Here's a quick summary of all possible relationships, and the interface you might want for them. Click on the relationship you're interested in, for more details and an example:

- **[hasOne (1-1)](#hasone-1-1-relationship)** ✅
 - (A) show a subform - add a `relationship` field with `subfields`
 - (B) show a separate field for each attribute on the related entry - add any field type, with dot notation for the field name (`passport.title`)
- **[belongsTo (n-1)](#belongsto-n-1-relationship)** ✅
 - show a select2 (single) - add a `relationship` field
- **[hasMany (1-n)](#hasmany-1-n-relationship)** ✅
 - (A) show a select2_multiple - add a `relationship` field
 - (B) show a subform - add a `relationship` field with `subfields`
- **[belongsToMany (n-n)](#belongstomany-n-n-relationship)** ✅
 - (A) show a select2_multiple - add a `relationship` field
 - (B) show a subform - add a `relationship` field and define `subfields`
- **[morphOne (1-1)](#morphone-1-1-polymorphic-relationship)** ✅
 - (A) show a subform - add a `relationship` field with `subfields`
 - (B) show a separate field for each attribute on the related entry - add any field type, with dot notation for the field name (`passport.title`)
- **[morphMany (1-n)](#morphmany-1-n-polymorphic-relationship)** ✅
 - (A) show a select2_multiple - add a `relationship` field
 - (B) show a subform - add a `relationship` field with `subfields`
- **[morphToMany (n-n)](#morphtomany-n-n-polymorphic-relationship)** ✅
 - (A) show a select2_multiple - add a `relationship` field
 - (B) show a subform - add a `relationship` field and define `subfields`
- **[morphTo (n-1)](#morphto-n-1-relationship)** ✅
 - Manage both `_type` and `_id` of the morphTo relation;
- **[hasOneThrough (1-1-1)](#hasonethrough-1-1-1-relationship)** ❌
 - it's read-only, no sense having a field for it;
- **[hasManyThrough (1-1-n)](#hasmanythrough-1-1-n-relationship)** ❌
 - it's read-only, no sense having a field for it;
- **[Has One Of Many (1-n turned into 1-1)](#has-one-of-many-1-1-relationship-out-of-1-n-relationship)** ❌
 - it's read-only, no sense having a field for it;
- **[Morph One Of Many (1-n turned into 1-1)](#morph-one-of-many-1-1-relationship-out-of-1-n-relationship)** ❌
 - it's read-only, no sense having a field for it;
- **[morphedByMany (n-n inverse)](#morphedbymany-n-n-inverse-relationship)** ❌
 - never needed, UI would be very difficult to understand & use;

#### hasOne (1-1 relationship)

- example:
 - `User -> hasOne -> Phone`
 - the foreign key is stored on the Phone (`user_id` on `phones` table)
- what to use:
 - the `relationship` field with `subfields` defined for each column on the related entry
- how to use:
 - [the `hasOne` relationship should be properly defined](https://laravel.com/docs/eloquent-relationships#one-to-one) in the User model;

```php
// inside UserCrudController::setupCreateOperation()
CRUD::field('phone')->type('relationship')->subfields([
    'prefix',
    'number',
    [
        'name' => 'type',
        'type' => 'select_from_array',
        'options' => ['mobile' => 'Mobile Phone', 'landline' => 'Landline', 'fax' => 'Fax'],
    ]
]);
```

#### hasOne (1-1 relationship) - one field for each attribute of the related entry

- example:
 - `User -> hasOne -> Phone`
 - the foreign key is stored on the Phone (`user_id` on `phones` table)
- what to use:
 - any field (eg. `text`, `number`, `textarea`), with the field name prefixed by the relationship name (dot notation);
- how to use:
 - [the `hasOne` relationship should be properly defined](https://laravel.com/docs/eloquent-relationships#one-to-one) in the User model;
 - you can add fields for each individual attribute on the related entry; specify in the field name that the value should not be stored on the _main model_, but on _a related model_; you can do that using dot notation (`relationship_name.column_name`); note that the prefix (before the dot) is the **Relation** name, not the table name;
 - all fields types should work fine - depending on your needs you could choose to add a [`text`](#text) field, [`number`](#number) field, [`textarea`](#textarea) field, [`select`](#select) field etc.;

```php
// inside UserCrudController::setupCreateOperation()
CRUD::field('phone.number')->type('number');
CRUD::field('phone.prefix')->type('text');
CRUD::field('phone.type')->type('select_from_array')->options(['mobile' => 'Mobile Phone', 'landline' => 'Landline', 'fax' => 'Fax']);
```

#### belongsTo (n-1 relationship)

- example:
 - `Phone -> User`
 - a Phone belongs to one User; a Phone can only belong to one User
 - the foreign key is stored on the Phone (`user_id` on `phones` table)
- how to use:
 - [the `belongsTo` relationship should be properly defined](https://laravel.com/docs/eloquent-relationships#one-to-many-inverse) in the Phone model;
 - you can add a dropdown to let the admin pick which User the Phone belongs; you can use any of the dropdown fields, but for convenience we've made a list here, and broken them down depending on approximately how many entries the dropdown will have:
 - for 0-10 dropdown items - we recommend you use the [`relationship`](#relationship) or [`select`](#select) field;
 - for 0-500 dropdown items - we recommend you use the [`relationship`](#relationship) or [`select2`](#select2) field;
 - for 500-1.000.000+ dropdown items - we recommend you load the dropdown items using AJAX, by using the [`relationship`](#relationship) field and Fetch operation (alternatively, use the [`select2_from_ajax`](#select2-from-ajax) field);

```php
// inside PhoneCrudController::setupCreateOperation()
CRUD::field('user'); // notice the name is the relationship name and backpack will auto-infer the field type as [`relationship`](#relationship)
CRUD::field('user_id')->type('select')->model('App\Models\User')->attribute('name')->entity('user'); // notice the name is the foreign key attribute
CRUD::field('user_id')->type('select2')->model('App\Models\User')->attribute('name')->entity('user'); // notice the name is the foreign key attribute
```

- notes:
 - if you choose to use the [`relationship`](#relationship) field, you could also use [the InlineCreate operation](/docs/{{version}}/crud-operation-inline-create), which will add a [+ Add Item] button next to the dropdown, to let the admin create a User in a modal, without leaving the current Create Phone form;

#### hasMany (1-n relationship)

- example:
 - `Post -> HasMany -> Comment`
 - the foreign key is stored on the Comment (`post_id` on `comments` table)
- what to use:
 - use the `relationship` field;
- how to use:
 - [the `hasMany` relationship should be properly defined](https://laravel.com/docs/eloquent-relationships#one-to-many) in the Post model;

```php
// inside PostCrudController::setupCreateOperation()
CRUD::field('comments'); // when unselected, will set foreign key to null
CRUD::field('comments')->fallback_id(3); // when unselected, will set foreign key to 3
CRUD::field('comments')->force_delete(true);  // when unselected, will delete the related entry
```

- notes:
 - when a related entry is unselected (removed), Backpack will:
 - set the foreign key to `null`, if that db column is nullable (eg. `post_id`);
 - set the foreign key to a default value, if you define a `fallback_id` on the field;
 - delete related entry entirely, if you define `'force_delete' => false` on the field;
 - you can use [the InlineCreate operation](/docs/{{version}}/crud-operation-inline-create) to show a [+ Add Item] button next to the dropdown; for it to work `post_id` on comments table need to nullable or have a default setup in database;

#### hasMany (1-n relationship) with subform to create, update and delete related entries

If you want the admin to not only _select_ an entry, but also create them, edit their attributes or delete related entries.

- example:
 - `Post -> HasMany -> Comment`
 - the foreign key is stored on the Comment (`post_id` on `comments` table)
- what to use:
 - use the `relationship` field;
- how to use:
 - [the `hasMany` relationship should be properly defined](https://laravel.com/docs/eloquent-relationships#one-to-many) in the Post model;

```php
// inside PostCrudController::setupCreateOperation()
CRUD::field('comments')->subfields([['name' => 'body']]);
// where body is a text field in the comment table.
```

#### belongsToMany (n-n relationship)

Note: Starting with v5, the `BelongsToMany` relation had been improved to simplify the scenario where your pivot table has extra database columns (in addition to the foreign keys).

- example:
 - `User -> BelongsToMany -> Role`
 - the foreign keys are stored on a pivot table (usually the `user_roles` table has both `user_id` and `role_id`)
- how to use:
 - [the `belongsToMany` relationship should be properly defined](https://laravel.com/docs/eloquent-relationships#many-to-many) in both the User and Role models;
 - you can add a dropdown on your User to pick the Roles that are connected to it; for that, use the [`relationship`](#relationship), [`select_multiple`](#select-multiple), [`select2_multiple`](#select2-multiple) or [`select2_from_ajax_multiple`](#select2-from-ajax-multiple) fields;

```php
// inside UserCrudController::setupCreateOperation()
CRUD::field('roles');

// inside RoleCrudController::setupCreateOperation()
CRUD::field('users');
```

- notes:
 - if you choose to use the [`relationship`](#relationship) field type, you can also use [the InlineCreate operation](/docs/{{version}}/crud-operation-inline-create), which will add a `[+ Add Item]` button next to the dropdown, to let the admin create a User/Role in a modal, without leaving the current Create User/Role form;

##### EXTRA: Saving additional attributes to the pivot table

If your pivot table has additional database columns (eg. not only `user_id` and `role_id` but also `notes` or `supervisor`, you can use the `relationship` field to show a subform, instead of a `select2`, and show `subfields` for each of those attributes you want edited on the pivot table. For the example above (`User -> BelongsToMany -> Roles`) you should do the following:

**Step 1.** Setup the pivot fields in your relation definition:

```php
// inside App\Models\User
public function roles() {
    return $this->belongsToMany('App\Models\Role')->withPivot('notes', 'some_other_field'); // `notes` and `some_other_field` are aditional fields in the pivot table that you plan to show in the form.
}
```

**Step 2.** Setup the pivot fields in your relation definition:

```php
// inside UserCrudController::setupCreateOperation()
CRUD::field('roles')->subfields([
    ['name' => 'notes', 'type' => 'textarea'],
    ['name' => 'some_other_field']
]);
```

And you are done: a subform will shown, with a select for the pivot connected entity field and the defined fields and Backpack will take care of the saving process.

**Need to change the pivot `select` field?** You can add any configuration to the pivot field as you would do in a [relationship](#relationship) select field, the only difference is is that it should go inside the `pivotSelect` key:
```php
CRUD::field('users')->subfields([ ['name' => 'notes'] ])
    ->pivotSelect([
        'ajax' => true,
        'data_source' => backpack_url('role/fetch/user'),
        'placeholder' => 'some placeholder',
        'wrapper' => [
            'class' => 'col-md-6'
        ]
    ]);
```

#### morphOne (1-1 polymorphic relationship)

- example:
 - Post/User -> morphOne -> Video.
 - The User model and the Post model have 1 Video each.
 - [the `morphOne` relationship should be properly defined](https://laravel.com/docs/eloquent-relationships#one-to-one-polymorphic-relations) in both the Post/User and Video models;

You can add a subform for the related entry to be created/edited/deleted from the main form:

```php
CRUD::field('video')->type('relationship')->subfields([
    'url',
    [
        'name' => 'description',
        'type' => 'ckeditor',
    ]
]);
```

Backpack will take care of the saving process and deal with the morphable relation.

#### morphOne (1-1 polymorphic relationship) one field for each related entry attribute

- example:
 - Post/User -> morphOne -> Video.
 - The User model and the Post model have 1 Video each.
 - [the `morphOne` relationship should be properly defined](https://laravel.com/docs/eloquent-relationships#one-to-one-polymorphic-relations) in both the Post/User and Video models;

You can add any type of field to change the attribute on the related entry, but make sure to prefix the field name with the name of the relationship:

```php
CRUD::field('video.description')->type('ckeditor');
CRUD::field('video.url');
```

Backpack will take care of the saving process and deal with the morphable relation.

#### morphMany (1-n polymorphic relationship)

This is in all aspects similar to [HasMany](#hasmany) relation, the difference is that it's stored in a pivot table.
- example:
 - Video/Post -> morphMany -> Comment.
 - The Video model and the Post model can have multiple Comment model but the comment belongs to only one of them.
 - [the `morphMany` relationship should be properly defined](https://laravel.com/docs/eloquent-relationships#one-to-many-polymorphic-relations) in both the Post/Video and Comment models;

There is no sense in using this a `select` when using a polymorphic relation because the items that could/would be select might belong to different entities. So you should setup this relation as you would setup a [HasMany creatable](#hasmany-creatable).

```php
// inside PostCrudController::setupCreateOperation() and inside VideoCrudController::setupCreateOperation()
CRUD::field('comments')->subfields([['name' => 'comment_text']]); //note comment_text is a text field in the comment table.
```

#### morphToMany (n-n polymorphic relationship)

This is in all aspects similar to [BelongsToMany](#belongstomany) relation, the difference is that it stores the `morphable` entity in the pivot table:
 - Video/Post -> belongsToMany -> Tag.
 - The Video model and the Post model can have multiple Tag model and each Tag model can belong to one or more of them.
 - [the `morphToMany` relationship should be properly defined](https://laravel.com/docs/eloquent-relationships#many-to-many-polymorphic-relations) in both the Post/Video and Tag models;

Please read the relationship [BelongsToMany](#belongstomany) documentation, everything is the same in regards to fields definition and Backpack will take care of the morphable relation saving.

#### MorphTo (n-1 relationship)

Using this relation type Backpack will automatically manage for you both `_type` and `_id` fields of this relation.
Let's say we have `comments`, that can be either for `videos` or `posts`.
- Your `Comment` Model should have its `morphTo` relation set up.
- Your db table should have the `commentable_type` and `commentable_id` columns.
```php
// in CommentCrudController you can add the morphTo fields by naming the field the morphTo relation name
CRUD::field('commentable')
    ->addMorphOption('App\Models\Video')
    ->addMorphOption('App\Models\Post');
```
This will generate two inputs:
1 - A select with two options `Video` and `Post` as the `morph type field`.
2 - A second select that will have the options for both `Video` and `Post` models.

In a real world scenario, you might have other needs, like using AJAX to select the actual entries or changing the inputs size etc. For that, check out the available attributes:

```php
// ->addMorphOption(string $model/$morphMapName, string $labelInSelect, array $options)
CRUD::field('commentable')
    ->addMorphOption('App\Models\Video')
    ->addMorphOption('App\Models\Post', 'Posts', [
        [
            'data_source'          => backpack_url('comment/fetch/post'),
            'minimum_input_length' => 2,
            'placeholder'          => 'select an amazing post',
            'method'               => 'POST',
            'attribute'            => 'title',
        ]
    ]);

// by defining `data_source` you are telling Backpack that the `Posts` select should be an ajax select.
```
In this scenario the same two selects would be generated, but for the Post, your admin see an AJAX field, instead of a static one, use POST instead of GET etc.

To further customize the fields you can use `morphTypeField` and `morphIdField` to configure the select sizes etc.

```php
CRUD::field('commentable')
    ->addMorphOption('App\Models\Video')
    ->addMorphOption('App\Models\Post', 'Posts')
    ->morphTypeField(['wrapper' => ['class' => 'form-group col-sm-4']])
    ->morphIdField(['wrapper' => [
        'class' => 'form-group col-sm-8'],
        'attributes' => ['my_custom_attribute' => 'custom_value']
    ]);
```

Here is an example using array field definition:

```php
CRUD::field([
    'name' => 'commentable',
    'morphOptions' => [
        ['App\Models\PetShop\Owner', 'Owners'],
        ['monster', 'Monsters', [
            'placeholder' => 'Select a little monster'
        ]],
        ['App\Models\PetShop\Pet', 'Pets', [
            'data_source' => backpack_url('pet-shop/comment/fetch/pets'),
            'minimum_input_length' => 2,
            'placeholder' => 'select a fluffy pet'
        ]],
    ],
    'morphTypeField' => [
        'wrapper' => ['class' => 'form-group col-md-6']
    ],
    'morphIdField' => [
        'wrapper' => ['class' => 'form-group col-md-6']
    ]
]);
```

#### hasOneThrough (1-1-1 relationship)

- This is a "read-only" relationship. It does not make sense to add a field for it.

#### hasManyThrough (1-1-n relationship)

- This is a "read-only" relationship. It does not make sense to add a field for it.

#### Has One of Many (1-1 relationship out of 1-n relationship)

- This is a "read-only" relationship. It does not make sense to add a field for it. Please use the general-purpose relationship towards this entity (the 1-n relationship, without `latestOfMany()` or `oldestOfMany()`).

#### Morph One of Many (1-1 relationship out of 1-n relationship)

- This is a "read-only" relationship. It does not make sense to add a field for it. Please use the general-purpose relationship towards this entity (the 1-n relationship, without `latestOfMany()` or `oldestOfMany()`).

#### MorphedByMany (n-n inverse relationship)

- We do not provide an interface to edit this relationship. We never needed it, nobody ever asked for it and it would be very difficult to create an interface that is easy-to-use and easy-to-understand for the admin. If you find yourself needing this, please let us know by opening an issue on GitHub.
