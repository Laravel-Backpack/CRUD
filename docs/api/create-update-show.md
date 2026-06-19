### Show Operation

Use [the same Columns API as for the ListEntries operation](#columns-api), but inside your ```show()``` method.

### Create & Update Operations

Manipulate what fields are shown in the create / update forms. Check out [CRUD > Operations > Create & Update > Fields](/docs/{{version}}/crud-fields) in the docs to see examples of ```$field_definition_array```.

**Note:** The call is being performed for the current operation. So it's important to pay attention _where_ you're calling fields. Most likely, you'll want to do this inside ```setupCreateOperation()``` or ```setupUpdateOperation()```.

- **addField()** - add one field
```php
$this->crud->addField($field_definition_array);
$this->crud->addField('db_column_name'); // a lazy way to add fields: let the CRUD decide what field type it is and set it automatically, along with the field label
```

- **addFields()** - add multiple fields
```php
$this->crud->addFields($array_of_fields_definition_arrays);
```

- **modifyField()** - change the attributes of an existing field
```php
$this->crud->modifyField($name, $modifs_array);
```

- **removeField()** - remove a given field from the current operation
```php
$this->crud->removeField('name');
```

- **removeFields()** - remove multiple fields from the current operation
```php
$this->crud->removeFields($array_of_names);
```

- **removeAllFields()** - remove all registered fields
```php
$this->crud->removeAllFields();
```
- **Chained - beforeField()** - add a field _before_ a given field
```php
$this->crud->addField()->beforeField('name');
```

- **Chained - afterField()** - add a field _after_ a given field
```php
$this->crud->addField()->afterField('name');
```

- **setRequiredFields()** - check the FormRequests used in this EntityCrudController for required fields, and add an asterisk to them in the create or edit forms
```php
$this->crud->setRequiredFields(StoreRequest::class);
```

- **setValidation()** - makes sure validation and authorization in the FormRequest you've passed is being performed; also uses that file to figure out asterisk to show in the forms (calls ```setRequiredFields()``` above):
```php
$this->crud->setValidation(ArticleRequest::class);
```

### Reorder Operation

Show a reorder button in the table view, next to Add. Provides an interface to reorder & nest elements, provided the ```parent_id```, ```lft```, ```rgt```, ```depth``` columns are in the database, and ```$fillable``` on the model.

```php
$this->crud->set('reorder.label', 'name'); // which model attribute to use for labels
$this->crud->set('reorder.max_level', 3); // maximum nesting depth; this example will prevent the user from creating trees deeper than 3 levels;
```

- **disableReorder()** - disable the Reorder functionality
```php
$this->crud->disableReorder();
```

- **isReorderEnabled()** - returns ```true```/```false``` if the Reorder operation is enabled or not
```php
$this->crud->isReorderEnabled();
```

### Revise Operation

A.k.a. Audit Trail. Tracks all changes to an entry and provides an interface to revert to a previous state. This operation is not installed by default - please check out [Revise Operation](/docs/{{version}}/crud-operation-revisions) for the installation & usage steps.
