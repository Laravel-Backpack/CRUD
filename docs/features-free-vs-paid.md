# Features (Free vs Paid)

---

Our software is open-core. That means there are features that you can use for free, and features you can only access by purchasing. Our goal with this split was to have:
- a simplified version, that includes what most admin panels absolutely need, in `backpack\crud`; <span class="badge badge-pill badge-success">FREE</span>
- a plug-and-play add-on, that adds features for more complex use cases, in `backpack\pro`; <span class="badge badge-pill badge-info">PRO</span>
- add-ons to help in corner cases (both FREE and PAID);

You do not _need to_ purchase anything from us. But we hope that:
- if you're making money from your project, as soon as you need _one_ paid feature, you can justify its cost, to save the time it takes to build that yourself;
- if you're _not_ making money from your project yet, as the project grows and starts making a profit, you'll _want to_ purchase, to get access to paid features and support its maintenance;

<a name="feature-list"></a>
## Features

Everywhere in our docs, you'll see the <span class="badge badge-pill badge-info">PRO</span> label if it needs the `backpack\pro` add-on. Everything else is free, as part of `backpack\crud`. Here's a comparison table of all features, so you can easily understand what will be a good fit for you:

<table class="table table-sm table-striped table-hover">
  <thead>
    <tr>
      <th></th>
      <th class="text-center">Backpack\CRUD</th>
      <th class="text-center">Backpack\CRUD + <br>Backpack\PRO</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td><strong>Admin UI</strong></td>
      <td class="text-center"></td>
      <td class="text-center"></td>
    </tr>
    <tr>
      <td> &nbsp; - Alerts &nbsp; <a href="/docs/6.x/base-alerts"><i class="fe fe-book-open icon-small"></i></a></td>
      <td class="text-center"><span class="badge badge-success">FREE</span></td>
      <td class="text-center"><span class="badge badge-success">FREE</span></td>
    </tr>
    <tr>
      <td> &nbsp; - Authentication &nbsp; <a href="/docs/6.x/base-about#authentication"><i class="fe fe-book-open icon-small"></i></a></td>
      <td class="text-center"><span class="badge badge-success">FREE</span></td>
      <td class="text-center"><span class="badge badge-success">FREE</span></td>
    </tr>
    <tr>
      <td> &nbsp; - Custom Pages &nbsp; <a href="/docs/6.x/base-about#custom-pages"><i class="fe fe-book-open icon-small"></i></a></td>
      <td class="text-center"><span class="badge badge-success">FREE</span></td>
      <td class="text-center"><span class="badge badge-success">FREE</span></td>
    </tr>
    <tr>
      <td> &nbsp; - Breadcrumbs &nbsp; <a href="/docs/6.x/base-breadcrumbs"><i class="fe fe-book-open icon-small"></i></a></td>
      <td class="text-center"><span class="badge badge-success">FREE</span></td>
      <td class="text-center"><span class="badge badge-success">FREE</span></td>
    </tr>
    <tr>
      <td> &nbsp; - HTML Components &nbsp; <a href="/docs/6.x/base-about#general"><i class="fe fe-book-open icon-small"></i></a></td>
      <td class="text-center"><span class="badge badge-success">180+ components</span></td>
      <td class="text-center"><span class="badge badge-success">180+ components</span></td>
    </tr>
    <tr>
      <td> &nbsp; - Widgets &nbsp; <a href="/docs/6.x/base-widgets"><i class="fe fe-book-open icon-small"></i></a></td>
      <td class="text-center"><span class="badge badge-success">9 widgets</span></td>
      <td class="text-center"><span class="badge badge-success">9 widgets</span> + <a href="/docs/6.x/base-widgets#chart-pro" class="badge badge-info text-white" data-toggle="tooltip" title="Easily create charts from your database entries!">chart</a></td>
    </tr>
    <tr>
      <td><strong>CRUD Panels</strong></td>
      <td class="text-center"></td>
      <td class="text-center"></td>
    </tr>
    <tr>
      <td> &nbsp; - <a href="/docs/6.x/crud-operation-list" class="font-weight-bold">List Operation</a></td>
      <td class="text-center"><span class="badge badge-success">FREE</span></td>
      <td class="text-center"><span class="badge badge-success">FREE</span></td>
    </tr>
    <tr>
      <td> &nbsp; &nbsp; &nbsp; - Columns &nbsp; <a href="/docs/6.x/crud-columns"><i class="fe fe-book-open icon-small"></i></a></td>
      <td class="text-center"><span class="badge badge-success">28 columns types</span></td>
      <td class="text-center">
        <span class="badge badge-success">28 free</span> +
        <a href="/docs/6.x/crud-columns#pro-column-types" class="badge badge-info text-white" data-toggle="tooltip" title="Easily show arrays, markdown, relationships, tables and videos!">29 pro columns</a>
      </td>
    </tr>
    <tr>
      <td> &nbsp; &nbsp; &nbsp; - Buttons &nbsp; <a href="/docs/6.x/crud-buttons"><i class="fe fe-book-open icon-small"></i></a></td>
      <td class="text-center"><span class="badge badge-success">FREE</span></td>
      <td class="text-center"><span class="badge badge-success">FREE</span></td>
    </tr>
    <tr>
      <td> &nbsp; &nbsp; &nbsp; - Search &nbsp; <a href="/docs/6.x/crud-operation-list-entries#the-search-logic"><i class="fe fe-book-open icon-small"></i></a></td>
      <td class="text-center"><span class="badge badge-success">FREE</span></td>
      <td class="text-center"><span class="badge badge-success">FREE</span></td>
    </tr>
    <tr>
      <td> &nbsp; &nbsp; &nbsp; - Filters &nbsp; <a href="/docs/6.x/crud-filters"><i class="fe fe-book-open icon-small"></i></a></td>
      <td class="text-center">-</td>
      <td class="text-center"><a href="/docs/6.x/crud-filters" class="badge badge-info text-white" data-toggle="tooltip" title="Help your admin easily filter their table view - by date, text, options, date range... and more!">10+ filter types</a></td>
    </tr>
    <tr>
      <td> &nbsp; &nbsp; &nbsp; - Export Buttons &nbsp; <a href="/docs/6.x/crud-operation-list-entries#export-buttons"><i class="fe fe-book-open icon-small"></i></a></td>
      <td class="text-center">-</td>
      <td class="text-center"><span class="badge badge-info">PRO</span></td>
    </tr>
    <tr>
      <td> &nbsp; &nbsp; &nbsp; - Details Row &nbsp; <a href="/docs/6.x/crud-operation-list-entries#details-row"><i class="fe fe-book-open icon-small"></i></a></td>
      <td class="text-center">-</td>
      <td class="text-center"><span class="badge badge-info">PRO</span></td>
    </tr>
    <tr>
      <td> &nbsp; - <strong><a href="/docs/6.x/crud-operation-create">Create</a> & <a href="/docs/6.x/crud-operation-update">Update</a> Operations</strong></td>
      <td class="text-center"><span class="badge badge-success">FREE</span></td>
      <td class="text-center"><span class="badge badge-success">FREE</span></td>
    </tr>
    <tr>
      <td> &nbsp; &nbsp; &nbsp; - Fields &nbsp; <a href="/docs/6.x/crud-fields"><i class="fe fe-book-open icon-small"></i></a></td>
      <td class="text-center"><span class="badge badge-success">28 field types</span></td>
      <td class="text-center">
        <span class="badge badge-success">28 free</span> +
        <a href="/docs/6.x/crud-fields#pro-field-types" class="badge badge-info text-white" data-toggle="tooltip" title="For relationships with a lot of entries, complex relationships, fields with subfields, WYSIWYGs, addresses, videos, images and A LOT more!">29 pro fields</a>
      </td>
    </tr>
    <tr>
      <td> &nbsp; &nbsp; &nbsp; - Validation &nbsp; <a href="/docs/6.x/crud-operation-create#validation"><i class="fe fe-book-open icon-small"></i></a></td>
      <td class="text-center"><span class="badge badge-success">3 ways</span></td>
      <td class="text-center"><span class="badge badge-success">3 ways</span></td>
    </tr>
    <tr>
      <td> &nbsp; &nbsp; &nbsp; - Multiple fields per line &nbsp; <a href="/docs/6.x/crud-fluent-syntax#chained-methods"><i class="fe fe-book-open icon-small"></i></a></td>
      <td class="text-center"><span class="badge badge-success">FREE</span></td>
      <td class="text-center"><span class="badge badge-success">FREE</span></td>
    </tr>
    <tr>
      <td> &nbsp; &nbsp; &nbsp; - Split fields into tabs &nbsp; <a href="/docs/6.x/crud-fields#optional-tab-attribute-splits-forms-into-tabs"><i class="fe fe-book-open icon-small"></i></a></td>
      <td class="text-center"><span class="badge badge-success">FREE</span></td>
      <td class="text-center"><span class="badge badge-success">FREE</span></td>
    </tr>
    <tr>
      <td> &nbsp; &nbsp; &nbsp; - Translatable Models &nbsp; <a href="/docs/6.x/crud-operation-update#translatable-models-and-multi-language-cruds"><i class="fe fe-book-open icon-small"></i></a></td>
      <td class="text-center"><span class="badge badge-success">FREE</span></td>
      <td class="text-center"><span class="badge badge-success">FREE</span></td>
    </tr>
    <tr>
      <td> &nbsp; &nbsp; &nbsp; - Save Actions &nbsp; <a href="/docs/6.x/crud-save-actions"><i class="fe fe-book-open icon-small"></i></a></td>
      <td class="text-center"><span class="badge badge-success">FREE</span></td>
      <td class="text-center"><span class="badge badge-success">FREE</span></td>
    </tr>
    <tr>
      <td> &nbsp; - <a href="/docs/6.x/crud-operation-show" class="font-weight-bold">Show Operation</a></td>
      <td class="text-center"><span class="badge badge-success">FREE</span></td>
      <td class="text-center"><span class="badge badge-success">FREE</span></td>
    </tr>
    <tr>
      <td> &nbsp; &nbsp; &nbsp; - Columns &nbsp; <a href="/docs/6.x/crud-columns"><i class="fe fe-book-open icon-small"></i></a></td>
      <td class="text-center"><span class="badge badge-success">28 column types</span></td>
      <td class="text-center">
        <span class="badge badge-success">28 free</span> +
        <a href="/docs/6.x/crud-columns#pro-column-types" class="badge badge-info text-white" data-toggle="tooltip" title="Easily show arrays, markdown, relationships, tables and videos!">29 pro columns</a>
      </td>
    </tr>
    <tr>
      <td> &nbsp; - <a href="/docs/6.x/crud-operation-delete" class="font-weight-bold">Delete Operation</a></td>
      <td class="text-center"><span class="badge badge-success">FREE</span></td>
      <td class="text-center"><span class="badge badge-success">FREE</span></td>
    </tr>
    <tr>
      <td> &nbsp; - <a href="/docs/6.x/crud-operation-reorder" class="font-weight-bold">Reorder Operation</a></td>
      <td class="text-center"><span class="badge badge-success">FREE</span></td>
      <td class="text-center"><span class="badge badge-success">FREE</span></td>
    </tr>
    <tr>
      <td> &nbsp; - <strong>Revise Operation</strong> &nbsp; <a href="/docs/6.x/crud-operation-revise"><i class="fe fe-book-open icon-small"></i></a></td>
      <td class="text-center"><span class="badge badge-success">FREE</span></td>
      <td class="text-center"><span class="badge badge-success">FREE</span></td>
    </tr>
    <tr>
      <td> &nbsp; - <strong>BulkDelete Operation</strong> &nbsp; <a href="/docs/6.x/crud-operation-delete#delete-multiple-items-bulk-delete-pro"><i class="fe fe-book-open icon-small"></i></a></td>
      <td class="text-center">-</td>
      <td class="text-center"><a href="/docs/6.x/crud-operation-delete#delete-multiple-items-bulk-delete-pro" class="badge badge-info text-white" data-toggle="tooltip" title="Easily delete multiple entries in one go!">PRO</a></td>
    </tr>
    <tr>
      <td> &nbsp; - <strong>Clone Operation</strong> &nbsp; <a href="/docs/6.x/crud-operation-clone"><i class="fe fe-book-open icon-small"></i></a></td>
      <td class="text-center">-</td>
      <td class="text-center"><a href="/docs/6.x/crud-operation-clone" class="badge badge-info text-white" data-toggle="tooltip" title="Easily duplicate an entry!">PRO</a></td>
    </tr>
    <tr>
      <td> &nbsp; - <strong>BulkClone Operation</strong> &nbsp; <a href="/docs/6.x/crud-operation-clone#clone-multiple-items-bulk-clone"><i class="fe fe-book-open icon-small"></i></a></td>
      <td class="text-center">-</td>
      <td class="text-center"><a href="/docs/6.x/crud-operation-clone#clone-multiple-items-bulk-clone" class="badge badge-info text-white" data-toggle="tooltip" title="Easily duplicate multiple entries in one go!">PRO</a></td>
    </tr>
    <tr>
      <td> &nbsp; - <strong>Fetch Operation</strong> &nbsp; <a href="/docs/6.x/crud-operation-fetch"><i class="fe fe-book-open icon-small"></i></a></td>
      <td class="text-center">-</td>
      <td class="text-center"><a href="/docs/6.x/crud-operation-fetch" class="badge badge-info text-white" data-toggle="tooltip" title="Easily respond to AJAX requests from relationship, select2_from_ajax and select2_from_ajax_multiple fields!">PRO</a></td>
    </tr>
    <tr>
      <td> &nbsp; - <strong>InlineCreate Operation</strong> &nbsp; <a href="/docs/6.x/crud-operation-inline-create"><i class="fe fe-book-open icon-small"></i></a></td>
      <td class="text-center">-</td>
      <td class="text-center"><a href="/docs/6.x/crud-operation-inline-create" class="badge badge-info text-white" data-toggle="tooltip" title="Empower your admins to add related entries in a modal, without leaving the main form!">PRO</a></td>
    </tr>
  </tbody>
