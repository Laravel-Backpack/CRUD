### How to link two air-datepicker fields

The `air-datepicker` field stores its air-datepicker instance on the visible input, and provides one JS helper to reach it:

- `bpFieldAirDatepickerInstance(fieldName)` - returns the air-datepicker instance of a field, by field name (or `false`). With the instance you can use the [full air-datepicker API](https://air-datepicker.com/docs): `dp.update()`, `dp.selectDate()`, `dp.clear()`, `dp.onSelect`, etc.

For example, let's prevent `end_date` from ever being before `start_date`, in a Create/Update form that has two single `air-datepicker` fields:

```php
CRUD::field([
    'name'  => 'start_date',
    'label' => 'From date',
    'type'  => 'air-datepicker',
]);

CRUD::field([
    'name'  => 'end_date',
    'label' => 'To date',
    'type'  => 'air-datepicker',
]);
```

Then add a script to the operation:

```php
// in your CrudController, inside setupCreateOperation() and/or setupUpdateOperation()
Widget::add()->type('script')->content('assets/js/admin/forms/link-dates.js');
```

```js
// assets/js/admin/forms/link-dates.js
$(function () {
    // Backpack initializes its fields inside its own document-ready handler,
    // registered after this one — defer with a macrotask so the instances
    // already exist.
    setTimeout(linkDates, 0);
});

function linkDates() {
    var startDp = bpFieldAirDatepickerInstance('start_date');
    var endDp   = bpFieldAirDatepickerInstance('end_date');

    if (!startDp || !endDp) return;

    // What minDate/maxDate each picker was configured with in PHP ('' by default).
    var startDefaultMaxDate = startDp.opts.maxDate;
    var endDefaultMinDate   = endDp.opts.minDate;

    // One way: "To date" can't be before "From date"...
    linkAirDatepickers(startDp, endDp, 'minDate', endDefaultMinDate);
    // ...and the other way: "From date" can't be after "To date".
    linkAirDatepickers(endDp, startDp, 'maxDate', startDefaultMaxDate);
}

function linkAirDatepickers(sourceDp, targetDp, limit, targetDefaultLimit) {
    var originalOnSelect = sourceDp.opts.onSelect; // the field's own handler

    sourceDp.opts.onSelect = function (args) {
        // keep the field's built-in behavior (updates the hidden input)
        originalOnSelect.apply(this, arguments);

        if (args.date) {
            targetDp.update({ [limit]: args.date });

            // optional: clear the target if its current selection is now invalid
            var targetSelected = targetDp.selectedDates[0];
            if (targetSelected && (
                (limit === 'minDate' && targetSelected.getTime() < args.date.getTime()) ||
                (limit === 'maxDate' && targetSelected.getTime() > args.date.getTime())
            )) {
                targetDp.clear();
            }
        } else {
            // source cleared: restore the originally configured limit.
            // NOTE: passing null throws in air-datepicker 3.6.0 — restore ''
            // (or the original value) instead.
            targetDp.update({ [limit]: targetDefaultLimit || '' });
        }
    };

    // also apply the link on page load, when editing an entry that already
    // has a source value
    var initialSource = sourceDp.selectedDates[0];
    if (initialSource) {
        targetDp.update({ [limit]: initialSource });
    }
}
```

See the [air-datepicker field docs](../fields/air-datepicker-pro.md).
