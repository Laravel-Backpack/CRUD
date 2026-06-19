## All Operations

### Access

Prevent or allow users from accessing different CRUD operations.

- **allowAccess()** - give users access to one or multiple operations
```php
$this->crud->allowAccess('list');
$this->crud->allowAccess(['list', 'create', 'delete']);
```

- **allowAccessOnlyTo()** - give users access only to one or some operations, denying the rest of them
```php
$this->crud->allowAccessOnlyTo('list');
$this->crud->allowAccessOnlyTo(['list', 'create', 'delete']);
```

- **denyAccess()** - prevent users from accessing one or multiple operations
```php
$this->crud->denyAccess('list');
$this->crud->denyAccess(['list', 'create', 'delete']);
```

- **denyAllAccess()** - prevent users from accessing all operations (you may allow some operations then)
```php
$this->crud->denyAllAccess();
```

- **hasAccess()** - check if the current user has access to one or multiple operations
```php
$this->crud->hasAccess('something'); // returns true/false
$this->crud->hasAccessOrFail('something'); // throws 403 error
$this->crud->hasAccessToAll(['create', 'update']); // returns true/false
$this->crud->hasAccessToAny(['create', 'update']); // returns true/false
```

### Eager Loading Relationships

- **with()** - when the current entry is loaded (in any operation) also get its relationships, so that only one query is made to the database per entry
```php
$this->crud->with('relationship_name');
```

### Custom Views

- **setShowView()**, **setEditView()**, **setCreateView()**, **setListView()**, **setReorderView()**, **setRevisionsView()**, **setRevisionsTimelineView()**, **setDetailsRowView()** - set the view for a certain CRUD operation or feature

```php
// use a custom view for a CRUD operation
$this->crud->setShowView('path.to.your.view');
$this->crud->setEditView('path.to.your.view');
$this->crud->setCreateView('path.to.your.view');
$this->crud->setListView('path.to.your.view');
$this->crud->setReorderView('path.to.your.view');
$this->crud->setRevisionsView('path.to.your.view');
$this->crud->setRevisionsTimelineView('path.to.your.view');
$this->crud->setDetailsRowView('path.to.your.view');

// more generally, you can use the Settings API:
$this->crud->set('create.view', 'path.to.your.view');

// if you want to load something from the /resources/vendor/backpack/crud directory, you can do
$this->crud->set('create.view', 'crud::yourfolder.yourview');
// or
$this->crud->set('create.view', 'resources.vendor.backpack.crud.yourfolder.yourview');
```

### Content Class

- **setShowContentClass()**, **setEditContentClass()**, **setCreateContentClass()**, **setListContentClass()**, **setReorderContentClass()**, **setRevisionsContentClass()**, **setRevisionsTimelineContentClass()** - set the CSS class for an operation view, to make the main area bigger or smaller:

```php
// use a custom view for a CRUD operation
$this->crud->setShowContentClass('col-md-8');
$this->crud->setEditContentClass('col-md-8');
$this->crud->setCreateContentClass('col-md-8');
$this->crud->setListContentClass('col-md-8');
$this->crud->setReorderContentClass('col-md-8');
$this->crud->setRevisionsContentClass('col-md-8');
$this->crud->setRevisionsTimelineContentClass('col-md-8');

// more generally, you can use the Settings API:
$this->crud->set('create.contentClass', 'col-md-12');
```

### Getters

- **getEntry()** - get a certain entry of the current model type
```php
$this->crud->getEntry($entry_id);
```
- **getEntries()** - get all entries using the current CRUD query
```php
$this->crud->getEntries();
```

- **getFields()** - get all fields for a certain operation, or for both
```php
$this->crud->getFields('create/update/both');
```

- **getCurrentEntry()** - get the current entry, for operations that work on a single entry
```php
$this->crud->getCurrentEntry();
// ex: in your update() method, after calling parent::updateCrud()
```

### Operations

- **getOperation()** - get the name of the operation that is currently being performed
```php
$this->crud->getOperation();
```

- **setOperation()** - set the name of the operation that is currently being performed
```php
$this->crud->setOperation('ListEntries');
```

### Actions

An action is the controller method that is currently being run.

- **getActionMethod()** - returns the method on the controller that was called by the route; ex: ```create()```, ```update()```, ```edit()``` etc;
```php
$this->crud->getActionMethod();
```

- **actionIs()** - checks if the given controller method is the one called by the route
```php
$this->crud->actionIs('create');
```

### Title, Heading, Subheading

Legend:
- _operation_ - a collection of functions in a CrudController, that together allow the admin to perform something on the current model;
- _action_ - a method (aka function) of an operation; it is the actual PHP function's name;

- **getTitle()** - get the Title for the create action
```php
$this->crud->getTitle('create');
```

- **getHeading()** - get the Heading for the create action
```php
$this->crud->getHeading('create');
```

- **getSubheading()** - get the Subheading for the create action
```php
$this->crud->getSubheading('create');
```

- **setTitle()** - set the Title for the create action
```php
$this->crud->setTitle('some string', 'create');
```

- **setHeading()** - set the Heading for the create action
```php
$this->crud->setHeading('some string', 'create');
```

- **setSubheading()** - set the Subheading for the create action
```php
$this->crud->setSubheading('some string', 'create');
```

### CrudPanel Basic Info

- **setModel()** - set the Eloquent object that should be used for all operations
```php
$this->crud->setModel("App\Models\Example");
```

- **setRoute()** - set the main route to this CRUD
```php
$this->crud->setRoute("admin/example");
// OR $this->crud->setRouteName("admin.example");
```

- **setEntityNameStrings()** - set how the entity name should be shown to the user, in singular and in plural
```php
$this->crud->setEntityNameStrings("example", "examples");
```
