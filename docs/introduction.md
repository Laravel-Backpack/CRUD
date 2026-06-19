# Introduction

Backpack is a collection of Laravel packages that help you **build custom administration panels**, for anything from presentation websites to complex web applications. You can install them on top of existing Laravel installations _or_ fresh projects.

In a nutshell:

- **UI** - Provides a Bootstrap-based visual interface (HTML/CSS/JS), authentication, and notifications. Choose from three themes (Tabler, CoreUI v4, CoreUI v2) or create your own.
- **CRUDs** - Admin panels for Eloquent models. [Understanding Backpack](/docs/{{version}}/getting-started-basics) lets you build one in minutes per model:

If you already have your Eloquent models, generating Backpack CRUDs is as simple as:
```bash
# -------------------------------
# For one specific Eloquent Model
# -------------------------------
# Create a Model, Request, Controller, Route and sidebar item, so
# that one Eloquent model you specify has an admin panel.

php artisan backpack:crud tag # use singular, not plural (like the Model name)

# -----------------------
# For all Eloquent Models
# -----------------------
# Create a Model, Request, Controller, Route and sidebar item for
# all Eloquent models that don't already have one.

php artisan backpack:build
```

If you haven't created your models yet:
- FREE - [`laravel-shift/blueprint`](https://github.com/laravel-shift/blueprint) — YAML-based generator
- PAID - [`backpack/devtools`](https://backpackforlaravel.com/products/devtools) — web GUI for creating models

## How to Start

- **[Video Course](/docs/{{version}}/getting-started-videos)** - 59 minutes
- **[Text Course](/docs/{{version}}/getting-started-basics)** - 20 minutes

## Need to Know

### Requirements

 - Laravel 12.x or 13.x
 - MySQL / PostgreSQL / SQLite / SQL Server

### How does it look?

**Take a look at our [live demo](https://demo.backpackforlaravel.com/admin/login).** If you've purchased ["Everything"](https://backpackforlaravel.com/pricing) you can even [install the demo](/docs/{{version}}/demo) and fiddle with the code. Otherwise, you can just start a new Laravel project, [install Backpack\CRUD](/docs/{{version}}/installation) on top, and [follow our text course](/docs/{{version}}/getting-started-basics) to create a few CRUDs.

### Security

Backpack has never had a critical vulnerability/hack. But there _have_ been important security updates for dependencies (including Laravel). Please [register using Github](/auth/github) or [subscribe to our twice-a-year newsletter](https://backpackforlaravel.com/newsletter), so we can reach you in case your admin panel becomes vulnerable in any way.

### Maintenance

Backpack v7 is the current version and is actively maintained by the Backpack team, with the help of a wonderful community of Backpack veterans. [See all contributors](https://github.com/Laravel-Backpack/CRUD/graphs/contributors).

### License

Backpack is open-core:
- **Backpack CRUD** is [MIT-licensed](https://github.com/Laravel-Backpack/CRUD/blob/main/LICENSE.md) (free, open-source).
- **Backpack PRO** is [EULA-licensed](https://backpackforlaravel.com/eula) (paid, closed-source) — adds features for complex panels. See [FREE vs PRO comparison](https://backpackforlaravel.com/docs/7.x/features-free-vs-paid).
- Of the other add-ons we've created, some are FREE and some are PAID. Please see [our add-ons list](https://backpackforlaravel.test/docs/7.x/add-ons-official) for more info.

[Our documentation](https://backpackforlaravel.com/docs) covers both CRUD and PRO, with all the PRO features clearly labeled [PRO].

### Versioning, Updates and Upgrades

Starting with the previous version, all our packages follow [semantic versioning](https://semver.org/). Here's what `major.minor.patch` (e.g. `7.0.1`) means for us:
- `major` - breaking changes, major new features, complete rewrites; released **once a year**, in February. It adds features that were previously impossible and upgrades our dependencies; upgrading is done by following our clear and detailed upgrade guides.
- `minor` - new features, released in backwards-compatible ways; **every few months**; update takes seconds.
- `patch` - bug fixes & small non-breaking changes; historically **every week**; update takes seconds.

When we release a new Backpack\CRUD version, all paid add-ons receive support for it the same day.

When you buy a premium Backpack add-on, you get access to updates and upgrades for 12 months.

### Add-ons

Backpack's core is open-source and free (Backpack\CRUD). [**FREE**]

Backpack has been supported since 2016 by paid add-ons:
- [Backpack PRO](/products/pro-for-unlimited-projects) - additional fields, columns, operations and filters. [**PAID**]
- [Backpack DevTools](/products/devtools) - web GUI for generating migrations, models and CRUDs. [**PAID**]
- [Backpack FigmaTemplate](/products/figma-template) - Figma kit using Backpack's design. [**PAID**]
- [Backpack EditableColumns](/products/editable-columns) - inline editing in table view. [**PAID**]

Community add-ons for common use cases: [settings](https://github.com/Laravel-Backpack/Settings), [users](https://github.com/eduardoarandah/UserManager), [permissions](https://github.com/Laravel-Backpack/PermissionManager), [page templates](https://github.com/Laravel-Backpack/PageManager), [news/categories/tags](https://github.com/Laravel-Backpack/NewsCRUD). [**FREE**]

See [all add-ons](/addons).
