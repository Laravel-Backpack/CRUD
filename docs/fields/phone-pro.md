### phone [PRO]

Show a telephone number input. Lets the user choose the prefix using a flag from dropdown.

```php
CRUD::field([   // phone
    'name'  => 'phone', // db column for phone
    'label' => 'Phone',
    'type'  => 'phone',

    // OPTIONALS
    // most options provided by intlTelInput.js are supported, you can try them out using the `config` attribute;
    //  take note that options defined in `config` will override any default values from the field;
    'config' => [
        'onlyCountries' => ['bd', 'cl', 'in', 'lv', 'pt', 'ro'],
        'initialCountry' => 'cl', // this needs to be in the allowed country list, either in `onlyCountries` or NOT in `excludeCountries`
        'separateDialCode' => true,
        'nationalMode' => true,
        'autoHideDialCode' => false,
        'placeholderNumberType' => 'MOBILE',
    ]
]);
```

For more info about parameters please see this JS plugin's [official documentation](https://github.com/jackocnr/intl-tel-input).

> NOTE: you can validate this using Laravel's default **numeric** or if you want something advanced, we recommend [Laravel Phone](https://github.com/Propaganistas/Laravel-Phone)
