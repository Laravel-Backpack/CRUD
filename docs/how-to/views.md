## Views

### How to customize views for each CRUD panel

Backpack loads its views through a double-fallback mechanism:
- by default, it will load the views in the vendor folder (the package views);
- if you've included views with the exact same name in your ```resources/views/vendor/backpack/*``` folder, it will pick up those instead; you can use this method to overwrite a blade file for the whole application.
- alternatively, if you only want to change a blade file for one CRUD, you can use the methods below in your ```setup()``` method, to change a particular view:
```php
CRUD::setShowView('your-view');
CRUD::setEditView('your-view');
CRUD::setCreateView('your-view');
CRUD::setListView('your-view');
CRUD::setReorderView('your-view');
CRUD::setDetailsRowView('your-view');
```

### How to add CSS or JS to a page or operation

If you want to add extra CSS or JS to a certain page, use the `script` and `style` widgets to add a new file of that type onpage, either from your CrudController or a custom blade file:

```php
use Backpack\CRUD\app\Library\Widget;

// script widget - works the same for both local paths and CDN
Widget::add()->type('script')->content('assets/js/custom-script.js');

Widget::add()->type('script')->content('https://code.jquery.com/ui/1.12.0/jquery-ui.min.js');

Widget::add()->type('script')
             ->content('https://code.jquery.com/ui/1.12.0/jquery-ui.min.js')
             ->integrity('sha256-0YPKAwZP7Mp3ALMRVB2i8GXeEndvCq3eSl/WsAl1Ryk=')
             ->crossorigin('anonymous');

// style widget - works the same for both local paths and CDN
Widget::add()->type('style')->content('assets/css/custom-style.css');

Widget::add()->type('style')->content('https://cdn.jsdelivr.net/npm/@shoelace-style/shoelace@2.0.0-beta.58/dist/themes/light.css');

Widget::add()->type('style')
             ->content('https://cdn.jsdelivr.net/npm/@shoelace-style/shoelace@2.0.0-beta.58/dist/themes/light.css')
             ->integrity('sha256-0YPKAwZP7Mp3ALMRVB2i8GXeEndvCq3eSl/WsAl1Ryk=')
             ->crossorigin('anonymous');
```

For more details please see the `script` and `style` sections in the [Widgets](/docs/{{version}}/base-widgets) page.

You can limit where that CSS/JS is added by making the `Widget::add()` call in the right place in your CrudController:
- if you do `Widget::add()` inside the `setupListOperation()` method, it will only be loaded there;
- if you do `Widget::add()` inside the `setup()` method, it will be loaded on all pages for that CRUD;
- if you want it to be loaded on all pages for all CRUDs, you can create a CustomCrudController that extends our CrudController, do it there and then make sure all your CRUDs extend `CustomCrudController`;
- if you want it to be loaded on all pages (even non-CRUD like dashboards) you can add the CSS/JS file on all pages by adding it in your `config/backpack/base.php`, under `scripts` and `styles`;
