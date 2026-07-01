### air-datepicker [PRO]

A unified date/time/range picker field powered by [air-datepicker](https://air-datepicker.com/) (~13KB, zero dependencies). Replaces the deprecated `date_picker`, `datetime_picker`, and `date_range` fields.

```php
CRUD::field([
    'name'  => 'published_at',
    'label' => 'Published Date',
    'type'  => 'air-datepicker',

    // OPTIONALS
    'air-datepicker' => [
        // Date only (default, format: yyyy-MM-dd)

        // DateTime mode — dateFormat and timeFormat are SEPARATE when timepicker is on
        'timepicker' => true,
        'dateFormat' => 'dd/MM/yyyy',
        'timeFormat' => 'HH:mm',

        // Date range mode (requires two db columns)
        'range'      => true,
        // When using range, 'name' must reference two columns:
        // 'name' => ['start_date', 'end_date'] or 'name' => 'start_date,end_date'

        // Calendar options (all air-datepicker options are pass-through)
        'firstDay'   => 1,                       // Monday (0 = Sunday)
        'minDate'    => '2024-01-01',
        'maxDate'    => '2024-12-31',
        'autoClose'  => true,
        'buttons'    => ['today', 'clear'],

        // Time constraints (when timepicker is on)
        'minHours'    => 9,
        'maxHours'    => 18,
        'minutesStep' => 15,

        // Range preset buttons (when range is true)
        'ranges' => [
            'Today'      => [now()->startOfDay(), now()->endOfDay()],
            'Last 7 Days' => [now()->subDays(6)->startOfDay(), now()],
            'This Month' => [now()->startOfMonth(), now()->endOfMonth()],
        ],

        // Locale override (pass a full air-datepicker locale object)
        'locale' => [
            'days'        => ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'],
            'daysShort'   => ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
            'months'      => ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
            'monthsShort' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            'firstDay'    => 1,
        ],
    ],
]);
```

> **Format tokens:** This field uses Unicode TR35 tokens (NOT Carbon/PHP/Moment tokens).
> Common conversions: Carbon `d/m/Y` → TR35 `dd/MM/yyyy`, Carbon `D MMM YYYY` → TR35 `E MMM yyyy`.
> When `timepicker` is on, `dateFormat` and `timeFormat` are separate — do NOT combine them (e.g. `'dateFormat' => 'dd/MM/yyyy HH:mm'` is wrong, use `'dateFormat' => 'dd/MM/yyyy'` + `'timeFormat' => 'HH:mm'`).

> **Locale:** Day/month names come from Backpack's translation files (`resources/lang/vendor/backpack/{lang}/crud.php` → `date_time` section). English and 11 other languages have built-in translations. To add a language, publish the lang files and add the `date_time` array with `days`, `daysShort`, `daysMin`, `months`, `monthsShort`, `dateFormat`, `timeFormat`, and `firstDay` keys.

> **Dependencies:** Only air-datepicker (~13KB). No jQuery, no moment.js, no dayjs. The old fields (`date_picker`, `datetime_picker`, `date_range`) required moment.js + bootstrap widgets and are now deprecated.

> **Hidden inputs:** Single and datetime modes use a hidden input with `bp-field-main-input` for SQL-format values (`Y-m-d` or `Y-m-d H:i:s`). Range mode uses two hidden inputs with classes `air-datepicker-range-start` and `air-datepicker-range-end`. The visible input is read-only (keyboard input is blocked).

> **Default values:** For range mode, pass `'default' => ['2024-01-01', '2024-12-31']`. For single mode, use `'default' => '2024-01-01'`.

Any option from [air-datepicker's documentation](https://air-datepicker.com/docs) can be passed inside the `air-datepicker` config array — they're transparently forwarded to the JS widget.
