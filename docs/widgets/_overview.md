# Widgets

## About

Widgets (aka cards, aka charts, aka graphs) provide a simple way to insert blade files into admin panel pages. You can use them to insert cards, charts, notices or custom content into pages.

### Requirements

To use the ```Widget``` class, you should make sure your main views (for new admin panel pages) extend the ```backpack::blank``` or ```backpack_view('blank')``` blade template. This template includes two sections where you can push widgets:
- ```before_content```
- ```after_content```

### How to Use

You can push widgets to these sections, by using the autoloaded ```Widget``` class. You can think of the ```Widget``` class as a global container for widgets, for the current page being rendered. That means you can call the ```Widget``` container inside a ```Controller```, inside a ```view```, or inside a service provider you create - wherever you want.

```php
use Backpack\CRUD\app\Library\Widget;

Widget::add($widget_definition_array)->to('before_content');

// alternatively, use a fluent syntax to define each widget attribute
Widget::add()
    ->to('before_content')
    ->type('card')
    ->content(null);
```

### Mandatory Attributes

When passing a widget array, you need to specify at least these attributes:
```php
[
   'type' => 'card' // the kind of widget to show
   'content' => null // the content of that widget (some are string, some are array)
],
```

### Optional Attributes

Most widget types also have these attributes present, which you can use to tweak how the widget looks inside the page:
```php
'wrapper' => [
    'class' => 'col-sm-6 col-md-4', // customize the class on the parent element (wrapper)
    'style' => 'border-radius: 10px;',
]
```

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
