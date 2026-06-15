# Demo

---

We've put together a working Laravel app (backend-only). This should make it easier to:
- see how it looks & feels;
- see how it works;
- change stuff in code, to see how easy it is to customize Backpack;

In this [Demo repository](https://github.com/laravel-backpack/demo), we've:
- installed Laravel 13;
- installed Backpack\CRUD; <span class="badge badge-pill badge-success">FREE</span>
- installed Backpack\PRO; <span class="badge badge-pill badge-info">PRO</span>
- installed Backpack\Editable-Columns; <span class="badge badge-pill badge-warning">PREMIUM</span>
- created a few demo models and admin panels for them, using dozens of field types, column types, filters, etc - to show off most of Backpack CRUD + PRO features;
- installed a few Backpack extensions: PermissionManager, PageManager, LogManager, BackupManager, Settings, MenuCRUD, NewsCRUD;


>**Don't use this demo to start your real projects.** Please use [the recommended installation procedure](/docs/{{version}}/installation). You don't want all the bogus entities we've created. You don't want all the packages we've used. And you _definitely_ don't want the default admin user. Start from scratch!

<a name="preview"></a>
## Demo Preview

![https://user-images.githubusercontent.com/1032474/86720524-c5a1d480-c02d-11ea-87ed-d03b0197eb25.gif](https://user-images.githubusercontent.com/1032474/86720524-c5a1d480-c02d-11ea-87ed-d03b0197eb25.gif)

If you just want to take a look at the Backpack interface and click around, you don't have to install anything. **Take a look at [demo.backpackforlaravel.com](https://demo.backpackforlaravel.com/admin) - we've installed for you.** The online demo is wiped and reinstalled every hour, on the hour.

<a name="installation"></a>
## Demo Installation

If you _do_ want to install the Demo and play around, it's easy to do so. But because the demo uses [Backpack/PRO](https://backpackforlaravel.com/products/pro-for-unlimited-projects) and [Backpack/Editable-Columns](https://backpackforlaravel.com/products/editable-columns), you need to [purchase "Everything" first](https://backpackforlaravel.com/pricing), or those addons individually. If you don't like it, we'll happily give you a refund.

1) In your ```Projects``` or ```www``` directory, wherever you host your apps:

```zsh
git clone https://github.com/Laravel-Backpack/demo.git backpack-demo
```

2) Set your database information in your ```.env``` file (use ```.env.example``` as an example);

3) Authenticate and install the requirements:
``` zsh
cd backpack-demo

# Tell Composer how to connet to the private Backpack repo.
# You'll need to replace these with your real token and password:
composer config http-basic.backpackforlaravel.com [your-token-username] [your-token-password]

# Install all dependencies
composer install
```

4) Populate the database:
```zsh
php artisan key:generate
php artisan migrate
php artisan db:seed --class="Backpack\Settings\database\seeds\SettingsTableSeeder"
php artisan db:seed
```

5) Cache the CSS and JS assets using Basset:
```zsh
php artisan storage:link
php artisan basset:cache
```

<a name="usage"></a>
## Demo Usage

Once everything's installed, and your database has been set up:

- Your admin panel is available at `{APP_URL}`/admin
- Login with email ```admin@example.com```, password ```admin```
- You can register a different account, to check out the process and see your Gravatar inside the admin panel.
- By default, registration is open only in your local environment. Check out ```config/backpack/base.php``` to change this and other preferences.
- Check out the Monsters admin panel - it features over 50 field types.
- Devs love Backpack not just for its standard functionality, but also for how easy it is to code your own, or customize every little bit of it. Our recommendation:
    - Go through the [CRUD Tutorial](/docs/{{version}}/crud-tutorial) to understand it;
    - Create a new CRUD panel for an entity, using the faster procedure outlined at the end of that page; say... ```car```;


>**vhost configurations**
>
>Depending on your vhost configuration you might need to access the application via a different url, for example if you're using ```artisan serve``` you can access it on http://127.0.0.1:8000/admin - if you're using Laravel Valet, then it may look like http://backpack-demo.test/admin - you will need to access the url which matches your systems configuration. If you do not understand how to configure your virtual hosts, we suggest [watching Laracasts Episode #1](https://laracasts.com/series/laravel-from-scratch/episodes/1) to quickly get started.
