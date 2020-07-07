# Changelog

All Notable changes to `Backpack CRUD` will be documented in this file.


-----------
IMPORTANT
-----------

Since version 4.1.2 we no longer use this file to track changes. Please see
https://github.com/Laravel-Backpack/CRUD/releases

-----------

# Backpack Version 4.1

-----------

## 4.1.1 - 2020-05-08

### Fixed
- #2748 address_algolia field didn't work inside repeatable fields;
- summernote field tooltips were not showing properly so we disable them by default;


## 4.1.0 - 2020-05-06

See the [release notes](https://backpackforlaravel.com/docs/4.1/release-notes) and the [upgrade guide](https://backpackforlaravel.com/docs/4.1/upgrade-guide). TLDR:

### Added
- new operation - InlineCreate
- new operation - Fetch
- new field - repeatable
- new field - relationship
- new column - relationship
- new column - relationship_count
- new widget - chart
- new optional API - fluent syntax for Fields, Column, Filters, Buttons, Widgets
- inside list operation
    - hide/show the searchbar with a config
    - hide/show a Reset button that clears filtering pagination everything
- customizable SaveActions (buttons at the end of Create/Update forms)

### Changed
- fields `wrapperAttributes` is now `wrapper`
- line-awesome uses `la la-home` syntax instead of `fa fa-home`

### Removed
- support for Laravel 5.8;
- support for PHP lower than 7.2.5;
- laravel/helpers dependency;
- barryvdh/laravel-elfinder dependency;
- venturecraft/revisionable dependency;
- intervention/image dependency;
- App\Models\BackpackUser;
- RevisionsOperation - it's now an add-on;
- elFinder functionality - it's now an add-on;


-----------

# Backpack Version 4

-----------

## 4.0.61 - 2020-05-06

### Changes
- pameters not respecting what passed @dilneiss (#2705)
- Rtl refines @ahmadmrj (#2703)
- Date range calendar Icon @martijnb92 (#2695)
- Date picker calendar Icon @martijnb92 (#2694)

### Changes
- Bump places.js from 1.18.1 to 1.18.2 @dependabot-preview (#2741)
- Bump moment from 2.24.0 to 2.25.0 @dependabot-preview (#2739)
- Bump css-loader from 3.5.0 to 3.5.3 @dependabot-preview (#2740)

## 4.0.60 - 2020-04-21

### Fixed
- Fixed breadcrumb alignment in rtl html direction. @ahmadmrj (#2691)
- added names to edit-account-info post route @iMokhles (#2672)
- Security upgrade jquery from 3.4.1 to 3.5.0 @snyk-bot (#2671)
- use named route for login link @juventus18 (#2658)
- Fix docblock in Validation trait @JaZo (#2664)
- Fix different date formats in Date Range Field @FakeFakers (#2627)

## 4.0.59 - 2020-04-07

### Fixed
- updated npm dependencies;


## 4.0.58 - 2020-03-31

### Fixed
- Fixing arguments to set settings value @shadowbane (#2618)
- Update checklist.blade.php @PiahDoNeumann (#2598)

## 4.0.57 - 2020-03-23

### Fixed
- #2592 - fixed widget style not loading;


## 4.0.56 - 2020-03-19

### Fixed
- #2217 - odd bump when opening/closing the sidebar-pills;


## 4.0.55 - 2020-03-19

### Fixed
- In datatabes, when zero records, only show that inside the table, not as a subheading too;
- Updated npm dependencies to update minimist;


## 4.0.54 - 2020-03-15

### Fixed
- Deleted obsolete line about cheaper licences @genesiscz (#2556)
- Added a Czech translation for base @genesiscz (#2557)
- Fixed switch icon_picker @adriallongarriu (#2476)
- Updated acorn dependency @tabacitu (#2559)


## 4.0.53 - 2020-03-10

### Fixed
- #2532 during installation, the published elFinder menu item had its quotes doubled;


## 4.0.52 - 2020-03-09

### Fixed
- #2529 fixes #2525 and #2523 - removed PUT route for the Create operation, which didn't serve any direct purpose but caused errors when running ```php artisan optimize``` for some, under Laravel 7;


## 4.0.51 - 2020-03-09

### Fixed
- #2528 fix installation problem - ```elfinder:publish``` command didn't work on Windows; 


## 4.0.50 - 2020-03-09

### Fixed
- #2524 - Change include_all_form_fields data attribute to explicitly output string true/false;
- #2526 - debug flag in installation command;


## 4.0.49 - 2020-03-08

### Fixed
- Symmetry alignment and window widening for password reset view @urlportal (#2516)
- Actualisation of Russian localisation @urlportal (#2515)
- Update @onurmutlu (#2514)
- Fixed ```php artisan backpack:version``` command @tabacitu (#2520)


## 4.0.48 - 2020-03-06

### Fixed
- Create/Update operation tabs no longer worked because of Str::slug() helper;


## 4.0.47 - 2020-03-05

### Fixed
- Delete operation can respond with Notification Bubbles @tabacitu (#2477)
- Elfinder mime type filter. @pxpm (#2505)
- Implement orderButtons @tabacitu (#2457)
- Allow delay on AJAX calls for select2_from_ajax fields @pxpm (#2504)

## 4.0.46 - 2020-03-05

### Fixed
- added back revisionable as a dependency;


## 4.0.45 - 2020-03-05

### Fixed
- installation command did not correctly use the new version of Symphony Process;


## 4.0.44 - 2020-03-04

### Fixed
- installation command on Laravel 7;


## 4.0.43 - 2020-03-04

### Added
- support for Laravel 7;

### Fixed
- #2501 - Backpack is no longer using the Auth services from Laravel (5.8-7); because Laravel moved its services in a different package in Laravel 7, but we want to support L5.8, L6 and L7 at the same time, we've been forced to move those classes inside Backpack, and we've done so, inside app\Library\Auth; Backpack is now completely indepenendent from Laravel's authentication;


## 4.0.42 - 2020-03-01

### Fixed
- updated npm dependencies;
- CSS assets inside elFinder views;


## 4.0.41 - 2020-02-16

### Fixed
- merged #2450 - video field now has customizable youtube api key;
- merged #2453 - radio field can have attributes even if inline;
- merged #2455 - select_from_array column fallback if value not in options array;


## 4.0.40 - 2020-02-10

### Fixed
- merged #2438 - exception class for isColumnNullable();


## 4.0.39 - 2020-02-06

### Fixed
- merged #2426 - select2_from_ajax field did not save empty array when Clear button was pressed;


## 4.0.38 - 2020-02-04

### Fixed
- merged #2428 - bug in installation process published elfinder files even when not necessary;


## 4.0.37 - 2020-02-02

### Fixed
- (fixed in Backstrap) when closing the sidebar, the content is centered left-to-right;
- on mobile and tablet, the hamburger menu is no longer out of the container, we have the same margin left-to-right;
- updated Backstrap to 0.2.22, updated all JS dependencies, re-published all CSS and JS assets;


## 4.0.36 - 2020-01-28

### Fixed
- merged #2401 fixes #2390 - dropdown filter did not properly reset when clearing filters;


## 4.0.35 - 2020-01-23

### Fixed
- merged #2389 - print button on Show operation (aka Preview);
- merged #2386 - overwrite new loggedOut() method in LoginController instead of logout();
- merged #2395 - simplemde field should not download FontAwesome;


## 4.0.34 - 2020-01-20

### Fixed
- merged #2377 - updated Chinese translation;
- merged #2359 - fix field label generation when name is an array;


## 4.0.33 - 2020-01-15

### Fixed
- merged #2353 - markdown column does not show error when null on Laravel 6.10+;


## 4.0.32 - 2020-01-14

### Added
- merged #2351 & #2352 - Indonesian language file;
- merged #2369 - fixes #2365 and #2366 - browse_multiple field type had an extra php tag;


## 4.0.31 - 2020-01-03

### Fixed
- merged #2330 - translation strings for clone button;
- merged #2343 fixes #2338 - installation on windows failed if the public/uploads directory existed; 


## 4.0.30 - 2019-12-20

### Fixed
- merged #2324 fixes #2323 - multiple summernote fields on page with different options;
- merged #2294 fixes #2293 - request inconsistency between Controller and CrudPanel object;


## 4.0.29 - 2019-12-19

### Added
- support for PHP 7.4;

### Fixed
- merged #2183 fixes #1797 - columns weren't orderable in MSSQL;


## 4.0.28 - 2019-12-19

### Added
- Persian language added;

### Fixed
- merged #2136 - fixed Create operation with foreign keys that are not inside the form;
- reversed Laravel composer requirement - first 6, then 5.8;


## 4.0.27 - 2019-12-17

### Fixed
- #2306 - pt-BR translation fixes;
- changed default "install elFinder" answer to "false" when installing Backpack;
- fixed installation progress bar;
- fixed ScrutinizerCI warnings on installation process;


## 4.0.26 - 2019-12-16

### Fixed
- #2149 - select2_multiple field support for UUIDs;


## 4.0.25 - 2019-12-16

### Fixed
- #2292 - admin panel home link broke the installation because it used url() inside config files;


## 4.0.24 - 2019-12-14

### Fixed
- merged #2287 - CrudTrait's ```isColumnNullable``` should not throw error if the column does not exist; that way you can use it on non-existing columns;
- merged #2292 fixes #2289 - config for changing the URL for the top-left logo to something else;
- merged #2101 - Don't force the default controller namespace;
- merged #2268 - video field's input should be type url not text;


## 4.0.23 - 2019-12-02

### Fixed
- merged #2270 - missing ES language translations;
- merged #2261 - browse_multiple field can now be sortable;


## 4.0.22 - 2019-11-25

### Fixed
- merged #2260 - image field type JS function name was wrong;


## 4.0.21 - 2019-11-23

### Fixed
- merged #2257 fixed #2256 - upload field type could not clear the input;
- merged #2243 fixes #1828 - select_all not working in a second select2_multiple on the same page;
- merged #2113 - datetime_picker field did not have its locale working;


## 4.0.20 - 2019-11-23

### Fixed
- merged #2245 fixes #2178 - ShowOperation should use column keys, not column names, when doing stuff to columns;
- merged #2249 - upgrades minimum required version for angular to 1.7.9 to fix a security vulnerability;
- merged #2211 fixes #2188 - allow custom wrapperAttributes on upload, upload_multiple, image and base64_image fields without breaking stuff;


## 4.0.19 - 2019-11-19

### Fixed
- merged #2247 - datatables persistentTable bug introduced by #2220;


## 4.0.18 - 2019-11-18

### Fixed
- merged #2229 fixes #2219 - select_from_array field did not work properly with one or no options;
- merged #2215 - use translated yes/no strings for check column;
- merged #2220 fixes #2193 #2165 #2231 - various datatable issues with persistentTable and Export Buttons;


## 4.0.17 - 2019-11-18

### Fixed
- merged #2119 - no license check if no remote address is set;
- no license check if both debug is true and env is local;
- update all CSS and JS assets but line-awesome;


## 4.0.16 - 2019-11-17

### Fixed
- merged #2232 fixes #2233 - setPersistentTable() did not work because it was a getter instead of setter;
- merged #2073 - select_from_array always used the default value;
- merged #2207 - when the sidebar was open on mobile there was a slight chin to the navbar;


## 4.0.15 - 2019-11-15

### Fixed
- merged 2218 fixes #2142 - check column type always had its label shown; 
- merged #2216 - user menu dropdown did not have a closing div;
- merged #2225 fixes #2224 - sidebar menu item was not active when inside Create, Update or Preview operations;
- merged #2182 - language switch floated wrong;
- merged #2196 fixes #2195 and #2192 - order logic did not work with orderByRaw and inRandomOrder;


## 4.0.14 - 2019-11-12

### Fixed
- merged #2206 fixes #2161 - fixes setSubheading typo;
- merged #2209 - default saveAllInputsExcept to ONLY when saveAllInputsExcept is NULL;
- merged #2181 fixes #2145 - missing row class to tabbed fields view;


## 4.0.13 - 2019-11-11

### Fixed
- merged #2156 - checkbox field did not pass boolean validation sometimes;
- merged #2197, fixes #2198 - image and base64_image fields: remove button did not do anything if crop wasn't set;
- merged #2174, fixes #2104 - ability to tell the Create and Update operations to save the request using Except instead of Only, using the new operation-level config item ```saveAllInputsExcept```;


## 4.0.12 - 2019-10-24

### Fixed
- merged #2130 - dropdown filter did not show the active item;
- fixed #2102 - multiple select2_ajax filters on one CRUD did not work;
- fixed #2075 - select2_multiple filter couldn't be used twice on one crud;


## 4.0.11 - 2019-10-23

### Fixed
- fixed column model_function_attribute - if no function leave empty;
- fixed column upload_multiple - added prefix attribute so you can prepend something to the URL;


## 4.0.10 - 2019-10-23

### Fixed
- fixed #2152 merged #2154 - ckeditor options were not working;
- fixed #2170 - default columns for autoset table field and table column;
- fixes #2162 - autoSet allows columns to end in AT or ID;
- merged #2155 - better docblock for Validation trait;


## 4.0.9 - 2019-10-12

### Fixed
- merged #2144 fixes #2141 and #2140 - checkbox field label did not trigger checkbox;


## 4.0.8 - 2019-10-11

### Fixed
- user menu dropdown was not shown if the default auth routes were disabled;


## 4.0.7 - 2019-10-06

### Fixed
- fixes #2114 - delete and clone message texts are overly escaped;
- ```image``` and ```base64_image``` field types no longer show empty space when empty;


## 4.0.6 - 2019-10-04

### Fixed
- fixes #2104 - page_or_link field type;


## 4.0.5 - 2019-09-28

### Fixed
- fixes #2091 - could not overwrite what the operation was doing in its defaults, only if you used operation closures;


## 4.0.4 - 2019-09-28

### Fixed
- fixes #2089 - simplemde field type could not be used multiple times in one page, and overwrote textarea fields;


## 4.0.3 - 2019-09-28

### Fixed
- merged #2065 fixes #2084 - ```Route::crud()``` macro now works inside deeply nested route groups;
- fixes #2088 - could not add column with name or key with dot notation; it got converted by ```getOperationSetting()``` into an array; keys are now dot notation free - dot gets replaced with ```__```;


## 4.0.2 - 2019-09-28

### Fixed
- YUMMY license text is now more clear; added penalties;
- delete button shows a modal for longer if the delete failed - it's more useful that way;
- clearer issue template for github;
- merged #2087, fixes #2086 - image field type could not be added twice to a CRUD;
- added link to free license application form to Github auto-reply on merged PR;
- removed backpack/base version from stats;


## 4.0.1 - 2019-09-25

### Fixed
- installation command used a wrong service provider for Elfinder;


## 4.0.0 - 2019-09-24

### Added
- merged #1955 - ```image``` field type has a new ```max_file_size``` option; which defaults to the defaults to ```upload_max_filesize``` set in PHP;
- merged #1913 - new design based on CoreUI, instead of AdminLTE; 
- developers can now add widgets to the top/bottom of the operation views;
- ```CRUD``` facade, so developers can now do ```CRUD::addField()``` instead of ```$this->crud->addField()```;
- the ability for developers to use a different CrudPanel object instead of the one in the package; this way, they can customize/overwrite how anything works inside the CrudPanel object;
- routes are now defined inside operations; you no longer need to edit the route file to add routes to one controller; you can now re-use an operation on different controllers and it will also add the necessary routes;
- merged #2012 - phone column type;
- merged #1997 - settings API;
- when specifying a route to an EntityCrudController, an "operation" is specified for each action; that "operation" is used as the string name of the operation (basically doing setOperation() automatically); there are currently two ways to set the current operation: (1) by defining the operation when defining the route, and (2) by doing setOperation()
- inside each operation action, it's no longer required to run ```setOperation()```; but IT IS required to run ```setConfigurationFromSettings()``` so that anything that was inside operation closures gets run;
- All Backpack/Base functionality inside Backpack\CRUD;
- Laravel-Backpack/Base#384 - Ability to toggle breadcrumbs on/off;
- Laravel-Backpack/Base#385 - NPM and Laravel Mix for CSS & JS dependencies;
- Laravel-Backpack/Base#385 - By default Backpack\Base no longer loads anything from CDNs;
- Laravel-Backpack/Base#387 - Easily add scripts/style to all admin panes, using asset() or mix();
- Laravel-Backpack/Base#387 - Easily remove the bundled js and css and use CDNs if you want to;
- Laravel-Backpack/Base#380 - New design - Backstrap, based on CoreUI;
- [Webfactor/Laravel-Generators](https://github.com/webfactor/laravel-generatorssu) to the installation command;
- the Create and Update operations only save the values of the fields (determined using the name attribute); anything else it ignores; this is for security reasons - to prevent saving fields that have been inserted in the front-end maliciously; 
- field types can now have arrays for names, instead of strings; when a field type wants to save multiple attributes, it should have all of them as array in the "name" field attribute; this makes sure that they will get saved in the database;
- support for Right-to-Left languages (just change a variable in the base config file);


### Fixed
- merged #1984 fixes #1952 and #1981 - ```table``` fied type has been rewritten using JQuery instead of Angular, for consistency;
- merged #1977 - fields, filters and operations now use LOCAL assets, instead of CDNs; Backpack can now be used on intranets;
- merged #1947 fixes #1927 - package version was often incorrect, due to maintainers not updating the number on each patch release; fixed by using ocramius/package-versions to determine the package version;
- merged #1950 - reorder operation is now twice as fast;
- merged #1994 - moved SaveActions to the CrudPanel object, since they're not an operation;
- delete button now shows up (and works) in the Show operation view;
- for the List operation, the default order is now by primary key DESC (instead of ASC); backwards-compatible, in that if a different order has been set for the primary key, that one will be used instead;
- we've reduced the default character limit for a all columns that had it - previously if ```text```, ```email```, ```model_function```, ```model_function_attribute```, ```phone```, ```row_number```, ```select``` column had its contents bigger than 50 characters, it got shortened (_Something some[...]_); we've reduced this limit to 40 characters, so that more columns can fit into one screen by default; you can overwrite this default with ```'limit' => 50``` in your column;
- checklist_dependency field now uses array for name;
- date_range field now uses array for names;


### Removed
- CrudControllers now come with zero operations and zero routes by default; old EntityCrudControllers should now specifically mention which operations should be loaded, using operation traits;
- ```CRUD::route()``` is no longer the way to load routes for a CrudController, but ```Route::crud()```;
- merged #1994 - moved SaveActions methods to the CrudPanel object;
- no CRUD access is provided by defaul; access is automatically given when using an operation trait on an EntityCrudController;
- Backpack/Base as a separate package; It's now included in Backpack/CRUD;
- AdminLTE dependency;
- Backpack/Generators from the installation command;
- Laracasts/Laravel-Generators from the installation command;
- Since all Backpack/Base/app classes have been moved to Backpack/CRUD/app, when upgrading to 4.0 you need to do a search-and-replace in all your application files; search for "Backpack/Base/app" and replace with "Backpack/CRUD/app"; make sure you include the following folders: app, config, resources/views, routes;
- If you're importing or extending our BaseController (```Backpack/Base/app/Http/Controllers/BaseController``` or ```Backpack/CRUD/app/Http/Controllers/BaseController```) anywhere inside your app, know that that controller is no longer needed. It's identical to ```Illuminate\Routing\Controller```, so you can use that instead. We haven't removed the file in this version yet, but it's considered deprecated and will be removed in the next version; 


-----------

# Backpack Version 3

-----------

## [3.6.33] - 2019-09-17

### Added
- japanese translation;


## [3.6.32] - 2019-09-17

### Added
- boolean field type, as an alias to the checkbox field type;


### Fixed
- merged #2058 - autoset generates boolean column/field type from boolean/tinyint db columns;


## [3.6.31] - 2019-09-01

### Fixed
- fixes #2010 - number column should show null when null, not zero;


## [3.6.30] - 2019-09-01

### Fixed
- fixes #1982 merged #1983 - AutoSet uses model connection instead of default connection;
- French language fixes;
- fixes #2006 merged #2007 - Create/Update forms not opening the correct tab when Saving and Editing an item multiple times;


## [3.6.29] - 2019-08-23

### Fixed
- fixes #1972 - removed var_dump from select2_from_array field;


## [3.6.28] - 2019-08-20

### Added
- merged #1750 - localization for select2 filters;

### Fixed
- fixed #1762 - uploaded have unique file names even if the same file is submitted twice in the same form;
- fixed #1652 - small ```table``` field and ```table``` column bugs when inserting empty last rows;
- adresses #1224 - ```select2_from_array``` with multiple now shows empty option when all are unselected;


## [3.6.27] - 2019-08-17

### Fixed
- merged #1634 - using closest instead of parents for datetime_picker field type;
- merged #1594 - fixes ```upload_multiple``` field error when casting to array;


## [3.6.26] - 2019-08-17

### Added
- merged #1795 - adds a ```removeButtons()``` method to the CRUD API, which allows developers to remove multiple buttons in one go;
- merged #1965 - adds a ```multiple``` attribute for ```select2_multiple``` field, which allows developers to force the user to only select one item, even though the relationship is n-n;

### Fixed
- merged #1964, fixes #1836 - allows carbon immutable dates;


## [3.6.25] - 2019-08-17

### Added
- merged #1952 - ```json``` column type;

### Fixed
- merged #1906 fixes #1902 - save_and_back url redirected to the default language, instead of the current editing locale;
- merged #1896 - default button view namespace is now ```crud::buttons```;
- merged #1921 - table column type can now output both arrays and objects;
- merged #1852 - syncPivot() method now allows pivot data;
- merged #1954 - semicolons on date_picker field js;
- merges #1962 fixes #1910 - allows keyboard use on date_picker field type;
- merges #1945 - select_and_order did not have its default values after 2 saves;


## [3.6.24] - 2019-07-23

### Added
- merged #1886 - applying the filters is done in a separate method; so that filters can be used outside the List operation;


## [3.6.23] - 2019-07-09

### Fixed
- issue #1922 - merges #1923 - list view details modal is now prettier - table has table and table-hovered classes;


## [3.6.22] - 2019-07-03

### Added
- merged #1899 - Czech translation, thanks to [Aleš Zatloukal](https://github.com/aleszatloukal);
- merged #1891 - support for MongoDB, thanks to [andrycs](https://github.com/andrycs);
- merged #1911 - markdown column type;
- merged #1908 - added options to tinymce field type;

### Fixed
- merged #1917 - typo in image column type;
- merged #1901 - pushed checklist_dependency var into crud_fields_scripts stack;


## [3.6.21] - 2019-05-16

### Fixed
- issue #1889 - a space was present in some columns after the text, which was inconvenient for copy-pasting;

## [3.6.20] - 2019-05-09

### Added
- fixes #1591 - added base64 and disk support to ```image``` column type;

### Fixed
- unit tests failed because ```is_countable()``` helper cannot be included in unit tests;


## [3.6.19] - 2019-05-09

### Added
- merged #1884 - added ```is_countable()``` helper;

### Fixed
- fixed #1861 merged #1882 - ```count()``` was run on non-countable object;


## [3.6.18] - 2019-05-08

### Fixed
- #1789 - ```select_and_order``` javascript error fixed;


## [3.6.17] - 2019-05-07

### Fixed
- fixes #1824; merges #1880 - inside the ListEntries operation, ```visibleInModal``` did not work for columns that were NOT ```visibleInTable```;
- fixes #1806 - visible export and column visiblity buttons when using fixed adminlte layout;

## [3.6.16] - 2019-04-25

### Fixed
- merged #1871 fixes #1808 - unicode characters got escaped in translated models; no mas;


## [3.6.15] - 2019-04-25

### Fixed
- merged #1868 - error when opening revisions timeline because it was still using jessengers/date;


## [3.6.14] - 2019-04-25

### Fixed
- merged #1858 - ```password``` field type now has ```autocomplete = off``` by default; since that's better in most use cases;
- fixes #1343 - hides the Remove Filters button when all filters are cleared;
- merged #1863 fixes #1862 - makes ```image```, ```base64_image``` and ```checklist_dependency``` fields look fine on XS displays;

## [3.6.13] - 2019-04-10

### Fixed
- merged #1803 - ```enum``` field type uses one less query to determine enum options;
- #1771 - ```datetime_picker``` field can be manually edited;
- merged #1764 fixes #1763 - persistent table local storage key is now the route instead of the plural name of the entity, because sometimes there can be two entities with the same plural name (or two cruds for one entity) so that is not unique;
- merged #1778 - don't escape labels in Show view;


## [3.6.12] - 2019-04-10

### Added
- merged #1850 - added ```dec_point``` and ```thousands_sep``` to ```number``` column type; parameters and defaults are now the same as PHP's number_format() method: https://www.php.net/manual/en/function.number-format.php


## [3.6.11] - 2019-04-09

### Fixed
- #1851 - cdn.rawgit.com will stop working in October 2019; switched to a different CDN;
- merged #1822 - vertical tabs can be enabled and switched with one line instead of two;
- #1829 - when the item is not deleted, a success bubble is no longer shown, but a warning;


## [3.6.10] - 2019-04-01

### Fixed
- #1769 - vertical tabs not showing on the same line as form content;


## [3.6.9] - 2019-04-01

### Fixed
- merged #1840 - when errors happen in forms with tabs, the first tab that has an error gets selected by default;


## [3.6.8] - 2019-04-01

### Fixed
- hotfix extra brackets introduced in #1847;


## [3.6.7] - 2019-04-01

### Added
- merged #1847 - added ```include_all_form_fields``` option for select2_from_ajax and select2_from_ajax_multiple fields, so that developers can choose NOT to send all form values in the AJAX request;


## [3.6.6] - 2019-03-13

### Fixed
- #1801 - for CRUDs, you can now use entity names that happen to be the same as variables that are already in every request;
- #1563 - allow using database prefix with AutoSet;

### Added
- merged #1782 - adds unofficial MongoDB support; with a few caveats: all columns are nullable; hasColumn() will always return true - so accidentally adding a column twice will be a bad experience for the developer;
- #1818 - ```limit``` attribute to the ```select``` column; defaults to 50;

## [3.6.5] - 2019-03-12

### Fixed
- merged #1830 - ```fire()``` function is not defined in the loaded Dispatcher and throws an error when using sluggables in translatable CRUDs;


## [3.6.4] - 2019-03-03

### Fixed
- ```view``` field type now works with PHP 7.3 (it did not, because of ```compact()``` usage); merged #1825;
- merged #1804 - fixes ```select_grouped``` field for some people;


## [3.6.3] - 2019-03-01

### Fixed
- unit tests on Laravel 5.8;


## [3.6.2] - 2019-03-01

### Fixed
- date and datetime columns now using carbon v2;


## [3.6.1] - 2019-03-01

### Added
- support for Laravel 5.8 through Base 1.1;

### Fixed
- Italian translation, thanks to [Roberto Butti](https://github.com/roberto-butti);


## [3.5.14] - 2019-01-24

### Added
- #1749 - ```$crud->getFilter('name')``` and ```$crud->hasActiveFilter('name')``` methods for Filters;

### Fixed
- #1792 - javascript error on Show page, due to Clone button;
- #1790 - DE translation;
- #1777 - back button; PT translation;


## [3.5.13] - 2019-01-07

### Added
- #1783 - ```limit``` functionality to the ```email``` column type;
- #1770 - added support for dot notation to all relevant column types;


## [3.5.12] - 2018-12-28

### Added
- #1758 - ```image``` column type can now show the image of a connected entity, if you use dot notation for the column name;


## [3.5.11] - 2018-12-13

### Fixed
- #1736 - minor CSS issues in list;
- #1068 - better date and datetime search; searching for "28 nov 2018" now works too, search strings no longer have to be in MySQL format;


## [3.5.10] - 2018-12-05

- merged #1741 - deprecated ```CrudRequest``` uses ```backpack_auth()```;
- upgraded PHPUnit to v7;


## [3.5.9] - 2018-09-28

- fixed #1732 - added support for laravel/translatable 3.x;


## [3.5.8] - 2018-09-28

- fixed #1730 - export buttons causing issues; broken list view;

## [3.5.7] - 2018-09-27

- fixed #1730 - filtered list view count wasn't quite right;

## [3.5.6] - 2018-09-27

- fixed #1728;

## [3.5.5] - 2018-09-27

- fixed #1723 - export buttons look bad when bulk buttons are missing;

## [3.5.4] - 2018-09-26

- fixed #1723 - export buttons now showing;
- fixed #1535 - orderBy did not get respected;

## [3.5.3] - 2018-09-23

- fixed clone button using POST method for AJAX;
- CRUD buttons sometimes extended beyond table;

## [3.5.2] - 2018-09-22

- Travis CI config file changes;


## [3.5.1] - 2018-09-22

- composer.json change, requiring Backpack/Base 1.0.x;


## [3.5.0] - 2018-09-22

### BREAKING
- #1535 - orderBy gets ignored when the user clicks on a column heading to reoder the datatable;
- #1658 - model function button did not pass $crud to button;
- #1680 - Backpack checks that CrudTrait is used on the Model; otherwise it throws a 500 error with a clear message;

### Added
- #1675 - design facelift for the list view - a lot cleaner;
- #1516 - setters and getters for the current operation;
- #1527 - custom titles, headings and subheadings;
- #1518 - CrudPanel class is now macroable;
- #1687 - ```select2_nested``` field type;
- #1703 - ```visibleInTable``` option to columns;
- #1703 - ```visibleInExport``` option to columns;
- #1706 - added ```visibleInShow``` option to columns;
- #1704 - added ```orderLogic``` option for columns;
- #1694 - ```options``` option to ```select```, ```select2```, ```select_multiple```, ```select2_multiple```, that allows developers to filter or order the options shown, using a scope or custom query;
- #1695 - added ```select_and_order``` field type;
- #1708 - added ```Clone``` operation;
- #1712 - added ```address_google``` field type;
- #1674 - you can now pass parameters to ```model_function``` and ```model_function_attribute``` column types; 
- #1484 - added dependant select2s with ajax;
- #1484 - added ```method``` attribute to ajax select2s;
- #1484 - added ```dependencies``` attribute to ajax select2s;
- #1702 - added ```persistent_table``` functionality, and save state datatables;

### Fixed
- #1390 - using our own helper ```mb_ucfirst()``` instead of ```ucfirst()```;
- #791 - could not revert changes made in fake field holders;
- #1712 - renamed ```address``` field type to ```address_algolia```; alias keeps backwards-compatibility;
- #1714 - autoset getting tables now happens only once;
- #1692 - we can now use arrays for field names, like ```category[0][name]```, the only thing that needed to be fixed was the ```old()``` value which did not work;

----

## [3.4.43] - 2018-11-21

## Fixed
- #1717 - French translation;


## [3.4.42] - 2018-11-20

## Fixed
- #870 - error when adding tabs only on update, or only on create;


## [3.4.41] - 2018-11-14

## Added
- #1592 - ```options``` attribute to let developers customize ckeditor;


## [3.4.40] - 2018-11-11

### Added
- #1587 - support for temporaryUrl to upload field type;
- #1693 - Turkish language translations;


### Removed
- obsolete TODO time_picker field; never used;


## [3.4.39] - 2018-11-09

### Fixed
- #1540 fixes #1539 - what happens if actions don't exist because the controller is overwritten;
- fixes #1678 - ```textarea``` column type has default search logic;
- fixes #1676 - pagination in ```select2_from_ajax``` and ```select2_from_ajax_multiple``` fields;
- fixes #509 using #1689 - assets got loaded twice if using tabs;
- fixes #1421 using #1690 - user can now clear inputs, receive validation error and inputs will still be cleared;


## [3.4.38] - 2018-10-26

### Added
- "default" for select field type;

### Fixed
- merged #1651 fixes #1640 - column width when resizing window or sidebar, on unresponsive crud table;
- fixed #1648 - select_from_array column can now display multiple entries;


## [3.4.37] - 2018-10-24

### Fixed
- spanish translation;
- updated datetimepicker version in ```datetime_picker``` field;


## [3.4.36] - 2018-10-15

### Fixed
- loading the custom views folder is now done only if it exists, this way fixing conflicts with the ```php artisan view:cache``` command;


## [3.4.35] - 2018-09-26

### Fixed
- unit tests were failing;
- version update in CrudServiceProvider;

## [3.4.34] - 2018-09-25

### Fixed
- merged #1632 - not showing bulk columns on preview page;
- merged #1617 - don't mark required_with and required_if with asterisks;
- merged #1642 - where ```getRelationModelInstances()``` returns array instead of object;
- merged #1643 - new script for ```address``` field type;
- merged #1614 - show Remove All Filters button even for simple filters;


## [3.4.33] - 2018-09-05

### Fixed
- merged #1625 - docblocks everywhere;
- replaced ```or``` with ```??``` for Laravel 5.7 compatibility;


## [3.4.32] - 2018-09-04

### Added
- merged #1609 - bulk actions;

### Fixed
- Spanish translation;
- Italian translation;


## [3.4.31] - 2018-08-28

### Fixed
- merged #1606 fixes #1605 - ```upload_multiple``` column type fix disk;


## [3.4.30] - 2018-08-10

### Added
- #1589 - ```upload_multiple``` column type;
- ```suffix``` option to ```array_count``` column type;


## [3.4.29] - 2018-08-10

### Fixed
- #1588 - show button had a typo and required the update permission instead of show;

## [3.4.28] - 2018-08-07

### Added
- merged #1577 - French Canadian translation thanks to @khoude24;
- merged #1579 - table column type;
- merged #1588 - sending usage stats to mothership in 1% of pageloads (server info only, no client info);

### Fixed
- text column now json_encodes value if array, so that it does not trigger error;
- merged #1572 - added padding and alignment to list modal when responsive;
- merged #1505 - spatie sluggable inconsistency when querying slugs prepended by quotes;
- merged #1566 - details row is now removed when deleting an entry, thanks to @promatik;
- merged #1576 - removed length from array_slice in addClause;
- merged #1580 - show functionality is now prettier, multi-language and official;

## [3.4.27] - 2018-07-19

### Fixed
- merged #1564 - buttons did not have the ```$button``` variable available inside their blade file;


## [3.4.26] - 2018-07-17

### Fixed
- #1554 - translatable Edit button wasn't entirely visible;
- number column still used TD instead of SPAN;


## [3.4.25] - 2018-07-16

### Fixed
- #1546 by merging #1548 - fake translatable fields when cast as array/object;


## [3.4.24] - 2018-07-16

### Fixed
- #1542 by merging #1543 - validation rules defined as arrays;

## [3.4.23] - 2018-07-11

### Added
- composer.lock to gitignore;

### Fixed
- #1533 - fixed casts with fakes;


## [3.4.22] - 2018-07-10

### Fixed
- #1523 - required asterisks didn't show up when ```wrapperAttributes``` was used;


## [3.4.21] - 2018-07-09

### Added
- #1524 - columns ```searchLogic``` attribute can now receive a string; this will make that column search like it was that column type; so if you pass ```'searchLogic'=> 'text'``` it will search like a text column;
- #1380 - ```$this->crud->disableResponsiveTable()```, ```$this->crud->enableResponsiveTable()``` and a config option to set the default behaviour;
- #1353 - ```$this->crud->modifyColumn($name, $modifs_array)```;
- #1353 - ```$this->crud->modifyField($name, $modifs_array, $form)```;
- #1353 - ```$this->crud->modifyFilter($name, $modifs_array)```;
- #1353 - ```$this->crud->modifyButton($name, $modifs_array)```;

## [3.4.20] - 2018-07-08

### Fixed
- default UpdateRequest and CreateRequest were missing from both CrudController and Operations/Create and Operations/Update, because StyleCI removed them;

## [3.4.19] - 2018-07-07

### Added
- #1501 - priority attribute to addColumn statement and ```$this->crud->setActionsColumnPriority(10000);``` method; first and last column now take priority by default;
- #1507 - actions; the ability to determine what controller method is currently being called by the route using ```$this->crud->getActionMethod()``` and the ```$this->crud->actionIs('create')``` conditional;
- #1495 - asterisk for required fields are added automatically for create&update operations, if ```$this->crud->setRequiredFields(StoreRequest::class, 'create');``` and ```$this->crud->setRequiredFields(UpdateRequest::class, 'edit');``` are defined in the setup() method;

### Fixed
- #1489, #1416 merged #1499 - datatables colvis and responsive incompatibility;
- #1502 - range filter type can now work only with one value - min/max;
- #1510 - renamed CrudFeatures into Operations (simple refactoring);

## [3.4.18] - 2018-07-04

### Removed
- what PR #1416 did;


## [3.4.17] - 2018-06-28

### Added
- merges #1479 - added print button on show view;
- merges #1424 - added --elfinder option to install command;

### Fixed
- merges #1480 - hide back buttons and unnecessary features from print previews;
- merges #1416 - enables responsive mode on the table;


## [3.4.16] - 2018-06-28

### Fixed
- automatically remove ```row_number```` columns from the Preview screen, since it doesn't make sense there and it would break the functionality;
- return to current_tab functionality broke when used with autoSet();

## [3.4.15] - 2018-06-26

### Added
- ```php artisan backpack:crud:publish [folder.file]``` command, thank to [Owen Melbourne's PR in Generators](https://github.com/Laravel-Backpack/Generators/pull/15);
- merged #1471 - ```row_number``` column type;
- merged #1471 - ```makeFirstColumn()``` method for columns;

### Fixed
- #1446 merged, fixes #1430 - return to current tab on save and edit;
- changed syntax for ```php artisan backpack:crud:publish``` command, from ```php artisan backpack:crud:publish field select2``` to ```php artisan backpack:crud:publish fields/select2```; this allows developers to also publish other view files, in other folders, using the same syntax;

## [3.4.14] - 2018-06-22

### Added
- #1443 - ```$this->crud->removeAllFields()``` API call;

### Fixed
- #1462 - date_range filter did not get triggered upon Today click;
- #1459 - select2_ajax filter did not load CSS and JS correctly;
- #1449 merged - fixes #1425 - "Undo revision" triggered 404 error;
- #1447 merged - create/edit's Cancel button now leads to previous page if no list access;
- #1417 merged - autofocus on iterable fields;

## [3.4.13] - 2018-06-04

### Fixed
- #1299 - installer now works on Windows too, thanks to [Toni Almeida](https://github.com/promatik);


## [3.4.12] - 2018-05-30

### Added
- ```range``` filter type;

### Fixed
- all filter clear buttons;
- date_range filter endless loop issue;

## [3.4.11] - 2018-05-16

### Added
- #1319 - format parameter for ```date``` and ```datetime``` column types;
- #1316 - ```closure``` column type;
- #1401 - ```default``` attribute for ```select2``` field type;
- #1388 - ```view_namespace``` attribute for columns;
- #1389 - ```view_namespace``` attribute for filters;
- #1387 - ```view_namespace``` attribute for fields;

### Fixed
- #1407 - AccessDeniedException did not show custom error message at all;
- #1346 - AccessDeniedException error message should show permission that is missing;
- #1076, merged #1355 - ```dropdown``` filter no longer has conflicts with VueJS because of key attribute;
- using null coalesce operator to simplify filters code;


## [3.4.10] - 2018-05-14

### Added
- #1382 - Arabic translation;

### Fixed
- #1326 - sorting in datatables when details_row is enabled;
- #1392 - check column type is now exportable;
- #756 - ```CKEDITOR.style.addCustomHandler is not a function``` by updating ckeditor to 4.9.2;
- #1318 - summernote field type can have separate configuration arrays if multiple summernotes in one form;
- #1398, PR #1399 - datepicker and daterangepicker did not load correct language files;

## [3.4.9] - 2018-05-10

## Fixed
- #1378 - when a custom default page length is specified, it should show up in the page length menu;
- #1297 - possible XSS vulnerability in ```select``` field type; now using ```e()``` to escape the attribute;
- #1383 - ability to display relationship information using dot notation in the ```text``` column type;


## [3.4.8] - 2018-05-07

## Fixed
- better pt_br translation; merged #1368;
- translated name for File Manager sidebar item; merged #1369;


## [3.4.7] - 2018-05-07

## Fixed
- fixed #1364 merged #1306 - datatables javascript issue in IE11;


## [3.4.6] - 2018-04-23

## Fixed
- added TD around columns in preview, to fix it; merges #1344;
- not showing "Remove filters" button when no filter is applied; merges #1343;

## [3.4.5] - 2018-04-17

## Fixed
- getting the correct current id for nested resources; fixes #1323; fixes #252; merges #1339;
- #1321 - setting locale for traversable items; merges #1330;
- LV translation, thanks to @tomsb; merges #1358;

## [3.4.4] - 2018-03-29

## Fixed
- ckeditor button now showing after js update; merges #1310; fixes #1309;


## [3.4.3] - 2018-03-28

## Fixed
- model_function column HTML was escaped;


## [3.4.2] - 2018-03-23

## Fixed
- CrudPanelCreateTest failing 1 test;


## [3.4.1] - 2018-03-22

## Fixed
- HUGE ERROR whereby entities could not be created if they had zero relationships;


## [3.4.0] - 2018-03-22

## Added
- one-line installation command ```php artisan backpack:crud:install```;
- 1-1 relatiosnhips; merges #865;

## Fixed
- ```checkbox``` field was using the default value over the DB value on edit; merges #1239;
- no longer registering Base, Elfinder and Image service providers and aliases, since they all now use auto-load; merges #1279;
- datatables responsive working with colvis and export buttons;

### Removed
- elFinder is no longer a dependency; users should require it themselves, if they need it;

-----------

## [3.3.17] - 2018-03-21

## Fixed
- changed Sluggable traits declarations to PHP 7+; merges #1084;


## [3.3.16] - 2018-03-21

## Added
- JSON response if the create/update action is triggered through AJAX; merges #1249;
- ```view``` filter type and ```view``` column type;

## Fixed
- Romanian translation;
- image field did not show proper image if validation failed; merges #1294;

## [3.3.15] - 2018-03-21

## Fixed
- ```select2_multiple``` filter triggered an error when the entire selection was removed - merges #824;
- fr translation;
- zh-hant translation;


## [3.3.14] - 2018-03-16

## Added
- ```select_all``` option to the ```select2_multiple``` field - merged #1206;
- ```browse_multiple``` field type, thanks to [chancezeus](https://github.com/chancezeus) - merged #1034;

## Fixed
- ```date_range``` filter methods now have custom names, so that more than one ```date_range``` filter can be included in one CRUD list;
- Romanian translation;
- Create/Update form will not show certain buttons, if that operation is disabled - merged #679;

## [3.3.13] - 2018-03-15

## Fixed
- ```checkbox``` field was using the default value over the DB value on edit; merges #1239;
- CrudTrait uses ```Config``` facade to get DB_CONNECTION instead of ```env()``` helper;
- Fake fields can now be casted, as well as 'extras' - merged #1116;

## [3.3.12] - 2018-03-09

## Fixed
- ```text``` column had a broken ```suffix``` attribute; fixed by merging #1261;
- not calling trans() in the config file; merges #1270;

## [3.3.11] - 2018-02-23

## Added
- ```allows_null``` option to ```datetime_picker``` field type;
- #1099 - added ```$this->crud->setPageLengthMenu();``` API call;
- added ```config('backpack.crud.page_length_menu')``` config variable;
- ```summernote``` field ```options``` parameter, for easy customization;
- probot to automatically invite contributors to the ```Community Members``` team, after their first PR gets merged;
- ```default``` option to ```select_from_array``` and ```select2_from_array``` field types; merges #1168;
- ```disk``` option to ```image``` field type;

## Fixed
- click on a column header now ignores the previous ```orderBy``` rules; fixes #1181; merges #1246;
- ```date_range``` field bug, whereby it threw a ```Cannot redeclare formatDate()``` exception when two fields of this type were present in one form; merges #1240;
- ```image``` column type didn't use the prefix for the image link; merges #1174;
- no broken image on ```image``` field type, when no image is present; merges #444;

## [3.3.10] - 2018-02-21

## Added
- ```number``` column type, with prefix, suffix and decimals options;
- prefix, suffix and limit to ```text``` column type;
- setLabeller($callable) method to change how labels are made; merges #688;
- support Github probot that automatically closes issues tagged ```Ask-It-On-Stack-Overflow```, writes a nice redirect message and gives them the proper link;

## Fixed
- #638 and #1207 - using flexbox for equal height rows for prettier inline errors;


## [3.3.9] - 2018-02-14

### Added
- (Github only) probot auto-replies for first issue, first PR and first PR merged;

## Fixed
- double-click on create form created two entries; fixes #1229;

### Deprecated
- CrudRequest; Since it does nothing, CrudController now extends Illuminate\Http\Request instead; merged #1129; fixes #1119;

## [3.3.8] - 2018-02-08

## Removed
- laravelcollective/html dependecy;


## [3.3.6] - 2018-01-16

## Fixed
- base64_image field triggered an error when using the src parameter - merged #1192;


## [3.3.5] - 2018-01-10

## Added
- custom error message for AJAX datatable errors - merged #1100; 
- 403 error on AccessDeniedException;

### Fixed
- CRUD alias is now loaded using package-autodiscovery instead of manually in CrudServiceProvider;
- datatables ajax loading screen was askew when also using export buttons;


## [3.3.4] - 2017-12-19

## Fixed
- ENUM field - Updated ```getPossibleEnumValues``` to use ```$instance->getConnectionName()``` so that enum values are correctly queried when the Model uses a non-default database connection - merged #650;
- addColumn will not overwrite the searchLogic, orderable and tableColumn attributes if otherwise specified;
- Better sorting effect on "table" fields - merged #466;
- When using the Autoset trait, the getDbColumnTypes() method used many separate queries to get the column type and column default; improved performance by merging #1159;
- fakeFields use array_keys_exists instead of isset - merged #734;
- CrudTrait::addFakes now supports objects - merged #1109;


## [3.3.3] - 2017-12-14

## Fixed
- Chinese translation;
- datetimepicker icon now triggers datetimepicker js - merged #1097;
- columns are now picked up using the database connection on the model - merged #1141; fixes #1136;
- model_function buttons now work for top and bottom stacks too - fixes #713;

## [3.3.2] - 2017-12-12

## Added
- loading image on ajax datatables, with fallback to old "Processing" text;

## Fixed
- answers to hasColumns() are now cached, to minimize number of db queries on list view - merged #1122;
- German translation;


## [3.3.1] - 2017-11-06

## Fixed
- unit tests for column key functionality;


## [3.3.0] - 2017-11-06

## Added
- you can now define a "key" for a column, if you need multiple columns with the same name;

## Fixed
- in create/update, fields without a tab are displayed before all tabs;
- unit tests now use PHPUnit 6;
- completely rewritten AjaxTables functionality;
- fixed all AjaxTables issues - merged #710;

### Deprecated
- ```$this->crud->enableAjaxTable();``` still exists for backwards-compatibility, but has been deprecated and does nothing;

### Removed
- DataTables PHP dependency;
- all tables now use AjaxTables; there is no classic tables anymore; 
- removed all classic table filter fallbacks;

-----------

## [3.2.27] - 2017-11-06

## Fixed
- inline validation on nested attributes - merged #987, fixes #986;
- morphed entities caused records in the pivot table to duplicate - merged #772, fixes #369;
- browse field used slash instead of backslash on windows - fixes #496;
- endless loop when using date_range filter - merged #1092;


## [3.2.26] - 2017-10-25

## Added
- prefix option to upload field type;

## Fixed
- when creating an entry, pivot fields were overwriting the $field variable - merged #1046;
- Italian translation file;
- select fields old data values;
- date_range field triggered error on Create;
- bug where non-translatable columns in translatable models got their $guarded updated - merged #754;


## [3.2.25] - 2017-10-24

## Added
- number of records per page menu now features "All", so people can use it before exporting results when using AjaxDataTables;
- prefix option for the image column (merged #1056; fixes #1054);


## [3.2.24] - 2017-10-23

## Fixed
- daterange field did not use the correct value if the start_date and end_date were not casted in the model - merged #1036;
- PR #1015 - fixes #798 - fixed field order methods;
- PR #1011 - fixes #982 and #971 - fixed column order methods;
- radio column not showing value - PR #1023;

## [3.2.23] - 2017-10-16

## Added
- Added config option to choose if the save actions changed bubble will be shown;

## Fixed
- lv language file spelling error;


## [3.2.22] - 2017-09-30

## Fixed
- date_picker initial display value offset - PR #767, fixes #768;
- unit test badge from Scrutinizer reported a wrong coverage %;


## [3.2.21] - 2017-09-28

## Added
- clear button to select2_from_ajax field type;
- autoSet is now using the database defaults, if they exist;
- cleaner preview page, which shows the db columns using the list columns (big thanks to [AbbyJanke](https://github.com/AbbyJanke));
- if a field has the required attribute, a red start will show up next to its label;
- shorthand method for updating field and column labels - setColumnLabel() and setFieldLabel();
- select_from_array column type;
- image column type;

## Fixed
- bug where you couldn't remove the last row of a table field;
- Switching from using env() call to config() call to avoid issues with cache:config as mentioned in issue #753;


## [3.2.20] - 2017-09-27

## Added
- UNIT TESTS!!! I KNOW, RIGHT?!
- fourth parameter to addFilter method, that accepts a fallback logic closure;
- ability to make columns non-orderable using the DataTables "orderable" parameter;

## Fixed
- zh-cn instead of zh-CN language folder - fixes #849;
- can't move a column before/after an inexisting column;
- can't move a field before/after an inexisting field;
- fixed beforeField() and afterField() methods;
- fixed beforeColumn() and afterColumn() methods;
- calling setModel() more than once now resets the entry;
- you can now store a fake field inside a column with the same name (ex: extras.extras);
- boolean column values can now be HTML;
- select2 filter clear button now works with ajax datatables;
- select2_from_ajax_multiple field old values fix;
- CrudTrait::isColumnNullabel support for json and jsonb columns in postgres;
- form_save_buttons had an untranslated string;
- deprecated unused methods in CrudPanel;


## [3.2.19] - 2017-09-05

## Added
- text filter type;

## Fixed
- date_range field start_name value always falled back to default - #450;
- hidden field types now have no height - fixes #555;
- image field type can now be modified in size - fixes #572;
- we were unable to save model with optional fake fields - fixes #616;

## [3.2.18] - 2017-08-30

## Added
- Package autodiscovery for Laravel 5.5;


## [3.2.17] - 2017-08-22

## Fixed
- SluggableScopeHelpers::scopeWhereSlug() signature, thanks to [Pascal VINEY](https://github.com/shaoshiva);


## [3.2.16] - 2017-08-21

## Added
- translation strings for CRUD export buttons, thanks to [Alashow](https://github.com/alashow);

## Fixed
- you can now skip mentioning the model for relation fields and columns (select, select2, select2multiple, etc) - it will be picked up from the relation automatically;


## [3.2.15] - 2017-08-11

## Added
- Danish (da_DK) language files, thanks to [Frederik Rabøl](https://github.com/Xayer);


## [3.2.14] - 2017-08-04

## Added
- Brasilian Portugese translation, thanks to [Guilherme Augusto Henschel](https://github.com/cenoura);
- $crud parameter to the model function that adds a button;

## Fixed
- setFromDb() now uses the column name as array index - so $this->crud->columns[id] instead of $this->crud->columns[arbitrary_number]; this makes afterColumn() and beforeColumn() work with setFromDb() too - #759;
- radio field type now has customizable attributes - fixes #718;
- model_function column breaking when not naming it - fixes #784;
- video column type uses HTTPs and no longer triggers console error - fixes #735;


## [3.2.13] - 2017-07-07

## Added
- German translation, thanks to [Oliver Ziegler](https://github.com/OliverZiegler);
- PHP 7.1 to TravisCI;

### Fixed
- resources loaded twice on tabbed forms - fixes #509;
- beforeColumn and afterColumn not working after setFromDb();
- afterField() always placing the field on the second position;
- date_range filter - clear button now works;
- select2 variants load the JS and CSS from CDN now to fix styling issues;
- show_fields error when no tabs on CRUD entity;

## [3.2.12] - 2017-05-31

### Added
- Latvian translation files (thanks to [Erik Bonder](https://github.com/erik-ropez));
- Russian translation files (thanks to [Aleksei Budaev](https://a-budaev.ru/));
- Dutch translation files (thanks to [Jelmer Visser](https://github.com/jelmervisser))

### Fixed
- allow for revisions by non-logged-in users; fixes #566;
- upgraded Select2 to the latest version, in all select2 fields;
- fixed select2_from_ajax_multiple;
- translated "edit translations" button;
- localize the filters navbar view;
- inline validation error for array fields;
- moved button initialization to CrudPanel constructor;
- pagelength bug; undoes PR #596;


## [3.2.11] - 2017-04-21

### Removed
- Backpack\CRUD no longer loads translations, as Backpack\Base does it for him.

## [3.2.10] - 2017-04-21

### Added
- prefix feature to the image field;

### Fixed
- select_multiple has allows_null option;
- details_row for AjaxDataTables;


## [3.2.9] - 2017-04-20

### Added
- email column type;

### Fixed
- fewer ajax requests when using detailsRow;
- redirect back to the same entry - fixed by #612;
- use "admin" as default elfinder prefix;
- datepicker error fixed by [Pavol Tanuška](https://github.com/pavoltanuska);
- simplemde field also triggered ckeditor when place before it, because of an extra class;
- details row column can be clicked entirely (thanks to [votintsev](https://github.com/votintsev));
- simpleMDE bug fixes and features #507 (thanks to [MarcosBL](https://github.com/MarcosBL));
- allow for dot notation when specifying the label of a reordered item (thanks to [Adam Kelsven](https://github.com/a2thek26));


## [3.2.8] - 2017-04-03

### Added
- fixed typo in saveAction functionality;
- checklist field had hardcoded primary key names;
- french translation for buttons;

## [3.2.7] - 2017-03-16

### Added
- Simplified Chinese translation - thanks to [Zhongwei Sun](https://github.com/sunzhongwei);
- date and date_range filters - thanks to [adriancaamano](https://github.com/adriancaamano);

### Fixed
- fixed horizontal scrollbar showing on list view;
- fixed edit and create extended CSS and JS files not loading;
- fixed AjaxDataTables + filters bug (encoded URL strings);
- replaced camel_case() with str_slug() in tab ids, to provide multibyte support;


## [3.2.6] - 2017-03-13

### Fixed
- custom created_at and updated_at columns threw errors on PHP 5.6;


## [3.2.5] - 2017-03-12

### Fixed
- SaveActions typo - fixes #504;
- Allow for custom created_at and updated_at db columns - fixes #518;
- base64_image field - preserve the original image format when uploading cropped image;
- fix bug where n-n relationship on CREATE only triggers error - fixes #512;
- reduce the number of queries when using the Tabs feature - fixes #461;


## [3.2.4] - 2017-02-24

### Fixed
- Spanish translation;
- Greek translation;
- select2_from_ajax, thanks to [MarcosBL](https://github.com/MarcosBL);
- Translatable "Add" button in table field view;

## [3.2.3] - 2017-02-14

### Fixed
- Spatie/Translatable fake columns had some slashed added to the json - fixes #442;


## [3.2.2] - 2017-02-13

### Fixed
- CrudTrait::getCastedAttributes();



## [3.2.1] - 2017-02-13

### Fixed
- removed a few PHP7 methods, so that PHP 5.6.x is still supported;


## [3.2.0] - 2017-02-13

### Added
- form save button better UI&UX: they have the options in a dropdown instead of radio buttons and the default behaviour is stored in the session upon change - thanks to [Owen Melbourne](https://github.com/OwenMelbz);
- redirect_after_save button actions;
- filters on list views (deleted the 3.1.41 and 4.1.42 tags because they were breaking changes);
- routes are now abstracted intro CrudRoute, so that new routes can be easily added;
- Greek translation (thanks [Stamatis Katsaounis](https://github.com/skatsaounis));
- tabbed create&update forms - thanks to [Owen Melbourne](https://github.com/OwenMelbz);
- grouped and inline errors - thanks to [Owen Melbourne](https://github.com/OwenMelbz);
- developers can now choose custom views per CRUD panel - thanks to [Owen Melbourne](https://github.com/OwenMelbz);
- select2_ajax and select2_ajax_multiple field types - thanks to [maesklaas](https://github.com/maesklaas);

### Fixed
- excluded _method from massAssignment, so create/update errors will be more useful;


## [3.1.60] - 2017-02-13

### Fixed
- select2_ajax and select2_ajax_multiple field types have been renamed to select2_from_ajax and select2_from_ajax_multiple for field naming consistency;


## [3.1.59] - 2017-02-13

### Added
- date_range field, thanks to [Owen Melbourne](https://github.com/OwenMelbz);
- select2_ajax and select2_ajax_multiple field types - thanks to [maesklaas](https://github.com/maesklaas);

### Fixed
- change the way the CrudPanel class is injected, so it can be overwritten more easily;
- simpleMDE field type - full screen fixed;


## [3.1.58] - 2017-02-10

### Added
- Bulgarian translation, thanks to [Petyo Tsonev](https://github.com/petyots);
- select2_from_array, thanks to [Nick Barrett](https://github.com/njbarrett);

### Fixed
- DateTime Picker error when date deleted after being set - fixes #386;
- Abstracted primary key in select_multiple column - fixes #377 and #412;
- AutoSet methods now using the connection on the model, instead of the default connection; This should allow for CRUDs from multiple databases inside one app; Big thanks to [Hamid Alaei Varnosfaderani](https://github.com/halaei) for this PR;
- Check that the Fake field is included in the request before trying to use it;


## [3.1.57] - 2017-02-03

### Added
- Laravel 5.4 compatibility;

### Fixed
- elfinder redirected to /login instead of /admin, because it used the "auth" middleware instead of "admin";


## [3.1.56] - 2017-02-03

### Fixed
- deleting a CRUD entry showed a warning;


## [3.1.55] - 2017-02-02

### Fixed
- allow custom primary key in field types base64_image and checklist_dependency;
- dropdown filter triggered separator on 0 index;
- make sure model events are triggered when deleting;
- in edit view, use the fields variable passed to the view;
- fix conflict bootstrap-datepicker & jquery-ui;
- fix "undefined index: disk" in upload field type;

## [3.1.54] - 2017-01-19

### Fixed
- revisions;


## [3.1.53] - 2017-01-20

### Fixed
- Revisions: $this->update() removed many to many relations;


## [3.1.52] - 2017-01-18

### Fixed
- revisions are sorted by key, not by date, since they keys are auto-incremented anyway; this should allow for multidimensional arrays;


## [3.1.51] - 2017-01-11

### Fixed
- revisions work when there are hidden (fake) fields present;
- the table in list view is responsive (scrollable horizontally) by default;
- new syntax for details_row URL in javascript;
- new syntax for the current URL in layout.blade.php, for making the current menu items active;

## [3.1.50] - 2017-01-08

### Added
- Chinese (Traditional) translation, thanks to [Isaac Kwan](https://github.com/isaackwan);
- You can now create a CRUD field to overwrite the primary key, thanks to [Isaac Kwan](https://github.com/isaackwan);

### Fixed
- Escaped table name for ENUM column types, so reserved PHP/MySQL names can also be used for table names; Fixes #261;
- CrudTrait's isColumnNullable() should now work for multiple-database systems, by getting the connection type automatically;
- Can use DB prefixed tables in CrudTrait's isColumnNullable(); fixes #300;
- Radio field type could not be used inside Settings; Now it can;


## [3.1.49] - 2017-01-08

### Fixed
- select_from_array field triggered an "Undefined index: value" error; fixes #312 thanks to [Chris Thompson](https://christhompsontldr.com/);


## [3.1.48] - 2016-12-14

### Fixed
- Prevent double-json-encoding on complicated field types, when using attribute casting; Fixes #259;


## [3.1.47] - 2016-12-14

### Fixed
- Don't mutate date/datetime if they are empty. It will default to now;
- select_from_array has a new option: "allows_multiple";
- syncPivot is now done before saving the main entity in Update::edit();
- added beforeColumn(), afterColumn(), beforeField() and afterField() methods to more easily reorder fields and columns - big up to [Ben Sutter](https://github.com/b8ne) for this feature;


## [3.1.46] - 2016-12-13

### Fixed
- a filter will be triggered if the variable exists, wether it's null or not;
- if the elfinder route has not been registered, it will be by the CrudServiceProvider;


## [3.1.45] - 2016-12-02

### Added
- $this->crud->with() method, which allows you to easily eager load relationships;
- auto eager loading relationships that are used in the CRUD columns;

### Fixed
- select and select_multiple columns use a considerably lower number of database queries;


## [3.1.44] - 2016-12-02

### Added
- Better ability to interact with the entity that was just saved, in EntityCrudController::create() and update() [the $this->crud->entry and $this->data['entry'] variables];


## [3.1.43] - 2016-11-29

### Fixed
- Allow mixed simple and complex column definitions (thanks [JamesGuthrie](https://github.com/JamesGuthrie));
- disable default DataTable ordering;


## [3.1.42] - 2016-11-13

### Fixed
- n-n filters prevented CRUD items from being added;


## [3.1.41] - 2016-11-11

### Added
- filters on list view;


## [3.1.40] - 2016-11-06

### Fixed
- fixed video field having an extra input on page;
- fixed hasUploadFields() check for update edit form; fixes #211;


## [3.1.39] - 2016-11-06

### Fixed
- fixed SimpleMDE which was broken by last commit; really fixes #222;


## [3.1.38] - 2016-11-04

### Fixed
- SimpleMDE field type did not allow multiple such field types in one form; fixes #222;


## [3.1.37] - 2016-11-03

### Fixed
- Boolean column type triggered error because of improper use of the trans() helper;


## [3.1.36] - 2016-10-30

### Added
- SimpleMDE field type (simple markdown editor).


## [3.1.35] - 2016-10-30

### Added
- new column type: boolean;
- new field type: color_picker;
- new field type: date_picker;
- new field type: datetime_picker;

### Fixed
- fixed default of 0 for radio field types;
- fixes #187 - can now clear old address entries;
- fixes hiding/showing buttons when the min/max are reached;
- ckeditor field type now has customizable plugins;


## [3.1.34] - 2016-10-22

### Fixed
- Config file is now published in the right folder.


## [3.1.33] - 2016-10-17

### Fixed
- all fields now have hint, default value and customizable wrapper class - thanks to [Owen Melbourne](https://github.com/OwenMelbz); modifications were made in the following fields: base64_image, checklist, checklist_dependecy, image;
- creating/updating elements works with morphable fields too; you need to define "morph" => true on the field for it to work;
- isCollumnNullable is now calculated using Doctrine, so that it works for MySQL, PosgreSQL and SQLite;


## [3.1.32] - 2016-10-17

### Added
- video field type - thanks to [Owen Melbourne](https://github.com/OwenMelbz);


## [3.1.31] - 2016-10-17

### Added
- $this->crud->removeAllButtons() and $this->crud->removeAllButtonsFromStack();


## [3.1.30] - 2016-10-17

### Fixed
- upload_multiple field did not remove the files from disk if no new files were added; solved with a hack - added a hidden input with the same name before it, so it always has a value and the mutator is always triggered;


## [3.1.29] - 2016-10-17

### Fixed
- elFinder height needed a 2px adjustment in javascript; now that's solved using css;


## [3.1.28] - 2016-10-16

### Added
- When elfinder is launched as it's own window, display full-screen;

### Fixed
- Update routes and editor links to follow the route_prefix set in config;
- elFinder iframe now has no white background and uses backpack theme;


## [3.1.27] - 2016-10-7

### Fixed
- 'table' field is properly encapsulated now;


## [3.1.26] - 2016-09-30

### Fixed
- bug fix for 'table' field type - you can now have multiple fields on the same form;


## [3.1.25] - 2016-09-28

### Fixed
- table field JSON bug;


## [3.1.24] - 2016-09-27

### Added
- address field type - thanks to [Owen Melbourne](https://github.com/OwenMelbz);


## [3.1.23] - 2016-09-27

### Added
- autoFocus() and autoFocusOnFirstField() - thanks to [Owen Melbourne](https://github.com/OwenMelbz);


## [3.1.22] - 2016-09-27

### Fixed
- checklist and checklist_dependency fields allow html on labels;


## [3.1.21] - 2016-09-26

### Added
- "table" field type - thanks to [Owen Melbourne](https://github.com/OwenMelbz);
- "multidimensional_array" column type - thanks to [Owen Melbourne](https://github.com/OwenMelbz);


## [3.1.20] - 2016-09-26

### Added
- Non-core CRUD features are now separated into traits;

### Fixed
- The 'password' field is no longer filtered before the create event;
- CrudPanels can now be defined in the new EntityCrudController::setup() method;

## [3.1.19] - 2016-09-26

### Fixed
- AJAX datatables can now have select_multiple columns;


## [3.1.18] - 2016-09-25

### Fixed
- checkbox field has default value;



## [3.1.17] - 2016-09-25

### Fixed
- Raw DB queries did not account for DB prefixes;


## [3.1.16] - 2016-09-22

### Added
- Radio field and column - thanks to [Owen Melbourne](https://github.com/OwenMelbz);


## [3.1.15] - 2016-09-21

### Fixed
- Missing $fillable item in model will now throw correct error, because _token is ignored;
- Correct and complete language files;


## [3.1.14] - 2016-09-19

### Fixed
- Checkbox storing issue in Laravel 5.3 - #115 thanks to [timdiels1](https://github.com/timdiels1);


## [3.1.13] - 2016-09-19

### Added
- Revisions functionality, thanks to [se1exin](https://github.com/se1exin);


## [3.1.12] - 2016-09-19

### Added
- French translation, thanks to [7ute](https://github.com/7ute);


## [3.1.11] - 2016-09-19

### Added
- iconpicker field type;


## [3.1.10] - 2016-09-16

### Fixed
- removeButton and removeButtonFromStack functionality, thanks to [Alexander N](https://github.com/morfin60);


## [3.1.9] - 2016-09-16

### Added
- "prefix" and "suffix" optional attributes on the number and text field types;


## [3.1.8] - 2016-09-15

### Fixed
- upload and upload_multiple can be used for S3 file storage too, by specifying the disk on the field;


## [3.1.7] - 2016-09-15

### Added
- image field type - stores a base64 image from the front-end into a jpg/png file using Intervention/Image;


## [3.1.6] - 2016-09-15

### Added
- upload_multiple field type;


## [3.1.5] - 2016-09-14

### Added
- upload field type;

### Fixed
- setFromDb() no longer creates a field for created_at;


## [3.1.4] - 2016-09-12

### Added
- Export buttons for CRUDs - to PDF, XLS, CSV and Print, thanks to [Nathaniel Kristofer Schweinberg](https://github.com/nathanielks);


## [3.1.3] - 2016-09-12

### Added
- a "view" field type, which loads a custom view from a specified location; thanks to [Nathaniel Kristofer Schweinberg](https://github.com/nathanielks);


## [3.1.2] - 2016-09-12

### Fixed
- save, update and reorder now replace empty inputs with NULL to allow for MySQL strict mode on (a default in Laravel 5.3) (#94)


## [3.1.1] - 2016-09-05

### Added
- Allow HTML in all field labels (#98)


## [3.1.0] - 2016-08-31

### Added
- Laravel 5.3 support;


## [3.0.17] - 2016-08-26

### Fixed
- adding buttons from views did not work; fixes #93;


## [3.0.16] - 2016-08-24

### Fixed
- Removed recurring comment from list view; Fixes #92;
- Added check for permission in the CrudController::search() method for allowing the AJAX table only if list is enabled;


## [3.0.15] - 2016-08-20

### Fixed
- Removed double-token input in Create view; Fixes #89;


## [3.0.14] - 2016-08-20

### Fixed
- Fixed AJAX table view with big data sets - was still selecting all rows from the DB; Fixes #87;


## [3.0.13] - 2016-08-17

### Fixed
- Custom pivot table in select2 and select2_multiple fields; Fixes #75;


## [3.0.12] - 2016-08-17

### Fixed
- Reorder view works with custom primary keys; fixes #85;
- URLs in views now use the backpack.base.route_prefix; fixes #88;


## [3.0.11] - 2016-08-12

### Added
- Spanish translation, thanks to [Rafael Ernesto Ferro González](https://github.com/rafix);


## [3.0.10] - 2016-08-09

### Removed
- PHP dependency, since it's already settled in Backpack\Base, which is a requirement;


## [3.0.9] - 2016-08-06

### Added
- base64_image field type, thanks to [deslittle](https://github.com/deslittle);


## [3.0.8] - 2016-08-05

### Added
- automatic route names for all CRUD::resource() routes;


## [3.0.7] - 2016-08-05

### Added
- PDO Support;

### Removed
- default column values on the setFromDb() function;


## [3.0.6] - 2016-07-31

### Added
- Bogus unit tests. At least we'be able to use travis-ci for requirements errors, until full unit tests are done.


## [3.0.5] - 2016-07-30

### Added
- Auto-registering the Backpack\Base class;
- Improved documentation for those who want to just install Backpack\CRUD;


## [3.0.4] - 2016-07-30

### Added
- Auto-registering the Backpack\Base class;
- Improved documentation for those who want to just install Backpack\CRUD;


## [3.0.3] - 2016-07-25

### Added
- Ctrl+S and Cmd+S submit the form;


## [3.0.2] - 2016-07-24

### Added
- added last parameter to addButton() function which determines wether to add the button to the beginning or end of the stack;


## [3.0.1] - 2016-07-23

### Added
- 'array' column type (stored as JSON in the db); also supports attribute casting;
- support for attribute casting in Date and Datetime field types;


## [3.0.0] - 2016-07-22

### Added
- wrapperAttributes to all field types, for resizing with col-md-6 and such;
- 'default' value for most field types;
- hint to most field types;
- extendable column types (same as field types, each in their own blade file);
- 'date' and 'datetime' column types;
- 'check' column type;
- button stacks;
- custom buttons, as views or model_function;
- registered service providers in order to simplify installation process;
- configurable number of rows in the table view, by giving a custom value in the config file or in the CRUD panel's constructor;

### Removed
- "required" functionality with just added asterisks to the fields;

### Fixed
- renamed the $field_types property to $db_column_types to more accurately describe what it is;
- issue #58 where select_from_array automatically selected an item with value zero;
- custom html attributes are now given to the field in a separate array, 'attributes';

-----------

# Backpack Version 2

-----------

## [2.0.24] - 2016-07-13

### Added
- model_function_attribute column type (kudos to [rgreer4](https://github.com/rgreer4))


## [2.0.23] - 2016-07-13

### Added
- Support for $primaryKey variable on the model (no longer dependant on ID as primary key).


## [2.0.22] - 2016-06-27

### Fixed
- Fix removeField method
- Improve autoSetFromDB method


## [2.0.21] - 2016-06-21

### Fixed
- Old input value on text fields in the create form;
- "Please fix" lang text.


## [2.0.20] - 2016-06-19

### Fixed
- Translate browse and page_or_link fields


## [2.0.19] - 2016-06-16

### Fixed
- Split the Crud.php class into multiple traits, for legibility;
- Renamed the Crud.php class to CrudPanel;


## [2.0.18] - 2016-06-16

### Removed
- Tone's old field types (were only here for reference);
- Tone's old layouts (were only here for reference);


## [2.0.17] - 2016-06-16

### Added
- $crud->hasAccessToAny($array) method;
- $crud->hasAccessToAll($array) method;


## [2.0.16] - 2016-06-15

### Fixed
- CrudController - use passed request before fallback to global one;


## [2.0.15] - 2016-06-14

### Fixed
- select_multiple worked, select2_multiple did not; #26


## [2.0.14] - 2016-06-13

### Fixed
- Allow HTML in fields help block;


## [2.0.13] - 2016-06-09

### Added
- Italian translation;
- Browse field parameter to disable readonly state;


## [2.0.12] - 2016-06-06

### Fixed
- multiple browse fields on one form did not work;


## [2.0.11] - 2016-06-06

### Fixed
- multiple browse fields on one form did not work;


## [2.0.10] - 2016-06-06

### Fixed
- browse field did not work if Laravel was installed in a subfolder;
- browse field Clear button did not clear the input;
- select_from_array field did not work;
- Crud::setFromDb() now defaults to NULL instead of empty string;


## [2.0.9] - 2016-05-27

### Deprecated
- Route::controller() - it's been deprecated in Laravel 5.2, so we can't use it anymore;


## [2.0.8] - 2016-05-26

### Added
- page_or_link field type now has a 'page_model' attribute in its definition;


## [2.0.7] - 2016-05-25

### Added
- Text columns can now be added with a string $this->crud->addColumn('title');
- Added hint to the 'text' field type;
- Added the 'custom_html' field type;


## [2.0.6] - 2016-05-25

### Fixed
- Elfinder triggered an error on file upload, though uploads were being done fine.


## [2.0.5] - 2016-05-20

### Fixed
- Removing columns was fixed.


## [2.0.4] - 2016-05-20

### Fixed
- Fields with subfields did not work any more (mainly checklist_dependency);


## [2.0.3] - 2016-05-20

### Fixed
- Easier CRUD Field definition - complex fields no longer need a separate .js and .css files; the extra css and js for a field will be defined in the same file, and then pushed to a stack in the form_content.blade.php view, which will put in the proper after_styles or after_scripts section. By default, the styles and scripts will be pushed to the page only once per field type (no need to have select2.js five times onpage if we have 5 select2 inputs)
- Changed existing complex fields (with JS and CSS) to this new definition.


## [2.0.2] - 2016-05-20

### Added
- Working CRUD API functions for adding fields and removing fields.
- Removed deprecated file: ToneCrud.php


## [2.0.1] - 2016-05-19

### Fixed
- Crud.php fixes found out during Backpack\PermissionManager development.
- Added developers to readme file.


## [2.0.0] - 2016-05-18

### Added
- Call-based API.


## ----------------------------------------------------------------------------


## [0.9.10] - 2016-03-17

### Fixed
- Fixed some scrutinizer bugs.


## [0.9.9] - 2016-03-16

### Added
- Added page title.


## [0.9.8] - 2016-03-14

### Added
- Added a custom theme for elfinder, called elfinder.backpack.theme, that gets published with the CRUD public files.


## [0.9.7] - 2016-03-12

### Fixed
- Using LangFileManager for translatable models instead of Dick's old package.


## [0.9.6] - 2016-03-12

### Fixed
- Lang files are pushed in the correct folder now. For realsies.


## [0.9.5] - 2016-03-12

### Fixed
- language files are published in the correct folder, no /vendor/ subfolder


## [0.9.4] - 2016-03-11

### Added
- CRUD::resource() now also acts as an implicit controller too.

### Removed
- firstViewThatExists() method in CrudController - its functionality is already solved by the view() helper, since we now load the views in the correct order in CrudServiceProvider



## [0.9.3] - 2016-03-11

### Fixed
- elFinder erro "Undefined variable: file" is fixed with a composer update. Just make sure you have studio-42/elfinder version 2.1.9 or higher.
- Added authentication middleware to elFinder config.


## [0.9.2] - 2016-03-10

### Fixed
- Fixed ckeditor field type.
- Added menu item instructions in readme.


## [0.9.1] - 2016-03-10

### Fixed
- Changed folder structure (Http is in app folder now).


## [0.9.0] - 2016-03-10

### Fixed
- Changed name from Dick/CRUD to Backpack/CRUD.

### Removed
- Entrust permissions.


## [0.8.17] - 2016-02-23

### Fixed
- two or more select2 or select2_multiple fields in the same form loads the appropriate .js file two times, so error. this fixes it.


## [0.8.13] - 2015-10-07

### Fixed
- CRUD list view bug fixed thanks to Bradis García Labaceno. The DELETE button didn't work for subsequent results pages, now it does.


## [0.8.12] - 2015-10-02

### Fixed
- CrudRequest used classes from the 'App' namespace, which rendered errors when the application namespace had been renamed by the developer;


## [0.8.11] - 2015-10-02

### Fixed
- CrudController used classes from the 'App' namespace, which rendered errors when the application namespace had been renamed by the developer;


## [0.8.9] - 2015-09-22

### Added
- added new column type: "model_function", that runs a certain function on the CRUD model;


## [0.8.8] - 2015-09-17

### Fixed
- bumped version;


## [0.8.7] - 2015-09-17

### Fixed
- update_fields and create_fields were being ignored because of the fake fields; now they're taken into consideration again, to allow different fields on the add/edit forms;

## [0.8.6] - 2015-09-11

### Fixed
- DateTime field type needed some magic to properly use the default value as stored in MySQL.

## [0.8.5] - 2015-09-11

### Fixed
- Fixed bug where reordering multi-language items didn't work through AJAX (route wasn't defined);


## [0.8.4] - 2015-09-10

### Added
- allTranslations() method on CrudTrait, to easily get all connected entities;


## [0.8.3] - 2015-09-10

### Added
- withFakes() method on CrudTrait, to easily get entities with fakes fields;

## [0.8.1] - 2015-09-09

### Added
- CRUD Alias for handling the routes. Now instead of defining a Route::resource() and a bunch of other routes if you need reorder/translations etc, you only define CRUD:resource() instead (same syntax) and the CrudServiceProvider will define all the routes you need. That, of course, if you define 'CRUD' => 'Dick\CRUD\CrudServiceProvider' in your config/app.php file, under 'aliases'.


## [0.8.0] - 2015-09-09

### Added
- CRUD Multi-language editing. If the EntityCrudController's "details_row" is set to true, by default the CRUD will output the translations for that entity's row. Tested and working add, edit, delete and reordering both for original rows and for translation rows.


## [0.7.9] - 2015-09-09

### Added
- CRUD Details Row functionality: if enabled, it will show a + sign for each row. When clicked, an AJAX call will return the showDetailsRow() method on the controller and place it in a row right below the current one; Currently that method just dumps the entry; But hey, it works.


## [0.7.8] - 2015-09-08

### Fixed
- In CRUD reordering, the leaf ID was outputted for debuging.


## [0.7.7] - 2015-09-08

### Added
- New field type: page_or_link; It's used in the MenuManager package, but can be used in any other model;


## [0.7.4] - 2015-09-08

### Added
- Actually started using CHANGELOG.md to track modifications.

### Fixed
- Reordering echo algorithm. It now takes account of leaf order.

