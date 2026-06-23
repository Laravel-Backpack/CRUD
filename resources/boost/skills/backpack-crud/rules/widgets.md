# Backpack CRUD — Widgets

Widgets add content blocks to operation pages outside the main table/form. Added in `setup()` or any `setup*Operation()`.

## Widget Sections

| Section | Where it appears |
|---------|-----------------|
| `before_content` | Top of page, above everything |
| `after_content` | Bottom of page |
| `before_filters` | Above filter bar (List only) |
| `after_filters` | Below filter bar (List only) |
| `details_row` | Inside the List details row |

## Adding Widgets

```php
use Backpack\CRUD\app\Library\Widget;

// In setup() or setupListOperation():
Widget::add()->type('progress')->to('before_content')
    ->value(135)->description('Total Users')->progress(70);

// Multiple widgets at once:
Widget::add([
    ['type' => 'progress', 'to' => 'before_content', 'value' => 50, 'description' => 'Orders'],
    ['type' => 'card',     'to' => 'before_content', 'content' => 'admin.widgets.status'],
]);

// Remove a widget:
Widget::remove('section-name');

// Hidden widget (for deferred rendering):
Widget::make()->type('view')->view('admin.widgets.later');
```

## Widget Types

### progress
```php
Widget::add()
    ->type('progress')
    ->to('before_content')
    ->value(135)
    ->description('Total Users')
    ->progress(70)           // percentage, 0-100
    ->progressClass('bg-success')
    ->hint('+10% this week');
```

### card
```php
Widget::add()
    ->type('card')
    ->to('before_content')
    ->content('admin.widgets.status_card')  // Blade view path
    ->wrapper(['class' => 'col-md-4']);
```

### chart
```php
Widget::add()
    ->type('chart')
    ->to('before_content')
    ->content('admin.charts.users_over_time')
    ->wrapper(['class' => 'col-md-6']);
```

### view
```php
Widget::add()
    ->type('view')
    ->to('before_content')
    ->view('admin.widgets.alert')
    ->wrapper(['class' => 'col-md-12']);
```

### script
```php
Widget::add()
    ->type('script')
    ->content('assets/js/admin/forms/product.js');  // file path

Widget::add()
    ->type('script')
    ->content('js/admin/custom.js')
    ->stack('after_scripts');
```

### style
```php
Widget::add()
    ->type('style')
    ->content('assets/css/admin/custom.css');

Widget::add()
    ->type('style')
    ->content('css/admin/custom.css')
    ->stack('after_styles');
```

### Details Row Widgets
```php
CRUD::enableDetailsRow();

Widget::add()->to('details_row')
    ->type('progress')
    ->value(fn ($entry) => $entry->orders->count())
    ->description('Orders')
    ->progress(fn ($entry) => $entry->completion_rate);
```

## Custom Widget Types

Generate: `php artisan backpack:widget WidgetName`.

## Gotchas
- Script widgets loaded via file path are served by Backpack's asset system — place files in `public/`.
- Details row widgets receive `$entry` through closures using `fn ($entry) => ...`.
- Widget ordering: widgets are rendered in the order they are added.
- The `before_filters` / `after_filters` sections only exist in List operation pages.
