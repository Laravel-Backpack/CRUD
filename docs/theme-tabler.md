# Theme Tabler

<a name="install-and-configure">
### About

For more information about installation and/or configuration steps, see [backpack/theme-tabler](https://github.com/Laravel-Backpack/theme-tabler) on Github.

<a name="components"></a>
## Components

<a name="menu-dropdown-column">
### Menu Dropdown Column

In addition to regular menu components provided by Backpack [Menu Dropdown and Menu Dropdown Item](https://backpackforlaravel.com/docs/base-components#menu-dropdown-and-menu-dropdown-item), Tabler theme provides this new component which is used to create side by side menus on horizontal layouts.

#### Requirements
- Require `Backpack/CRUD:6.6.4` or higher
- Require `Backpack/theme-tabler:1.2.1` or higher

#### Usage
In your parent dropdown item, enable the feature by setting `:withColumns="true"` and then use `x-theme-tabler::menu-dropdown-column` component to wrap each set of menu items. See the example below:

```html
<x-backpack::menu-dropdown title=" Vet Clinic" icon="las la-clinic-medical" :withColumns="true">
        <x-theme-tabler::menu-dropdown-column>
            <x-backpack::menu-dropdown-item title=" Appointments" icon="las la-calendar-check" link="https://example.com" />
            <x-backpack::menu-dropdown-item title=" Medical Records" icon="las la-file-medical" link="https://example.com" />
            <x-backpack::menu-dropdown-item title=" Prescriptions" icon="las la-prescription" link="https://example.com" />
        </x-theme-tabler::menu-dropdown-column>
        <x-theme-tabler::menu-dropdown-column>
            <x-backpack::menu-dropdown-item title=" Pet Vaccinations" icon="las la-syringe" link="https://example.com" />
            <x-backpack::menu-dropdown-item title=" Pet Grooming" icon="las la-cut" link="https://example.com" />
            <x-backpack::menu-dropdown-item title=" Pet Boarding" icon="las la-home" link="https://example.com" />
        </x-theme-tabler::menu-dropdown-column>
</x-backpack::menu-dropdown>
```

![tabler side by side menus](https://github.com/Laravel-Backpack/docs/assets/7188159/2c65e523-a545-486a-b7b0-cbd9f92ee273)
