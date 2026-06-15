# Themes

---

<a name="about"></a>
## About

Backpack provides three themes:
- `backpack/theme-tabler` - includes Tabler, the excellent Bootstrap 5 HTML template;
- `backpack/theme-coreuiv2` - includes Backstrap, which is a CoreUI v2 fork we used in Backpack v5;
- `backpack/theme-coreuiv4` - includes CoreUI v4;

Each theme has its PROs and CONs. We will discuss them in detail below, as well as presenting how the theming system works, and how you can use it.


<a name="how-themes-work"></a>
## How Themes Work

Each theme is a separate Composer package, containing the blade files needed for Backpack to show an interface to its admins. Backpack knows what theme to use by looking in `config/backpack/ui.php`. In there you'll see:

```php
    /*
    |--------------------------------------------------------------------------
    | Theme (User Interface)
    |--------------------------------------------------------------------------
    */
    // Change the view namespace in order to load a different theme than the one Backpack provides.
    // You can create child themes yourself, by creating a view folder anywhere in your resources/views
    // and choosing that view_namespace instead of the default one. Backpack will load a file from there
    // if it exists, otherwise it will load it from the fallback namespace.

    'view_namespace'          => 'backpack.theme-tabler::',
    'view_namespace_fallback' => 'backpack.theme-tabler::',
```

Notice that:
- you can specify a new theme by specifying a different view namespace; so if you want to point to a local view directory, you can do that too;
- we have a fallback mechanism in place; if a view isn't found in the first view namespace, Backpack will look in a second view namespace; this allows you to quickly create "child themes";

You can think of themes as extending `UI`. Both in terms of blade views and configs.

<a name="theme-view-fallbacks"></a>
### Theme View Fallbacks

When you're doing `backpack_view('path.to.view')`, Backpack will look both the namespace and fallback namespace configured in `config/backpack/ui.php`. If nothing is found, it will also look in the `backpack/ui` directory. For example if you have:
```php
    'view_namespace'          => 'admin.theme-custom.',
    'view_namespace_fallback' => 'backpack.theme-tabler::',
```
Backpack will use the first file it finds, in the order below. If none of these views exist, it will throw an error:
- `resources/views/admin/theme-custom/path/to/view.blade.php`
- `resources/views/vendor/backpack/theme-tabler/path/to/view.blade.php`
- `vendor/backpack/theme-tabler/resources/views/path/to/view.blade.php`
- `resources/views/vendor/backpack/ui/path/to/view.blade.php`
- `vendor/backpack/crud/src/resources/views/ui/path/to/view.blade.php`

This fallback mechanism might look complex at first, but you'll quickly get used to it, and it provides A LOT of power and convenience. It allows you to:
- override a blade file for one theme, by placing it in the theme directory;
- create a blade file for all themes, by placing it in the `ui` directory;
- easily create new themes, that extends a different theme;
- easily add/remove themes from your project, using Composer;

<a name="theme-configs"></a>
### Theme Configs

Each theme can provide its own config file. That file overrides anything that was set in `config/backpack/ui.php`, because each theme may want to do things differently, include other assets etc. So:
- if you change `config/backpack/ui.php`, your change will apply to ALL themes, unless that theme has overriden the config in its own config file;
- if you change `config/backpack/theme-tabler.php`, your change will only apply to that theme;

Inside your files you can access a theme config by using `backpack_theme_config('something')`.


<a name="translate-theme-strings"></a>
### Translate Theme Strings

Some themes may provide languages strings that can be translated into other languages. Those themes are stored inside the `backpack.theme-{themeName}` namespace so to change any strings or create new translations you can place your files in `lang/vendor/backpack.theme-{themeName}/{language}/theme-{themeName}.php`.

Replace `{themeName}` and `{language}` with the theme name and language you want to translate. For example, if you want to translate the `backpack/theme-tabler` theme into Spanish, you would create a file at `lang/vendor/backpack.theme-tabler/es/theme-tabler.php`.

> Better than just creating a file, feel free to submit a PR or just open an issue with the translated keys and we may add them into the package so all users can benefit from them. We highly appreciate your efforts. Thank you!

<a name="how-themes-work"></a>
## Official Themes

<a name="tabler-theme"></a>
### Tabler Theme