</table>

<br><br>
Both `backpack/crud` and `backpack/pro` will keep receiving active attention, maintenance and care from us, for many years going forward - this is our job. If you have suggestions, please [tell us](https://github.com/laravel-backpack/ideas).

<a name="no-license-needed-on-localhost"></a>
## Add-ons

In addition to our main packages (`backpack\crud` and `backpack\pro`), whose features we've detailed above, we've also developed a series of single-purpose Backpack add-ons. Most developers won't need these, but those who do will be grateful that we took the time:
    - some free: `backpack\permissionmanager`, `backpack\settings`, `backpack\pagemanager`, `backpack\newscrud`, `backpack\menucrud`, `backpack\filemanager`, `backpack\logmanager`, `backpack\backupmanager`, `backpack\revise-operation` etc. <span class="badge badge-pill badge-success">FREE</span>
    - some paid: `backpack\devtools`, `backpack\figma-template` <span class="badge badge-pill badge-warning">PAID EXTRA</span>

We also encourage our community to build third-party add-ons (we've made it [super easy](/docs/{{version}}/add-ons-tutorial-using-the-addon-skeleton)). Our plan is to create more and more add-ons, as we discover more recurring problems, that we can solve for Laravel freelancers and development teams.

For more information, see:
- [our add-ons page](https://backpackforlaravel.com/addons) for more add-ons (including third-party add-ons);
- [our pricing page](https://backpackforlaravel.com/pricing) for the first-party add-ons that we think are _so good_, that they're worth paying for;
