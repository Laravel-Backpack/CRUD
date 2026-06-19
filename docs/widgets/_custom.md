## Overriding Default Widget Types

You can override a widget type by placing a file with the same name in your ```resources\views\vendor\backpack\ui\widgets``` directory. When a file is there, Backpack will pick that one up, instead of the one in the package. You can do that from command line using ```php artisan backpack:widget --from=widget-name```

Examples:
- creating a ```resources\views\vendor\backpack\ui\widgets\card.blade.php``` file would overwrite the ```card``` widget functionality;
- ```php artisan backpack:widget --from=card``` will take the view from the package and copy it to the directory above, so you can edit it;

>Keep in mind that when you're overwriting a default widget type, you're forfeiting any future updates for that widget. We can't push updates to a file that you're no longer using.

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
