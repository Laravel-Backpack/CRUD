# Release Notes

---

**Planned launch date:** November 21st, 2025

For the past 2.5 years, we've done our very best to make all changes to Backpack in a backwards-compatible way. To not push a new version, because we know it's a pain to upgrade stuff. But... it's time for a new release. Have no fear though, we've made this super easy for you - and the new features & bug fixes are worth it!

Here are the BIG things Backpack v7 brings to the table and why you should upgrade from [Backpack v6](/docs/6.x) to v7. But first... we should give credit where credit is due. **Big BIG thanks to our team: [Pedro Martins](https://github.com/pxpm), [Jorge Castro](https://github.com/jcastroa87)**, [Karan Datwani](https://github.com/karandatwani92)** and [Cristian Tabacitu](https://github.com/tabacitu) for working on this new version - and of course **our paying customers**, who have made all of this possible by supporting our work 🙏

Together, our team has put in an incredible amount of work to make v7 what it is - more than 1000 commits, across more than 8 months, all while still maintaining, bug fixing and improving v6. Again, big thanks to everybody who has helped made this happen - and of course, BIG thanks to our beta testers 🙏

<a name="added"></a>
## Added

### Data Components

Our team has spend a lot of time and effort to make it possible to include the content of our main operations... anywhere you want. In the process, we've not only bent the laws of physics (ok maybe only PHP), but also made those operations _cleaner_.

#### Datatable component

![Datatable component in Backpack for Laravel v7](https://backpackforlaravel.com/uploads/v7/datatable_component.jpg)

You can now include a datatable anywhere you want! Just use the component in your custom views, custom pages or custom operations - it will pick up all the setup from your existing CrudController. [Read more](/docs/{{version}}/base-components#datatable-1).

#### Dataform component

![Dataform component in Backpack for Laravel v7](https://backpackforlaravel.com/uploads/v7/dataform_component.jpg)

You can now include a form anywhere you want! Same as the datatable - just use the component in your custom blade files, and it will pick up the fields from your CrudController. [Read more](/docs/{{version}}/base-components#dataform-component).

#### Datalist component

![Datalist component in Backpack for Laravel v7](https://backpackforlaravel.com/uploads/v7/datalist_component.jpg)

You can now use the content of the Show page for a particular entry... anywhere you want. It's as simple as loading the component and passing the CrudController. [Read more](/docs/{{version}}/base-components#datalist-component).

#### Datagrid component

![Datagrid component in Backpack for Laravel v7](https://backpackforlaravel.com/uploads/v7/datagrid_component.jpg)

Our previous design for the Show operation worked fine... but it wasn't pretty. We've developed an alternative comopnent, so that your Show operation looks a little better - just specify you want the show operation to use `datagrid` in your `config/backpack/operations/show.php`. Of course... you can also use it (you guessed it)... anywhere you want. It's as simple as loading the component and passing the CrudController. [Read more](/docs/{{version}}/base-components#datagrid-component).

### Chips

![Chips in Backpack v7](https://backpackforlaravel.com/uploads/v7/general_chip.jpg)

You know columns, you know fields - please welcome... chips! A chips helps show the information of a database entry, in a format that takes up little space visually. It can be used anywhere you want, but it's particularly useful inside the Show and List operations, to cram as much info as possible in as little space as possible. [Read more](/docs/{{version}}/crud-chips).

### Skins

![Chips in Backpack v7](https://backpackforlaravel.com/uploads/v7/glass_skin.jpg)

It's been 2.5 years since we've adopted Tabler as our default HTML template - and in this time, we have only grown more fond of it. We are truly convinced that it's the best free Bootstrap template for admin panels, and our partnership and commitment has only grown. This new version of Backpack comes with a new feature inside our Tabler theme... skins.

Skins are a simple CSS file, that you can enable/not, to give your admin panel a completely different look. We're launching with one modern skin (that we call "non-liquid glass"). Soon enough, we'll follow up with a few more skins and... a way for you to _quickly_ create a custom skin, that will match your brand colors.

### CRUD Lifecycle Hooks

Previously when working with Operations, developers found themselves needing to _override_ an entire operation method, in order to do things before/after the routes/defaults/operation is set up. This created a lot of duplicate code, and made it hard to maintain. Now, you can use CRUD Lifecycle Hooks to add your own code before/after each operation method. This is a much cleaner way to add your own code, without having to override the entire method. [Read more](/docs/{{version}}/crud-operations#lifecycle-hooks).

### Re-usable Filters - Inside Custom Pages or Operations

![](https://backpackforlaravel.com/uploads/docs/filters/filters-in-custom-page.png)

Starting with this Backpack version, you can use the [filters](/docs/{{version}}/crud-filters) in custom pages too. Instead of being tied to DataTables, filters now trigger generic Javascript events like `backpack:filter:changed`. You can catch those events using custom code in Javascript or Livewire... and do stuff. This it possible to use filters on completely custom pages - like custom dashboards, custom reports or custom operations. [Read more](/docs/{{version}}/crud-filters#use-filters-on-custom-admin-panel-pages).

### Filters inside CustomViews

Filters can now be used inside [Custom Views for your List operation](https://backpackforlaravel.com/docs/{{version}}/crud-operation-list-entries#custom-views-for-listoperation-pro). This means once the admin has selected the Custom View, they can further drill down in the list, using the filters. But not only that... you can _remove_ the general filters and add entirely new filters, just for that Custom View.

### SaveAction Classes

Save actions are now first-class citizens. Instead of maintaining large array definitions in each CrudController, you can encapsulate the behaviour inside PHP classes that implement Backpack\CRUD\app\Library\CrudPanel\SaveActions\SaveActionInterface. Backpack ships with 
- `SaveAndBack`, `SaveAndEdit`, `SaveAndNew` and `SaveAndPreview` as defaults;
- other examples like `SaveAndList`, that you can easily use (more coming as needed);

There's a huge difference, when you create your own SaveAction classes:

```php

// you get to do this in your CrudController
CRUD::addSaveAction(SaveActionOne::class);

// instead of all this
CRUD::addSaveAction([
    'name' => 'save_action_one',
    'redirect' => function($crud, $request, $itemId) {
        return $crud->route;
    }, // what's the redirect URL, where the user will be taken after saving?

    // OPTIONAL:
    'button_text' => 'Custom save message', // override text appearing on the button
    // You can also provide translatable texts, for example:
    // 'button_text' => trans('backpack::crud.save_action_one'),
    'visible' => function($crud) {
        return true;
    }, // customize when this save action is visible for the current operation
    'referrer_url' => function($crud, $request, $itemId) {
        return $crud->route;
    }, // override http_referrer_url
    'order' => 1, // change the order save actions are in
]);
```

For more information, see the [SaveAction docs](https://backpackforlaravel.com/docs/{{version}}/crud-save-actions#save-action-classes-1).

<a name="changed"></a>
## Changed

### New Versions for All Assets

We've bumped the version of ALL javascript and CSS assets we have, across the board. By upgrading to Backpack v7, you're automatically getting the best they have to offer - including Tabler, Bootstrap, Datatables etc.

### Uploaders

We've ironed out all the quirks of Uploaders (and uploaders inside repeatables etc). This needed a few small breaking changes, but nothing that should affect you, if you haven't created custom uploaders. Try them again - they should all work fine now!

### Moved TinyMCE and CKEditor fields & columns

We've moved the TinyMCE & CKeditor fields & columns from PRO to their own addons - released under open-source licenses. Please note that the underlying JS libraries are under GPLv2 license - which means you should not use them inside paid projects without purchasing a license from their respective creators.

### Basset

A revolution in how easy it is to load CSS and JS assets in PHP, but we found out the hard way that making things easy is... _hard_. We're glad to tell you we've finally fixed most problems that came with Basset - and it's more reliable than ever. Additionally, you can now:
- name your assets, to easily use the same asset in multiple places;
- publish the assetmap, to override assets from vendor packages;
- use Basset on localhost (to code without an internet connection);
- store your assets in Git, so there's zero need to run basset in production;

For more information, check out [the docs for Basset's next version](https://github.com/Laravel-Backpack/basset).


<a name="removed"></a>
## Removed

- Support for Laravel 10 and Laravel 11
- Support for PHP lower than 8.2

---

If you like what you see, please help us out - share what you like on social media or tell a friend. To get all of the features above (and a lot more), please [follow the upgrade guide](/docs/{{version}}/upgrade-guide).
