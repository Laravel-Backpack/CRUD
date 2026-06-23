# Backpack CRUD — Calendar Operation

Add-on package `backpack/calendar-operation`. Displays date-based entries in a full-page calendar view with month/week/day layouts, drag-and-drop rescheduling, and click-to-edit.

**⚠️ `backpack/calendar-operation` is a paid add-on.** Check if the user has it installed before generating calendar code. Do NOT run `composer require` for it — the user must purchase and configure credentials first. See `rules/pro-features.md`.

**FREE alternative**: Use the standard List view with a `date` or `datetime` column and ordering. There is no built-in free calendar equivalent — the list view is the closest you can get without this package.

---

## How to Use

**Step 1.** Add the trait:

```php
use Backpack\CalendarOperation\Http\Controllers\Operations\CalendarOperation;

class EventCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use CalendarOperation;
}
```

**Step 2.** Configure in `setupCalendarOperation()`:

```php
protected function setupCalendarOperation()
{
    CRUD::setOperationSetting('startColumn', 'start_date');
    CRUD::setOperationSetting('endColumn', 'end_date');
    CRUD::setOperationSetting('titleColumn', 'name');
    CRUD::setOperationSetting('colorColumn', 'color');

    // Optional
    CRUD::setOperationSetting('defaultView', 'month');     // month, week, day
    CRUD::setOperationSetting('editable', true);            // drag-and-drop rescheduling
    CRUD::setOperationSetting('selectable', true);          // click date range to create
}
```

**Step 3.** The calendar is available at `{admin_prefix}/{entity}/calendar`.

---

## Model Requirements

Your model must have date columns. For recurring events or drag-and-drop, additional columns are needed:

```php
// Minimal — single date event
Schema::create('events', function (Blueprint $table) {
    $table->id();
    $table->string('name');          // titleColumn
    $table->date('start_date');       // startColumn
    $table->date('end_date');         // endColumn (optional — uses start if not set)
    $table->string('color')->nullable(); // colorColumn (optional)
    $table->timestamps();
});
```

---

## FREE Alternative (without calendar-operation)

Use the standard List view with date-based ordering and filtering. This shows entries in a table — not a calendar — but gives the user basic date visibility:

```php
protected function setupListOperation()
{
    CRUD::column('name');
    CRUD::column('start_date')->type('date');
    CRUD::column('end_date')->type('date');

    // Default sort by upcoming events
    $this->crud->orderBy('start_date', 'asc');
}
```

Add a simple `where` to show only upcoming events:

```php
public function setup()
{
    CRUD::setModel(\App\Models\Event::class);
    CRUD::setRoute(config('backpack.base.route_prefix') . '/event');
    CRUD::setEntityNameStrings('event', 'events');

    // Show only future events by default (NOT a filter — permanent scope)
    CRUD::addClause('where', 'start_date', '>=', now()->startOfDay());
}
```

**Limitations of the free approach**:
- No calendar visual — entries display in a table
- No drag-and-drop rescheduling
- No month/week/day layout switching
- No click-date-range-to-create workflow
