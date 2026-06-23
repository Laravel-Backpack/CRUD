# Backpack CRUD — Chips

Chips are Blade view components that display summary information in a card-like format. They exist as column types, widget types, or custom Blade views generated via artisan. Chips are **view-based** — there is no PHP `Chip` class or `addChip()` method.

## Chip Column Type

```php
CRUD::addColumn([
    'name'    => 'chip_display',
    'type'    => 'chip',
    'label'   => 'Summary',
    'heading' => fn ($entry) => $entry->name,
    'details' => fn ($entry) => [
        ['value' => $entry->orders_count, 'label' => 'Orders'],
        ['value' => '$' . number_format($entry->total_revenue, 2), 'label' => 'Revenue'],
    ],
]);
```

## Chip Widget Type

```php
Widget::add()
    ->type('chip')
    ->to('before_content')
    ->heading('Dashboard Overview')
    ->details([
        ['value' => 150, 'label' => 'Total Orders'],
        ['value' => '$12,450', 'label' => 'Revenue'],
    ]);
```

## Custom Chips

Generate a custom chip view: `php artisan backpack:chip ChipName`.

This creates a Blade view in `resources/views/vendor/backpack/crud/chips/` that you can customize.

## Gotchas
- Chips are purely view-based — they render from Blade templates, not PHP classes.
- `heading` and `details` can be closures receiving `$entry` when used as a column.
- Detail values are displayed as-is — format them before passing.
