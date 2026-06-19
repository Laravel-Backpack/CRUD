### How to publish a column / field / filter / button and modify it

All Backpack packages allow you to overwrite and customize the views. If you want Backpack to pick up _your_ file, instead of the one in the package, you can do that by just placing a file with the same name in your views. So if you want to overwrite the select2 field (```vendor/backpack/crud/src/resources/views/fields/select2.blade.php```) to change some functionality, you need to create ```resources/views/vendor/backpack/crud/fields/select2.blade.php```. You can do that manually, or use this command:
```shell
php artisan backpack:field --from=select2
```
This will look for the blade file and copy it inside the folder, so you can edit it.

>**Please note:** Once you place a file with the same name inside your project, Backpack will pick that one up instead of the one in the package. That means that even though the file in the package is updated, you won't be getting those updates, since you're not using that file. Blade modifications are almost never breaking changes, but it's a good thing to receive updates with zero effort. Even small ones. So please overwrite the files as little as possible. Best to create your own custom fields/column/filters/buttons, whenever you can.
