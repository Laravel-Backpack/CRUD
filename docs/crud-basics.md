# Basics

---

Backpack\CRUD provides a fast way to build administration panels - places where your administrators can Create, Read, Update, Delete entries for a specific Eloquent model. **One CRUD Panel provides functionality for one Eloquent Model.**

<a name="requirements"></a>
## Requirements

In order to create a CRUD Panel, you'll need:
- **a table in the database** (and maybe connection tables for relationships);
- **an Eloquent Model** that points to that db table;

If you don't already have the models, don't worry, Backpack also includes a faster way to generate database migrations and models.

<a name="architecture"></a>
## Architecture

A Backpack CRUD Panel uses _the same elements_ you would have created for an administration panel, if you were doing it from scratch:
- a custom **route** - will be generated in ```routes/backpack/custom.php```; points to a controller;
- a custom **controller** - will be generated in ```app/Http/Controllers/Admin```; holds the logic for the all operations an admin can perform on that Eloquent model;
- a **request** file (optional) - will be generated in ```app/Http/Requests```; used to validate Create/Update forms;

**The only differences** between building it from scratch and using Backpack\CRUD are that:
- your controller will be extending ```Backpack\CRUD\app\Http\Controllers\CrudController```, which allows you to easily add traits to handle the already-built operations: Create, Update, Delete, List, Show,  Reorder, Revisions etc.
- your model will ```use \Backpack\CRUD\CrudTrait```;

This simple architecture (```ProductCrudController extends CrudController```) means:
- **your CRUD Panel will not be a _black box_**; you can easily see the logic for each operation, by checking the methods on this controller, or the traits you'll be using;
- **you can _easily_ override what happens inside each operation**;
- **you can _easily_ create custom operations**;

For example:
- want to change how a single ```Product``` is shown to the admin? just create a method called ```show()``` in your ```ProductCrudController```; simple OOP dictates that your method will be picked up, instead of the one in the `ShowOperation` trait; some goes for ```create()```, ```store()```, etc - you have complete control;
- want to create a new "Publish" operation on a ```Product```? your ```ProductCrudController``` is a great place for that logic; just create a custom ```publish()``` method and a route that points to it;

<a name="files"></a>
## Example Files

For a ```Tag``` entity, your CRUD Panel would consist of:
- your existing model (```app/Models/Tag.php```);
- a route inside ```routes/backpack/custom.php```;
- a controller (```app/Http/Controllers/Admin/TagCrudController.php```);
- a request (```app/Http/Requests/TagCrudRequest.php```);

To further your understanding  of how a CRUD Panel works, [read more about this example in the tutorial](/docs/{{version}}/crud-tutorial).
