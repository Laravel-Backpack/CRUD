#### Creating a New Operation With a Form

Say we want to create a ```Comment``` operation. Click the Comment button on an entry, and it brings up a form with a textarea. Submit the form and you're back to the list view. Let's get started. What we need to do is:

**Step 0.** Install ```backpack/generators``` if you haven't yet. [https://github.com/Laravel-Backpack/Generators](https://github.com/Laravel-Backpack/Generators). We have built a set of commands to help you create a new form operation easy peasy. You can use it like this:

```bash
php artisan backpack:crud-operation Comment # will create a form for the entries in your list view, with the id in the URL

php artisan backpack:crud-operation Comment --no-id # will create a form, without the id in the URL (generators v4.0.4+)
```

**Step 1.** Back to our goal, lets generate the operation trait, by running `php artisan backpack:crud-form-operation Comment`. This will create a new trait, `CommentOperation` that should look very similar to this:

```php
<?php

namespace App\Http\Controllers\Admin\Operations;

use Backpack\CRUD\app\Http\Controllers\Operations\Concerns\HasForm;

trait CommentOperation
{
    use HasForm;

    /**
     * Define which routes are needed for this operation.
     *
     * @param string $segment    Name of the current entity (singular). Used as first URL segment.
     * @param string $routeName  Prefix of the route name.
     * @param string $controller Name of the current CrudController.
     */
    protected function setupCommentRoutes(string $segment, string $routeName, string $controller): void
    {
        $this->formRoutes(
            operationName: 'comment',
            routesHaveIdSegment: true,
            segment: $segment,
            routeName: $routeName,
            controller: $controller
        );
    }

    /**
     * Add the default settings, buttons, etc that this operation needs.
     */
    protected function setupCommentDefaults(): void
    {
        $this->formDefaults(
            operationName: 'comment',
            buttonStack: 'line', // alternatives: top, bottom
            // buttonMeta: [
            //     'icon' => 'la la-home',
            //     'label' => 'Comment',
            //     'wrapper' => [
            //          'target' => '_blank',
            //     ],
            // ],
        );
    }

    /**
     * Method to handle the GET request and display the View with a Backpack form
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\View\View
     */
    public function getCommentForm(int $id)
    {
        $this->crud->hasAccessOrFail('comment');

        return $this->formView($id);
    }

    /**
    * Method to handle the POST request and perform the operation
    *
    * @param  int  $id
    * @return array|\Illuminate\Http\RedirectResponse
    */
    public function postCommentForm(int $id)
    {
        $this->crud->hasAccessOrFail('comment');

        return $this->formAction(id: $id, formLogic: function ($inputs, $entry) {
            // You logic goes here...
            // dd('got to ' . __METHOD__, $inputs, $entry);

            // show a success message
            \Alert::success('Something was done!')->flash();
        });
    }
}

```
> Notes : Please Keep in mind, when you set the **buttonStack** to *top* or *bottom*, don't forget to set the **routesHaveIdSegment** to *false*, otherwise it won't show your form.

**Step 2.** Now let's use this operation trait on our CrudController. For example in our UserCrudController we'd have to do this next to all other operations:
```php
    use \App\Http\Controllers\Admin\Operations\CommentOperation;
```

**Step 3.** Now let's add the fields. We have a decision to make... who adds the fields? Does it make more sense for:
- **(a)** the developer to add the fields, because they vary from CrudController to CrudController;
- **(b)** the operation itself to add the fields, because the fields never change when you add the operation to multiple CrudControllers;

If **(a)** made more sense, we'd just create a new function in our CrudController, called `setupCommentOperation()`, and define the fields there.

In case **(b)** makes more sense, we will define the fields at the operation itself in `setupCommentDefaults()`.

```php
// a) whenever we use this operation, we want to always setup the same fields

// inside `ComentOperation.php`
public function setupCommentDefaults(): void
{  
    // ...

    $this->crud->operation('comment', function () {
        $this->crud->field('message')->type('textarea');
    });
}

// b) when the operation can accept different fields for each crud controller, eg: UserCrudController may have some fields, while in PostCrudController we may have others

// inside `UserCrudController.php` 
public function setupCommentOperation(): void
{
    $this->crud->field('message')->type('textarea');
}

// inside `PostCrudController.php` 
public function setupCommentOperation(): void
{
    $this->crud->field('message')->type('textarea');
    $this->crud->field('rating')->type('number');

    // if you want to add a FormRequest to validate the fields you do it here.
    // later when you handle the form submission, the request will be automatically validated
    $this->crud->setValidation(CommentRequest::class); // this file is not automatically created. You have to create it yourself.
}

```

**Step 4.** Let's actually add the comment to the database. Inside the `CommentOperation` trait, if we go to `postCommentForm()` well see we have a placeholder for our logic there:

```php
    public function postCommentForm(int $id)
    {
        $this->crud->hasAccessOrFail('comment');

        return $this->formAction(id: $id, formLogic: function ($inputs, $entry) {
            // You logic goes here...

            // You can validate the inputs using the Laravel Validator, eg:
            // $valid = Validator::make($inputs, ['message' => 'required'])->validated();

            // alternatively if you set a FormRequest in the setupCommentOperation() method, 
            // the request will be validated here already

            // and then save it to database
            // $entry->comments()->create($valid);

            // show a success message
            \Alert::success('Something was done!')->flash();
        });
    }
```
