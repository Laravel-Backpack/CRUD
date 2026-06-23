# Backpack CRUD — Report Operation

Add-on package `backpack/report-operation`. Adds a dashboard with aggregate statistics, charts, and metrics per entity on a dedicated Reports page.

**⚠️ `backpack/report-operation` is a paid add-on.** Check if the user has it installed before generating report code. Do NOT run `composer require` for it — the user must purchase and configure credentials first. See `rules/pro-features.md`.

**FREE alternative**: Use `Widget::add()` with `card` or `progress` widgets on the List page for simple stats. These show counts and values but cannot render charts or per-entity dashboards.

---

## How to Use

**Step 1.** Add the trait to your CrudController:

```php
use Backpack\ReportOperation\Http\Controllers\Operations\ReportOperation;

class ProductCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use ReportOperation;
}
```

**Step 2.** Configure the report in `setupReportOperation()`:

```php
protected function setupReportOperation()
{
    // Total count widget
    CRUD::widget('report')
        ->type('card')
        ->label('Total Products')
        ->value($this->crud->getEntries()->count());

    // Chart widget
    CRUD::widget('report')
        ->type('chart')
        ->chartType('bar')
        ->label('Products by Month')
        ->data(fn () => \App\Models\Product::selectRaw('MONTH(created_at) as month, COUNT(*) as count')
            ->groupBy('month')
            ->pluck('count', 'month')
        );
}
```

**Step 3.** Access the report at `{admin_prefix}/{entity}/report`.

---

## Widget Types

| Type | Description | Requires `backpack/pro`? |
|------|-------------|--------------------------|
| `card` | Single stat in a card (count, sum, average) | No |
| `chart` | Bar, line, pie, or doughnut chart | Yes |
| `progress` | Progress bar showing a percentage | No |
| `table` | Mini data table within the dashboard | No |

---

## FREE Alternative (without report-operation)

For simple dashboard stats, use standard widgets on your List page. This gives you summary cards above your table without any paid package:

```php
protected function setupListOperation()
{
    // Summary card widget (FREE)
    Widget::add()
        ->type('card')
        ->to('before_content')
        ->wrapper(['class' => 'col-sm-4'])
        ->value(Product::count())
        ->description('Total Products');

    Widget::add()
        ->type('card')
        ->to('before_content')
        ->wrapper(['class' => 'col-sm-4'])
        ->value(Product::where('active', true)->count())
        ->description('Active Products');

    Widget::add()
        ->type('progress')
        ->to('before_content')
        ->wrapper(['class' => 'col-sm-4'])
        ->value(75)
        ->description('Completion');
}
```

**Limitations of the free approach**:
- No dedicated Reports page — widgets show on the List page
- No charts (bar, line, pie) — only cards and progress bars
- No per-entity aggregation logic built-in — you write queries manually
