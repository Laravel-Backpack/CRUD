# Show Operation

--

<a name="about"></a>
## About

This CRUD operation allows your admins to preview an entry. When enabled, it will add a "Preview" button in the ListEntries view, that points to a show page. By default, it will show all attributes for that model:

![Backpack CRUD Show Operation](https://backpackforlaravel.com/uploads/docs-4-0/operations/show.png)

In case your entity is translatable, it will show a multi-language dropdown, just like Edit.

**NOTE FOR TRANSLATED ENTRIES**: The `edit` and `show` buttons, show a dropdown with a language selector, so that you can directly edit/show the desired entry in a specific locale. Sometimes you wish to have "plain" buttons without all those dropdowns. In that case, you can turn `showLanguagesDirectlyInEditButton` and/or `showLanguagesDirectlyInShowButton` located in `config/backpack/operations/list.php` and it will disable the language dropdowns from those buttons. As usual you can do it for a specific crud only, by setting `CRUD::setOperationSetting('showLanguagesDirectlyInEditButton', false);` in your controller `setupListOperation` function. 

<a name="how-it-works"></a>
## How it Works

The ```/entity-name/{id}/show``` route points to the ```show()``` method in your EntityCrudController, which shows all columns that have been set up using [column types](/docs/{{version}}/crud-columns), by showing a ```show.blade.php``` blade file.

<a name="enabling"></a>
## How to Use

To enable this operation, you need to use the ```ShowOperation``` trait on your CrudController:

```php
<?php

namespace App\Http\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\CrudController;

class ProductCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;
}
```

This will:
- make a Preview button appear inside the List view;
- allow access to the show view;

By default, the operation tries to show all db columns in the database, but _remove_ any columns and buttons that it thinks you _wouldn't want_ shown. Which works great for simple Eloquent Models, it'll _just work_. But for more complex Models, it might be preferrable to define your own columns, using the same syntax you're using when defining the ListOperation.


<a name="configuring"></a>
## How to Configure

### setupShowOperation()

You can manually define columns inside the ```setupShowOperation()``` method - thereby stopping the default "guessing" and "removing" of columns - you'll start from a blank slate and be in complete control of what columns are shown. For example:

```php
    // if you just want to show the same columns as inside ListOperation
    protected function setupShowOperation()
    {
        $this->setupListOperation();
    }
```

But you can also do both - let Backpack guess columns, and do stuff before or after that guessing, by calling the `autoSetupShowOperation()` method wherever you want inside your `setupShowOperation()`:

```php
    // show whatever you want
    protected function setupShowOperation()
    {
        // MAYBE: do stuff before the autosetup

        // automatically add the columns
        $this->autoSetupShowOperation();

        // MAYBE: do stuff after the autosetup

        // for example, let's add some new columns
        CRUD::column([
            'name'  => 'my_custom_html',
            'label' => 'Custom HTML',
            'type'  => 'custom_html',
            'value' => '<span class="text-danger">Something</span>',
        ]);
        // in the following examples, please note that the table type is a PRO feature
        CRUD::column([
            'name' => 'table',
            'label' => 'Table',
            'type' => 'table',
            'columns' => [
                'name'  => 'Name',
                'desc'  => 'Description',
                'price' => 'Price',
            ]
        ]);
        CRUD::column([
            'name' => 'fake_table',
            'label' => 'Fake Table',
            'type' => 'table',
            'columns' => [
                'name'  => 'Name',
                'desc'  => 'Description',
                'price' => 'Price',
            ],
        ]);

        // or maybe remove a column
        CRUD::column('text')->remove();
    }
```
### Tabs - display columns in tabs

Adding the `tab` attribute to a column, will make the operation display the columns in tabs if `show.tabsEnabled` operation setting is not set to `false`.

```php
public function setupShowOperation()
{
    // using the array syntax
    CRUD::column([
        'name' => 'name',
        'tab' => 'General',
    ]);
    // or using the fluent syntax
    CRUD::column('description')->tab('Another tab');
}
```

By default `horizontal` tabs are displayed. You can change them to `vertical` by adding in the setup function:
`$this->crud->setOperationSetting('tabsType', 'vertical')`

As like any other operation settings, those can be changed globaly for all CRUDs in the `config/backpack/operations/show.php` file.

<a name="widget"></a>
## How to add custom sections(aka. Widgets)

[Widgets](https://backpackforlaravel.com/docs/{{version}}/base-widgets) (aka cards, aka charts, aka graphs) provide a simple way to insert blade files into admin panel pages. You can use them to insert cards, charts, notices or custom content into pages. You can use the [default widget types](https://backpackforlaravel.com/docs/{{version}}/base-widgets#default-widget-types) or [create your own custom widgets](https://backpackforlaravel.com/docs/{{version}}/base-widgets#creating-a-custom-widget-type).

Backpack's default template includes two [sections](https://backpackforlaravel.com/docs/{{version}}/base-widgets#requirements-1) where you can push widgets:

* `before_content`
* `after_content`

To use widgets on show operation, define them inside `setupShowOperation()` function.

```php
public function setupShowOperation()
{
    // dynamic data to render in the following widget
    $userCount = \App\Models\User::count();

    //add div row using 'div' widget and make other widgets inside it to be in a row
    Widget::add()->to('before_content')->type('div')->class('row')->content([

        //widget made using fluent syntax
        Widget::make()
            ->type('progress')
            ->class('card border-0 text-white bg-primary')
            ->progressClass('progress-bar')
            ->value($userCount)
            ->description('Registered users.')
            ->progress(100 * (int)$userCount / 1000)
            ->hint(1000 - $userCount . ' more until next milestone.'),

        //widget made using the array definition
        Widget::make(
            [
                'type'       => 'card',
                'class'   => 'card bg-dark text-white',
                'wrapper' => ['class' => 'col-sm-3 col-md-3'],
                'content'    => [
                    'header' => 'Example Widget',
                    'body'   => 'Widget placed at "before_content" secion in same row',
                ]
            ]
        ),
    ]);

    //you can also add Script & CSS to your page using 'script' & 'style' widget
    Widget::add()->type('script')->stack('after_scripts')->content('https://code.jquery.com/ui/1.12.0/jquery-ui.min.js');
    Widget::add()->type('style')->stack('after_styles')->content('https://cdn.jsdelivr.net/npm/@shoelace-style/shoelace@2.0.0-beta.58/dist/themes/light.css');
}
```

#### Output:
* Using `before_content`:

![](https://i.imgur.com/MF9ePIM.png)
* Using `after_content`

![](https://i.imgur.com/AxC3lAZ.png)

<a name="how-to-overwrite"></a>
## How to Overwrite

In case you need to modify the show logic in a meaningful way, you can create a ```show()``` method in your EntityCrudController. The route will then point to your method, instead of the one in the trait. For example:

```php
use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation { show as traitShow; }

public function show($id)
{
    // custom logic before
    $content = $this->traitShow($id);
    // custom logic after
    return $content;
}
```