![](https://user-images.githubusercontent.com/1032474/240274915-f45460a7-b876-432c-82c3-b0b3c60a39f2.png)

[`backpack/theme-tabler`](https://github.com/Laravel-Backpack/theme-tabler) was created in 2023 and brings the full power of Tabler, the excellent Bootstrap HTML Template, to Backpack developers. In addition to "normal" Backpack views it also offers:
- dark mode;
- 3 alternative authentication views (default, cover, image);
- 9 alternative layouts (horizontal, horizontal overlap, horizontal dark, vertical, vertical dark, vertical transparent, right vertical, right vertical dark, right vertical transparent);

We believe Tabler is the best HTML template on the market right now, with many _many_ HTML components to choose from, which is why we've chosen Tabler as the default theme for Backpack.

For more information and installation steps, see [`backpack/theme-tabler`](https://github.com/Laravel-Backpack/theme-tabler) on Github.

To get more information on Backpack specific built-in components for tabler, please check the [Tabler Theme](https://backpackforlaravel.com/docs/theme-tabler) page.

<a name="coreuiv2-theme"></a>
### CoreUIv2 Theme

![](https://user-images.githubusercontent.com/1032474/240272550-456499a0-ef31-48a1-a985-1de3ff6107e5.png)

[`backpack/theme-coreuiv2`](https://github.com/Laravel-Backpack/theme-coreuiv2) is a legacy theme. It provides the views from Backpack v5... to Backpack v6 and v7 users. It serves three purposes:
- allows Backpack developers who need Internet Explorer support to upgrade to Backpack v6 & v7;
- allows Backpack developers who have _heavily_ customized their blade files to upgrade to Backpack v6 & v7;
- allows Backpack developers to easily upgrade from v5 to v6 and v7, by using this theme with few breaking changes, before moving to one with a lot more breaking changes;

We do not recommend using this theme in production, long-term. We recommend using `theme-coreuiv4` or ideally `theme-tabler`, which use Bootstrap 5. But we do understand some projects are too difficult to upgrade or migrate, and for those projects we've spent the time to create this theme.

For more information and installation steps, see [`backpack/theme-coreuiv2`](https://github.com/Laravel-Backpack/theme-coreuiv2) on Github.

<a name="tabler-theme"></a>
### CoreUIv4 Theme

![](https://user-images.githubusercontent.com/1032474/240274314-184d328e-0e6c-4d67-942b-4e4d4efd96c8.png)

[`backpack/theme-coreuiv4`](https://github.com/Laravel-Backpack/theme-coreuiv4) uses the CoreUI v4 Bootstrap HTML Template. It's an easy upgrade from CoreUI v2 that we've used in Backpack v5, so if you're upgrading a project with a few custom blade files, you'll find this is an easiest theme to adopt. Not many breaking changes from v5 to v6.

For more information and installation steps, see [`backpack/theme-coreuiv4`](https://github.com/Laravel-Backpack/theme-coreuiv4) on Github.

<a name="what-theme-should-i-use"></a>
## What Theme Should I Use

**Short answer:** Tabler.

**Long answer:** Backpack has 3 themes, to cater for the most scenarios possible:
- **if you're starting a new project, use `backpack/theme-tabler`**; it's the newest theme, with the most features: dark mode, vertical layouts, alternative auth views and many more HTML components to choose from, in your custom pages;
- **if you're upgrading an old project**, depending on how many files there are in your `resources/views/vendor/backpack/`, under the `base` or `ui` directories:
    - if you don't have many files (1-5), use `backpack/theme-tabler`;
    - if you have a few files (5-10), use `backpack/theme-coreuiv4`;
    - if you have many files (10+), use `backpack/theme-coreuiv2`;

Even if you plan to use `theme-tabler` or `theme-coreuiv4`, when upgrading it's a good idea to use `theme-coreuiv2` during the upgrade process, while you make your changes in your PHP classes, configs, etc. Then once everything's working, start using the new theme, and make the needed changes in your blade files.

<a name="how-to-create-a-new-theme"></a>
## How to Create a New Theme

Creating a new Backpack theme should only take you about 5 hours. If it takes you more, please contact us and let us know what was the most time-consuming part, so we can improve the process. In order to create a new Backpack theme, please [follow the guide](/docs/{{version}}/add-ons-tutorial-how-to-create-a-theme).

<a name="how-to-uninstall-a-theme"></a>
## How to Uninstall a Theme

Each theme can have its own uninstallation process. So please check the theme's docs on Github. But in principle, uninstalling a Backpack theme should involve following these steps:

1. Remove the composer package. Eg. `composer remove backpack/theme-coreuiv2`
2. Delete the config file. Eg. `rm -rf config/backpack/theme-coreuiv2.php`
3. Install a new theme (eg. `php artisan backpack:require:theme-tabler`) or change the `view_namespace` in `config/backpack/ui.php` to the theme you want to be active.
