# Generating Code

---

Backpack also provides ways to quickly write code inside your admin panels, for you to customize to your needs. There are two officials tools, both will help you publish, override or generate files, that you can customize to your liking:
- [Backpack Generators](https://github.com/laravel-backpack/generators/) (FREE) - a command-line interface that has already been installed in your project;
- [Backpack DevTools](/products/devtools) (PAID) - a web interface that helps do all of the above and more, from a browser;

<a name="cli"></a>
## Command-Line Interface (CLI) - <span class="badge badge-pill badge-success">FREE</span>

If you've installed Backpack, you already have access to Backpack's command line interface. You can run `php artisan backpack` to get a quick list of everything you can do using our CLI. Here's that same list, a bit more organized:

<a name="generate-full-cruds"></a>
#### Generate Full CRUDs

<table class="table table-striped table-hover table-sm">
    <tr>
        <td><code>php artisan backpack:build</code></td>
        <td>Create CRUDs for all Models that do not already have one.</td>
    </tr>
    <tr>
        <td><code>php artisan backpack:crud</code></td>
        <td>Create a CRUD interface: Controller, Model, Request</td>
    </tr>
</table>

<a name="generate-crud-files"></a>
#### Generate CRUD files

<table class="table table-striped table-hover table-sm">
    <tr>
        <td><code>php artisan backpack:crud-controller</code></td>
        <td>Generate a Backpack CRUD controller.</td>
    </tr>
    <tr>
        <td><code>php artisan backpack:crud-model</code></td>
        <td>Generate a Backpack CRUD model</td>
    </tr>
    <tr>
        <td><code>php artisan backpack:crud-request</code></td>
        <td>Generate a Backpack CRUD request</td>
    </tr>
    <tr>
        <td><code>php artisan backpack:crud-operation</code></td>
        <td>Generate a custom Backpack CRUD operation trait</td>
    </tr>
    <tr>
        <td><code>php artisan backpack:crud-form-operation</code></td>
        <td>Generate a custom Backpack CRUD operation trait with Backpack Form</td>
    </tr>
</table>

<a name="generate-crud-operation-components"></a>
#### Generate CRUD operation components

<table class="table table-striped table-hover table-sm">
    <tr>
        <td><code>php artisan backpack:button</code></td>
        <td>Generate a custom Backpack button</td>
    </tr>
    <tr>
        <td><code>php artisan backpack:column</code></td>
        <td>Generate a custom Backpack column</td>
    </tr>
    <tr>
        <td><code>php artisan backpack:field</code></td>
        <td>Generate a custom Backpack field</td>
    </tr>
    <tr>
        <td><code>php artisan backpack:filter</code></td>
        <td>Generate a custom Backpack filter</td>
    </tr>
</table>

<a name="generate-general-admin-panel-files"></a>
#### Generate general admin panel files (non-CRUD)

<table class="table table-striped table-hover table-sm">
<tr>
    <td><code>php artisan backpack:page</code></td>
    <td>Generate a Backpack Page</td>
</tr>
<tr>
    <td><code>php artisan backpack:page-controller</code></td>
    <td>Generate a Backpack PageController</td>
</tr>
<tr>
    <td><code>php artisan backpack:chart</code></td>
    <td>Create a ChartController and route</td>
</tr>
<tr>
    <td><code>php artisan backpack:chart-controller</code></td>
    <td>Generate a Backpack ChartController</td>
</tr>
<tr>
    <td><code>php artisan backpack:widget</code></td>
    <td>Generate a Backpack widget</td>
</tr>
<tr>
    <td><code>php artisan backpack:add-custom-route</code></td>
    <td>Add code to the <code>routes/backpack/custom.php</code> file</td>
</tr>
<tr>
    <td><code>php artisan backpack:add-sidebar-content</code></td>
    <td>Add code to the Backpack sidebar_content file</td>
</tr>
<tr>
    <td><code>php artisan backpack:publish</code></td>
    <td>Publishes a view to make changes to it, for your project<td>
</tr>
</table>

<a name="installation-and-debugging"></a>
#### Installation & Debugging

<table class="table table-striped table-hover table-sm">
<tr>
    <td><code>php artisan backpack:install</code></td>
    <td>Install Backpack requirements on dev, publish CSS and JS assets and create uploads directory.</td>
</tr>
<tr>
    <td><code>php artisan backpack:require:pro</code></td>
    <td>Require Backpack PRO</td>
</tr>
<tr>
    <td><code>php artisan backpack:require:devtools</code></td>
    <td>Require Backpack DevTools on dev</td>
</tr>
<tr>
    <td><code>php artisan backpack:require:editablecolumns</code></td>
    <td>Require Backpack Editable Columns</td>
</tr>
<tr>
    <td><code>php artisan backpack:publish-middleware</code></td>
    <td>Publish the CheckIfAdmin middleware</td>
</tr>
<tr>
    <td><code>php artisan backpack:fix</code></td>
    <td>Fix known Backpack issues.</td>
</tr>
<tr>
    <td><code>php artisan backpack:user</code></td>
    <td>Create a new user</td>
</tr>
<tr>
    <td><code>php artisan backpack:version</code></td>
    <td>Show the version of PHP and Backpack packages.</td>
</tr>
</table>

<a name="devtools"></a>
## Web Interface (DevTools) - <span class="badge badge-pill badge-warning">PREMIUM</span>

![](https://backpackforlaravel.com/uploads/docs-5-0/devtools/list-models.jpg)

For the people who want to step up their code-generation game, we've created [Backpack DevTools](/products/devtools). It empowers devs to do most of the things our CLI does, but:
- in a more intuitive and easy-to-use environment (web browser);
- in a more complete and correct way (eg. fills in validation rules, relationships etc.);
- can also do things that the CLI can't (eg. generate Seeders, Factories);

Here are a few things DevTools makes easy, and how:

<a name="generate-migration-and-model"></a>
#### Generate Migration & Model

As opposed to the CLI who can only generate an _empty_ model, DevTools can create near-perfect Eloquent Models, that include fillable and relationships. Just fill in one web form:

![](https://backpackforlaravel.com/uploads/docs-5-0/devtools/new-model.jpg)

While the code might not be perfect for complex models (will need a double-check and possibly customizations), it _will_ generate working code, and it _will_ save you the bulk of your work.

<a name="generate-factory-and-seeder"></a>
#### Generate Factory & Seeder

This is something the CLI doesn't offer at all.

When generating a Model, you can also choose to generate a Factory and a Seeder for it. While the generated code isn't 100% perfect for complex models, it's a great starting point - and super-easy to customize after it's generated.

One other benefit of having Factories and Seeders generated is that you'll then be able to Seed your tables (aka. add dummy entries) right from your admin panel, inside DevTools:

![](https://backpackforlaravel.com/uploads/docs-5-0/devtools/seed-model.jpg)


<a name="generate-cruds"></a>
#### Generate CRUDs

The functionality here isn't much different from the CLI. DevTools will allow you to create standard CRUDs for your Eloquent models, from the comfort of your web browser. Hell, it will even show you a list of Models, to see which ones have CRUDs and which ones do not.

![](https://backpackforlaravel.com/uploads/docs-5-0/devtools/list-models.jpg)

*Note:* DevTools will _not_ allow you too choose operations, columns, fields etc for those CRUDs. The CRUDs that are generated are standard, just like the ones provided by our CLI. You can then customize operations, columns, fields etc in code, directly.


<a name="generate-crud-operations"></a>
#### Generate CRUD Operations

While the CLI allows you to create blank operations, DevTools takes that to a whole different level. It allows you to quickly create fully-working Operations, where you just need to customize the logic. Using various combinations, DevTools allows you to create up to 16 types of operations

![](https://backpackforlaravel.com/uploads/docs-5-0/devtools/new-operation.jpg)

<a name="generate-or-publish-operation-files"></a>
#### Generate or Publish Operations Files

Just like the CLI, DevTools will help generate custom buttons, columns, fields or filters... or alternatively... publish the _existing_ blade files, to customize them to your liking.

![](https://backpackforlaravel.com/uploads/docs-5-0/devtools/new-operation-file.jpg)


<a name="generate-custom-page"></a>
#### Generate Custom Page

Just like the CLI, DevTools will also help you generate completely custom admin panel pages, that DO NOT have CRUDs. This is very useful to generate pages for your Dashboards and other types of pages that do not depend on CRUD. Keep in mind you can copy-paste any HTML from https://backstrap.net into the views and it'll look identical.


![](https://backpackforlaravel.com/uploads/docs-5-0/devtools/new-page.jpg)


---


Needless to say, we highlighy recommend purchasing [Backpack DevTools](/products/devtools). It saved us so many hours in development time, we can't even count. It's not a magic bullet - it's NOT a no-code solution. But it will help you generate so much code, and keep you working on the important bits, the actual logic.
