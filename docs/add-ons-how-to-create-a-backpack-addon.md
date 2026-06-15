# How to Create an Add-on

---


### Intro

There's nothing special about add-ons. They are simple Composer packages. 

But for consistency, we recommend you follow our simple folder structure. Our rule of thumb: **organize your ```src``` folder like it were a Laravel application**. We do this because it's easier for users to understand how the package works, and it makes it easy to copy-paste the code inside their apps and modify, for complicated use cases. That way, add-ons can be kept super-simple, with everybody adding functionality _in their own apps_. Example folder structure:
- [src]
    - [app]
      - [Http]
      - [Models]
      - [Requests]
    - [database]
      - [migrations]
      - [seeds]
    - [routes]
    - YourPackageNameServiceProvider.php
- [tests]
- composer.json
- CHANGELOG.md
- LICENSE.md
- README.md

Requirements:
- a working installation of the [Backpack demo](https://github.com/laravel-backpack/demo)
- 1-2 hours

<a name="create-a-package"></a>
## Step 1. Create a package

### Install Backpack Demo

We're going to use [the Backpack demo project](https://github.com/laravel-backpack/demo) to create a new package. Follow the instructions in [the Installation chapter](https://github.com/laravel-backpack/demo#install). 

Any Laravel & Backpack app would work. But since you're going to require packages that you only need during package development, and make various changes to app files, we recommended you _create_ the package using a Backpack demo. After the package is online (with zero functionality), you will _install_ it in a real application, and _modify_ it right there, in the ```vendor``` folder. You will then delete this Backpack demo project.


### Install CLI tool

We're going to use [Jeroen-G/laravel-packager](https://github.com/Jeroen-G/laravel-packager) to generate a new package. Follow the instructions in the Installation chapter. 

### Generate Package Files

Next up, decide what your vendor name will be. This is NOT the package name, it's the name all your packages will reside under. For example, Laravel uses "laravel". Backpack for Laravel uses "backpack". Jeffrey Way uses "way". If unsure, use your GitHub username for the vendor name. That's what people usually do, if they don't run a company / brand.

Then decide what your package name will be. Ex: ```newscrud```, ```usermanager```, etc.

Then run:
```
php artisan packager:new myvendor mypackage
```

This will create a ```/packages/``` folder in your root directory, where your package will be stored, so you can build it. It will also pull [a very basic package template](https://github.com/thephpleague/skeleton), created by thephpleague. Everything you have right now is in ```packages/myvendor/mypackage``` - check it out.

### Customize Generated Files

Now let's customise it and add some boilerplate code, everything that most Laravel Packages will need. Replace everything you need in ```composer.json```, ```CHANGELOG.md```, ```CONTRIBUTING.md```, ```LICENSE.md```, ```README.md```. Make it yours.

If you want to use Laravel package auto-discovery (and why wouldn't you), make sure to include the Laravel providers section in your ```composer.json```'s ```extra``` section, like so:
```
    "extra": {
        "branch-alias": {
            "dev-master": "1.0-dev"
        },
        "laravel": {
            "providers": [
                "Backpack\\NewsCRUD\\NewsCRUDServiceProvider"
            ]
        }
    }
```

In ```/src/``` you'll find your service provider. That's where your package's logic is, but it's empty. Use this Service Provider template and replace ```League``` with your ```myvendor``` and ```Skeleton``` with your ```mypackage```. Your package will probably need some Controllers, routes and config files. 

### Create The Files Your Package Needs

Here are a few commands that could help you do that:

```bash
# make sure everything is inside your src folder
cd src/

# to create a controller
echo "<?php " >app/Http/Controllers/ControllerName.php

# to create a request file
echo "<?php " >app/Http/Requests/EntityRequest.php

# to create a route file
echo "<?php " >routes/mypackage.php

# to create a config file
echo "<?php " >config/mypackage.php

# to create a views folder
mkdir resources/views/
```

You use the routes, config and controller files just like you use the ones in your application. Nothing changes there. But remember that all classes should have the package's namespace:

```php
namespace MyVendor\MyPackage\Http\Controllers;
```

### Make Sure Your Laravel App Loads The Package

Add your service provider to your app's ```/config/app.php```

If not, add it:
```
"MyVendor\MyPackage\MyPackageServiceProvider",
```

Check that you autoload your package in composer.json:
```
"autoload" : {
  "psr-4": {
    "Domain\\PackageName\\": "packages/Domain/PackageName/src"
  }
},
```

Let's recreate the autoload
```
cd ../../../..
composer dump-autoload
```

If you have a config file to publish, do:
```
php artisan vendor:publish
```

Now test it. Start by doing a ```dd('testing)``` in your service provider's ```boot()``` method. If your package is working fine, I recommend you put it online first, even before it does _anything useful_. You'll get the setup out of the way, and be able to focus on code. Plus, you'll be able to install it in a _real_ Backpack application, and edit it from the ```vendor/myvendor/mypackage``` folder (and push to your git remote).

---

<a name="step-2-put-it-on-github"></a>
## Step 2. Put it on GitHub

```
cd packages/domain/packagename/
git init
git add .
git commit -m "first commit"
```

Create [a new GitHub repository](https://github.com/new).

```
git remote add origin git@github.com:yourusername/yourrepository.git
git push -u origin master
git tag -a 1.0.0 -m 'First version'
git push --tags
```

Tags are the way you will version your package, so it's important you do it. People will only be able to get updates if you tag them.

---

<a name="step-3-put-it-on-packagist"></a>
## Step 3. Put it on Packagist

On [Packagist.org](https://packagist.org), submit a new package. Enter your package's GitHub URL and click Check. If any errors occur, follow the onscreen instructions.

When you're done, you'll be taken to your packagist page, where you'll probably get a notice like this:

>This package is not auto-updated. Please set up the [GitHub Service Hook](https://packagist.org/profile/) for Packagist so that it gets updated whenever you push!

Let's take care of that. Click that link, get your API token and go to your package's GitHub page, in ```Settings / Webhooks & Services / Add a new service```. Search for Packagist. Enter your username and the token and hit Submit. Your error in Packagist should disappear in 5–10 minutes.

Congrats! You now have a working package online. You can now require it with composer.


---

<a name="step-4-install-in-a-real-project"></a>
## Step 4. Install in a Real Project

We've instructed you to create the package in a disposable backpack-demo install. If you've done so, you can now install your package **in your _real_ project**:

```bash
composer require myvendor/mypackage --prefer-source
```

Using the ```prefer-source``` flag will actually _clone the git repo_ inside your ```vendor/myvendor/mypackage``` directory. So you can do: 

```bash
cd /vendor/myvendor/mypackage
git checkout master
```

Then after each change you want to publish, you would mark that change in your ```CHANGELOG.md``` file and do:
```bash
git pull origin master
git add .
git commit -am "fixes #14189 - some problem or feature with an id"
git tag 1.0.3
git push origin master --tags
```

---

**That's it. Go build your package!** If you end up with something you like, please share it with the community in the [Gitter Chatroom](https://gitter.im/BackpackForLaravel/Lobby), and add it to [the Community Add-Ons page](/docs/{{version}}/add-ons-community), so other people know about it (_login, then click Edit in the top-right corner of the page_).

You can now delete the Backpack project, and the database you've created for it (if any).

For extra reading credits, these are the resources we've used to create this guide:
- https://laravel.com/docs/packages
- https://laracasts.com/discuss/channels/tips/developing-your-packages-in-laravel-5
- https://github.com/jaiwalker/setup-laravel5-package
- https://github.com/Jeroen-G/laravel-packager
- https://laracasts.com/lessons/package-development-101
