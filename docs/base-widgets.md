# Widgets

---

<a name="about"></a>
## About

Widgets (aka cards, aka charts, aka graphs) provide a simple way to insert blade files into admin panel pages. You can use them to insert cards, charts, notices or custom content into pages.


<a name="requirements"></a>
### Requirements

In order to use the ```Widget``` class, you should make sure your main views (for new admin panel pages) extend the ```backpack::blank``` or ```backpack_view('blank')``` blade template. This template includes two sections where you can push widgets:
- ```before_content```
- ```after_content```


<a name="how-to-use-widgets"></a>
### How to Use

You can easily push widgets to these sections, by using the autoloaded ```Widget``` class. You can think of the ```Widget``` class as a global container for widgets, for the current page being rendered. That means you can call the ```Widget``` container inside a ```Controller```, inside a ```view```, or inside a service provider you create - wherever you want.

```php
use Backpack\CRUD\app\Library\Widget;

Widget::add($widget_definition_array)->to('before_content');

// alternatively, use a fluent syntax to define each widget attribute
Widget::add()
    ->to('before_content')
    ->type('card')
    ->content(null);
```


<a name="mandatory-attributes"></a>
### Mandatory Attributes

When passing a widget array, you need to specify at least these attributes:
```php
[
   'type' => 'card' // the kind of widget to show
   'content' => null // the content of that widget (some are string, some are array)
],
```

<a name="optional-attributes"></a>
### Optional Attributes

Most widget types also have these attributes present, which you can use to tweak how the widget looks inside the page:
```php
'wrapper' => [
    'class' => 'col-sm-6 col-md-4', // customize the class on the parent element (wrapper)
    'style' => 'border-radius: 10px;',
]
```

<a name="widgets-api"></a>
### Widgets API

To manipulate widgets, you can use the methods below. The action will be performed on the page being constructed for the current request. And the ```Widget``` class is a global container, so you can add widgets to it both from the Controller, and from the view.

```php
// to add a widget to a different section than the default 'before_content' section:
Widget::add($widget_definition_array)->to('after_content');
Widget::add($widget_definition_array)->section('after_content');
Widget::add($widget_definition_array)->group('after_content');

// to create a widget, WITHOUT adding it to a section
Widget::make($widget_definition_array);

// to define the contents of a widget, pass the definition array to the make()/add() methods
Widget::add($widget_definition_array);
Widget::make($widget_definition_array);
// alternatively, define each widget attribute one by one, using a fluent syntax
Widget::add()
    ->to('after_content')
    ->type('card')
    ->content('something');

// to reference a widget later on, give it a unique 'name'
Widget::add($widget_definition_array)->name('my_widget');

// you can then easily modify it
Widget::name('my_widget')->content('some other content'); // change the 'content' attribute
Widget::name('my_widget')->forget('attribute_name'); // unset a widget attribute
Widget::name('my_widget')->makeFirst(); // make a widget the first one in its section
Widget::name('my_widget')->makeLast(); // to make a widget the last one in its section
Widget::name('my_widget')->remove(); // remove the widget from its section
```



<a name="widget-types"></a>
## Default Widget Types

<a name="alert"></a>
### Alert

Shows a notification bubble, with the heading and text you specify:

```php
[
    'type'         => 'alert',
    'class'        => 'alert alert-danger mb-2',
    'heading'      => 'Important information!',
    'content'      => 'Lorem ipsum dolor sit amet, consectetur adipisicing elit. Corrupti nulla quas distinctio veritatis provident mollitia error fuga quis repellat, modi minima corporis similique, quaerat minus rerum dolorem asperiores, odit magnam.',
    'close_button' => true, // show close button or not
]
```

For different colors, you can use the following classes: ```alert-success```, ```alert-warning```, ```alert-info```, ```alert-danger``` ```alert-primary```, ```alert-secondary```, ```alert-light```, ```alert-dark```.

Widget Preview:

![Backpack alert widget](https://backpackforlaravel.com/uploads/v4/widgets/alert.png)

<hr>

<a name="card"></a>
### Card

Shows a Bootstrap card, with the heading and body you specify. You can customize the class name to get cards of different color, size, etc.

```php
[
    'type'       => 'card',
    // 'wrapper' => ['class' => 'col-sm-6 col-md-4'], // optional
    // 'class'   => 'card bg-dark text-white', // optional
    'content'    => [
        'header' => 'Some card title', // optional
        'body'   => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Duis non mi nec orci euismod venenatis. Integer quis sapien et diam facilisis facilisis ultricies quis justo. Phasellus sem <b>turpis</b>, ornare quis aliquet ut, volutpat et lectus. Aliquam a egestas elit. <i>Nulla posuere</i>, sem et porttitor mollis, massa nibh sagittis nibh, id porttitor nibh turpis sed arcu.',
    ]
]
```

For different background colors, you can use the following classes: ```bg-success```, ```bg-warning```, ```bg-info```, ```bg-danger``` ```bg-primary```, ```bg-secondary```, ```bg-light```, ```bg-dark```.

Other useful helper classes: ```text-center```, ```text-white```.

Widget Preview:

![Backpack card widget](https://backpackforlaravel.com/uploads/v4/widgets/card.png)

<hr>

<a name="chart"></a>
### Chart <span class="badge badge-pill badge-info">PRO</span>

Shows a pie chart / line chart / bar chart inside a Bootstrap card, with the heading and body you specify.

![Backpack chart widgets](https://backpackforlaravel.com/uploads/docs-4-2/release_notes/chart_widget_small.gif)

To create and use a new widget chart, you need to:

**Step 1.** Install laravel-charts, that offers a single PHP syntax for 6 different charting libraries:
```
composer require consoletvs/charts:"6.*"
```

**Step 2.** Create a new ChartController:

```
php artisan backpack:chart WeeklyUsers

```

This will create:
- a new ChartController inside ```app\Http\Controllers\Admin\Charts\WeeklyUsersChartController```
- a route towards that ChartController in your ```routes/backpack/custom.php```

**Step 3.** Add the widget that points to that ChartController you just created:
```php
Widget::add([
    'type'       => 'chart',
    'controller' => \App\Http\Controllers\Admin\Charts\WeeklyUsersChartController::class,

    // OPTIONALS

    // 'class'   => 'card mb-2',
    // 'wrapper' => ['class'=> 'col-md-6'] ,
    // 'content' => [
         // 'header' => 'New Users',
         // 'body'   => 'This chart should make it obvious how many new users have signed up in the past 7 days.<br><br>',
    // ],
]);
```

**Step 4.** Configure the ChartController you just created:
- ```public function setup()``` (MANDATORY)
    - initialize and configure ```$this->chart```, using the methods detailed in the [laravel-charts documentation](https://charts.erik.cat/getting_started.html);
    - you _can_ define your dataset here, if you want your DB queries to be called upon page load;
- ```public function data()``` (OPTIONAL, but recommended)
    - use ```$this->chart->dataset()``` to configure what the chart should contain;
    - if it's defined, the chart will loads its contents using AJAX;

Optionally:
- you can _easily_ switch the JavaScript library used, by changing the use statement at the top of this file:

```diff
-use ConsoleTVs\Charts\Classes\Chartjs\Chart;
+use ConsoleTVs\Charts\Classes\Echarts\Chart;
+use ConsoleTVs\Charts\Classes\Fusioncharts\Chart;
+use ConsoleTVs\Charts\Classes\Highcharts\Chart;
+use ConsoleTVs\Charts\Classes\C3\Chart;
+use ConsoleTVs\Charts\Classes\Frappe\Chart;
```
- you can change the path to the JS library; if you don't want it loaded from a CDN, you can define ```$library``` or ```getLibraryFilePath()``` on your ChartController:

```php
protected $library = 'http://path/to/file';

// or

public function getLibraryFilePath()
{
    return asset('path/to/your/js/file');

    // or

    return [
        asset('path/to/first/js/file'),
        asset('path/to/second/js/file'),
    ];
}
```



**That's it!** Refresh the page to see your new chart widget. Below, you'll find a few examples of how the ChartController can end up looking.

<hr>

**Example 1:** ChartController that loads results using AJAX:
```php
<?php

namespace App\Http\Controllers\Admin\Charts;

use App\User;
use Backpack\CRUD\app\Http\Controllers\ChartController;
use Backpack\NewsCRUD\app\Models\Article;
use Backpack\NewsCRUD\app\Models\Category;
use Backpack\NewsCRUD\app\Models\Tag;
use ConsoleTVs\Charts\Classes\Chartjs\Chart;

class NewEntriesChartController extends ChartController
{
    public function setup()
    {
        $this->chart = new Chart();

        // MANDATORY. Set the labels for the dataset points
        $labels = [];
        for ($days_backwards = 30; $days_backwards >= 0; $days_backwards--) {
            if ($days_backwards == 1) {
            }
            $labels[] = $days_backwards.' days ago';
        }
        $this->chart->labels($labels);

        // RECOMMENDED.
        // Set URL that the ChartJS library should call, to get its data using AJAX.
        $this->chart->load(backpack_url('charts/new-entries'));

        // OPTIONAL.
        $this->chart->minimalist(false);
        $this->chart->displayLegend(true);
    }

    /**
     * Respond to AJAX calls with all the chart data points.
     *
     * @return json
     */
    public function data()
    {
        for ($days_backwards = 30; $days_backwards >= 0; $days_backwards--) {
            // Could also be an array_push if using an array rather than a collection.
            $users[] = User::whereDate('created_at', today()
                ->subDays($days_backwards))
                ->count();
            $articles[] = Article::whereDate('created_at', today()
                ->subDays($days_backwards))
                ->count();
            $categories[] = Category::whereDate('created_at', today()
                ->subDays($days_backwards))
                ->count();
            $tags[] = Tag::whereDate('created_at', today()
                ->subDays($days_backwards))
                ->count();
        }

        $this->chart->dataset('Users', 'line', $users)
            ->color('rgb(77, 189, 116)')
            ->backgroundColor('rgba(77, 189, 116, 0.4)');

        $this->chart->dataset('Articles', 'line', $articles)
            ->color('rgb(96, 92, 168)')
            ->backgroundColor('rgba(96, 92, 168, 0.4)');

        $this->chart->dataset('Categories', 'line', $categories)
            ->color('rgb(255, 193, 7)')
            ->backgroundColor('rgba(255, 193, 7, 0.4)');

        $this->chart->dataset('Tags', 'line', $tags)
            ->color('rgba(70, 127, 208, 1)')
            ->backgroundColor('rgba(70, 127, 208, 0.4)');
    }
}

```

**Example 2.** Pie chart with both labels and dataset defined in the ```setup()``` method (no AJAX):

```php
<?php

namespace App\Http\Controllers\Admin\Charts\Pies;

use Backpack\CRUD\app\Http\Controllers\ChartController;
use ConsoleTVs\Charts\Classes\Chartjs\Chart;

class ChartjsPieController extends ChartController
{
    public function setup()
    {
        $this->chart = new Chart();

        $this->chart->dataset('Red', 'pie', [10, 20, 80, 30])
                    ->backgroundColor([
                        'rgb(70, 127, 208)',
                        'rgb(77, 189, 116)',
                        'rgb(96, 92, 168)',
                        'rgb(255, 193, 7)',
                    ]);

        // OPTIONAL
        $this->chart->displayAxes(false);
        $this->chart->displayLegend(true);

        // MANDATORY. Set the labels for the dataset points
        $this->chart->labels(['HTML', 'CSS', 'PHP', 'JS']);
    }
}

```

<a name="dataform"></a>
### Dataform

Shows a dataform component from a particular CrudController. For more info about the configuration parameter, please see the [dataform component docs](/docs/{{version}}/base-components#dataform).

```php
[
    'type' => 'dataform',
    'controller' => 'App\Http\Controllers\Admin\InvoiceCrudController',
    'name' => 'invoice_form',
    'setup' => function($crud, $parent) {
        // you can use this closure to modify your CrudController definition.
        $crud->removeField('notes');
    }
]
```

<hr>

<a name="chip"></a>
### Chip

Shows a chip blade view - which is useful to show more information about a database entry, using little screen real estate.

```php
[
    'type' => 'chip',
    'view' => 'crud::chips.general',
    'title' => 'invoices',
    'entry' => Invoice::first(),
]
```


<hr>

<a name="datatable"></a>
### Datatable

Shows a datatable component from a particular CrudController. For more info about the configuration parameter, please see the [datatable component docs](/docs/{{version}}/base-components#datatable).

```php
[
    'type' => 'datatable',
    'controller' => 'App\Http\Controllers\Admin\PetShop\InvoiceCrudController',
    'name' => 'invoices',
    'setup' => function($crud, $parent) {
        // you can use this closure to modify your CrudController definition.
        if ($parent) {
            $crud->addClause('where', 'owner_id', $parent->id);
        }
    }
]
```


<hr>

<a name="div"></a>
### Div

Allows you to include multiple widgets within a "div" element with the attributes of your choice. For example, you can include multiple widgets within a ```<div class="row"></div>``` with the code below:

```php
[
    'type'    => 'div',
    'class'   => 'row',
    'content' => [ // widgets
        [ 'type' => 'card', 'content' => ['body' => 'One'] ],
        [ 'type' => 'card', 'content' => ['body' => 'Two'] ],
        [ 'type' => 'card', 'content' => ['body' => 'Three'] ],
    ]
]
```

Anything you specify on this widget, other than ```type``` and ```content```, has to be a string, and will be considered an attribute of the "div" element.
For example, in the following snippet, ```class``` and ```custom-attribute``` are attributes of the "div" element:

```php
[
    'type'    => 'div',
    'class'   => 'row my-custom-widget-class',
    'custom-attribute'   => 'my-custom-value',
    'content' => [ // widgets
        [ 'type' => 'card', 'content' => ['body' => 'One'] ],
        [ 'type' => 'card', 'content' => ['body' => 'Two'] ],
        [ 'type' => 'card', 'content' => ['body' => 'Three'] ],
    ]
]
```

and the generated output will be:

```html
    <div custom-attribute="my-custom-value" class="row my-custom-widget-class">
        // The HTML code of the three card widgets will be here
    </div>
```

<hr>

<a name="jumbotron"></a>
### Jumbotron

Shows a Bootstrap jumbotron component, with the heading and body you specify.

```php
[
    'type'        => 'jumbotron',
    'heading'     => 'Welcome!',
    'content'     => 'My magnific headline! Lets build something awesome together.',
    'button_link' => backpack_url('logout'),
    'button_text' => 'Logout',
    // OPTIONAL:
    'heading_class' => 'display-3 text-white',
    'content_class' => 'text-white',
]
```

Widget Preview:

![Backpack card widget](https://backpackforlaravel.com/uploads/v4/widgets/jumbotron.png)

<hr>

<a name="livewire"></a>
### Livewire

Add a Livewire component to a page. If you haven't created your component yet, head to [Livewire documentation](https://livewire.laravel.com/docs/components) and create the component you want to use.

**Note Livewire v2**: Livewire v2 does not automatically inject the `@livewireScripts` and `@livewireStyles` tags. If you **are NOT using** Livewire outside of this widget you can load them here by setting `livewireAssets => true`

```php
[
    'type'        => 'livewire',
    'content'   => 'my-livewire-component', // the component name
    'parameters'      => ['user' => backpack_user(), 'param2' => 'value2'], // optional: pass parameters to the component
    'livewireAssets' => false, // optional: set true to load livewire assets in the widget
]
```

**Note:** The ```parameters``` attribute will be passed to the component on initialization, and should be present in the `mount($user, $param2)`.

##### HelloWord Example:

```php
use Livewire\Component;

class HelloWorld extends Component
{
    public $name;

    public function mount(string $name)
    {
        $this->name = $name;
    }

    public function render()
    {
        return view('livewire.hello-world');
    }
}
```

```blade
<!-- livewire/hello-world.blade.php -->
<div>
    Hello {{ $name }}
</div>
```

```php
// add the widget to the page
Widget::add()->type('livewire')->content('hello-world')->parameters(['name' => 'John Doe'])->wrapperClass('col-md-12 text-center');
```

Widget Preview:

![Backpack Livewire Widget](https://github.com/Laravel-Backpack/CRUD/assets/7188159/e0aca2cb-f471-43c7-82e6-014704d576f3)

<hr>

<a name="progress"></a>
### Progress

Shows a colorful card to signify the progress towards a goal. You can customize the class name to get cards of different color, size, etc.

```php
[
    'type'        => 'progress',
    'class'       => 'card text-white bg-primary mb-2',
    'value'       => '11.456',
    'description' => 'Registered users.',
    'progress'    => 57, // integer
    'hint'        => '8544 more until next milestone.',
]
```

For different background colors, you can use the following classes: ```bg-success```, ```bg-warning```, ```bg-info```, ```bg-danger``` ```bg-primary```, ```bg-secondary```, ```bg-light```, ```bg-dark```.

Other useful helper classes: ```text-center```, ```text-white```.

Widget Preview:

![Backpack card widget](https://backpackforlaravel.com/uploads/v4/widgets/progress.png)

<hr>

<a name="progress-white"></a>
### Progress White

Shows a white card to signify the progress towards a goal. You can customize the class name to get progress bars of different color.

```php
[
    'type'          => 'progress_white',
    'class'         => 'card mb-2',
    'value'         => '11.456',
    'description'   => 'Registered users.',
    'progress'      => 57, // integer
    'progressClass' => 'progress-bar bg-primary',
    'hint'          => '8544 more until next milestone.',
]
```

For different progress bar colors, you can use the following classes: ```bg-success```, ```bg-warning```, ```bg-info```, ```bg-danger``` ```bg-primary```, ```bg-secondary```, ```bg-light```, ```bg-dark```.

Widget Preview:

![Backpack card widget](https://backpackforlaravel.com/uploads/v4/widgets/progress_white.png)

<hr>

<a name="script"></a>
### Script

Loads a JavaScript file from a location you specify using a `<script>` element.

```php
[
    'type'     => 'script',
    'content'  => 'assets/js/custom-script.js',
    // optional
    // 'stack'    => 'before_scripts', // default is after_scripts
]
```

You can also specify a link (`https://path-to-file.com/file.js`) as the content, and it will load the file from a CDN. In that case you might also want to add additional attributes, which you can easily do, for example:

```php
Widget::add()->type('script')
     ->content('https://code.jquery.com/ui/1.12.0/jquery-ui.min.js')
     ->integrity('sha256-0YPKAwZP7Mp3ALMRVB2i8GXeEndvCq3eSl/WsAl1Ryk=')
     ->crossorigin('anonymous');
```

<hr>

<a name="style"></a>
### Style

Loads a CSS file from a location you specify using a `<link>` element.

```php
[
    'type'     => 'style',
    'content'  => 'assets/css/custom-script.css',
    // optional
    // 'stack'    => 'before_styles', // default is after_styles
]
```

You can also specify a link (`https://path-to-file.com/file.css`) as the content, and it will load the file from a CDN. In that case you might also want to add additional attributes, which you can easily do, for example:

```php
Widget::add()->type('style')
     ->content('https://cdn.jsdelivr.net/npm/@shoelace-style/shoelace@2.0.0-beta.58/dist/themes/light.css')
     ->integrity('sha256-0YPKAwZP7Mp3ALMRVB2i8GXeEndvCq3eSl/WsAl1Ryk=')
     ->crossorigin('anonymous');
```

<hr>

<a name="view"></a>
### View

Loads a blade view from a location you specify. Any attributes you give it will be available in the ```$widget``` variable inside that view.

```php
[
    'type'     => 'view',
    'view'     => 'path.to.custom.view',
    'someAttr' => 'some value',
]
```

It helps load blade files that are not specifically created to be widgets, that live in a different path than ```resources/views/vendor/backpack/ui/widgets```, as if they were widgets.

<hr>

<a name="overwriting-default-widget-types"></a>
## Overriding Default Widget Types

You can override a widget type by placing a file with the same name in your ```resources\views\vendor\backpack\ui\widgets``` directory. When a file is there, Backpack will pick that one up, instead of the one in the package. You can do that from command line using ```php artisan backpack:widget --from=widget-name```

Examples:
- creating a ```resources\views\vendor\backpack\ui\widgets\card.blade.php``` file would overwrite the ```card``` widget functionality;
- ```php artisan backpack:widget --from=card``` will take the view from the package and copy it to the directory above, so you can edit it;

>Keep in mind that when you're overwriting a default widget type, you're forfeiting any future updates for that widget. We can't push updates to a file that you're no longer using.

<a name="creating-a-custom-widget-type"></a>
## Creating a Custom Widget Type

Widgets consist of only one file - a blade file with the same name as the widget type (ex: ```card.blade.php```). You can create one by placing a new blade file inside ```resources\views\vendor\backpack\ui\widgets```. Be careful to choose a distinctive name, otherwise you might be overwriting a default widget type (see above).

For example, you can create a ```well.blade.php```:
```php
@includeWhen(!empty($widget['wrapper']), 'backpack::widgets.inc.wrapper_start')
    <div class="{{ $widget['class'] ?? 'well mb-2' }}">
        {!! $widget['content'] !!}
    </div>
@includeWhen(!empty($widget['wrapper']), 'backpack::widgets.inc.wrapper_end')
```

You can then use the ```well``` widget in a Controller or View:
```php
@extends(backpack_view('blank'))

@php
    Widget::add([
        'type'    => 'well',
        'wrapper' => ['class' => 'col-sm-12'],
        'content' => 'This text will be in a div with the class "<i>well</i>".',
    ]);
@endphp

@section('content')
@endsection
```

To use information from the database, you can:
- use the full namespace for your models, like ```\App\Models\Product::count()```;
- load all your dashboard information using AJAX calls, if you're loading charts, reports, etc, and the DB queries might take a long time;
- [use view composers](https://laravel.com/docs/10.x/views#view-composers) to push variables inside this view when it's loaded, Like. ```View::composer('backpack::widgets.well, 'App\Http\View\Composers\WellComposer');```

Inside the widget blade files, you include custom CSS and JS, by pushing to the stacks in the layout:
```php
@includeWhen(!empty($widget['wrapper']), 'backpack::widgets.inc.wrapper_start')
    <div class="{{ $widget['class'] ?? 'well mb-2' }}">
        {!! $widget['content'] !!}
    </div>
@includeWhen(!empty($widget['wrapper']), 'backpack::widgets.inc.wrapper_end')

@push('after_styles')
    <link href="{{ asset('packages/select2/dist/css/select2.min.css') }}" rel="stylesheet" type="text/css" />
    <style>
        .some_class {
            color: red;
        }
    </style>
@endpush


@push('after_scripts')
    <script src="{{ asset('packages/select2/dist/js/select2.min.js') }}"></script>
    <script>
        jQuery(document).ready(function($) {
            // trigger select2 for each untriggered select2 box
            $('.select2_field').each(function (i, obj) {
                if (!$(obj).hasClass("select2-hidden-accessible"))
                {
                    $(obj).select2({
                        theme: "bootstrap"
                    });
                }
            });
        });
    </script>
@endpush
```


<a name="using-widget-types-from-packages"></a>
## Using a Widget Type from a Package

You can choose the view namespace when loading a widget:

```php

// using the fluent syntax, use the 'from' alias
Widget::add($widget_definition_array)->from('package::widgets');

// using the widget definition array, specify its 'viewNamespace'
Widget::add([
    'type'          => 'card',
    'viewNamespace' => 'package::widgets',
    'wrapper'       => ['class' => 'col-sm-6 col-md-4'],
    'class'         => 'card text-white bg-primary text-center',
    'content'       => [
        // 'header' => 'Another card title',
        'body'      => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Duis non mi nec orci euismod venenatis. Integer quis sapien et diam facilisis facilisis ultricies quis justo. Phasellus sem <b>turpis</b>, ornare quis aliquet ut, volutpat et lectus. Aliquam a egestas elit.',
    ],
]);

```

Similarly, if you want to create widgets somewhere else than in ```resources/views/vendor/backpack/ui/widgets```, you can pass that directory as the namespace of your widget. For example, ```resources/views/admin/widgets``` would have ```admin.widgets``` as the namespace.
