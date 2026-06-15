# Create an Add-On for a Custom Operation

-----

This tutorial will help you package a custom operation into a Composer package, so that you (or other people) can use it in multiple Laravel projects. If you haven't already, please [create your custom operation](/docs/{{version}}/crud-operations#creating-a-custom-operation) first, and make sure it's working well, before you move it to a package. It's just easier that way.


<a name="create-the-package"></a>
## Part A. Create The Package


<a name="generate-package-folder"></a>
### Step 1. Generate the package folder

Install this excellent package that will create the boilerplate code for you:
```sh
composer require jeroen-g/laravel-packager --dev
```

Ask the package to generate the boilerplate code for your new package:

```sh
php artisan packager:new MyName SomeCustomOperation --i
```

Keep in mind:
- the ```MyName``` should be your GitHub handle (or organisation), in studly case (```CompanyName```);
- the ```SomeCustomOperation``` should be the package name you want, in studly case (```ModerateOperation```);
- the ```website``` should be a valid URL, so include the protocol too: ```http://example.com```;
- the ```description``` should be pretty short;
- the ```license``` is just the license name, if it's a common one (ex: ```MIT```, ```GPLv2```);

This will create a ```/packages/MyName/SomeCustomOperation``` folder in your root directory, which will hold all the code for your package. It has pulled a basic package template, that we need to customize.

It will also modify your project's ```composer.json``` file to point to this new folder.

<a name="define-dependencies"></a>
### Step 2. Define dependencies

Inside your ```/packages/MyName/SomeCustomOperation/composer.json``` file, make sure you require the version of Backpack your package will support. If unsure, copy-paste the requirement from your project's ```composer.json``` file. 

```diff
    "require": {
+        "backpack/crud": "^4.0.0"
-        "illuminate/support": "~5|~6"
    },
```

Note: 
- you can also remove the ```illuminate/support``` requirement in most cases - if Backpack is installed, so is that;
- feel free to add any other requirements your package might have;

Notice that this ```composer.json``` will also:
- define your package namespace (in ```autoload/psr-4```);
- set up Laravel package autoloading (in ```extra/laravel/providers```);

<a name="instruct-laravel-to-use-your-package"></a>
### Step 3. Instruct your Laravel Project to use your package

```sh
composer require myname/somecustomoperation
```

Congratulations! Now you have a basic package, installed in ```packages/MyName/SomeCustomOperation```, that is loaded in your current Laravel project. Note that:
- The command above added a requirement to your **project's ```composer.json``` file**, to require the package; Because previously Packager has pointed to the packages folder, it will pick it up from there, instead of the Internet;
- Then your **package's ```composer.json```** file will point to the ```ServiceProvider``` inside your package's ```src``` folder;
- The ServiceProvider is the class that ties your package to Laravel's inner workings.

> If you want to test that your package is being loaded, you can do a ```dd('got here')``` inside your package's ```ServiceProvider::boot``` method. If you refresh the page, you should see that ```dd()``` statement executed.


<a name="move-the-files-needed-for-the-operation"></a>
### Step 4. Move the files needed for the operation

You can choose whatever folder structure you want for your package. But within Backpack add-ons, we follow the convention that the package folder should look as much as possible like a Laravel project folder. That way, when someone looks at the addon's source code, they instantly understand where everything is.

**Operation Trait** 

Notice a file has already been created, with the operation name, inside your package's ```src``` folder. You can **move your operation trait code** from ```app/Http/Controllers/Admin/Operations``` to this ```src/SomeCustomOperation``` file, but make sure:
- you use the proper namespace (```MyName\SomeCustomOperation```);
- you define it as a Trait, not a Class;

**Views**

If your operation has a user interface, consider moving all the views this operation needs inside your package folder, inside a ```resources/views``` folder.

Then in your package's ServiceProvider, make sure inside ```boot()``` that you load the views:
```php
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'somecustomoperation');
```

**Config**

For most custom Operations, there's really no need to define your own config file. You can just instruct people to use the ```config/backpack/crud.php``` file, and define stuff inside ```operations```, inside an array with your operation's name. If this works for you, then:
- delete the ```config``` folder entirely;
- inside your ServiceProvider's ```register()``` method, delete the line with ```mergeConfigFrom()```;
- inside your ServiceProvider's ```bootForConsole()``` method, delete the line that publishes the config file;
- inside your ServiceProvider's ```bootFromConsole()``` method, include:
```bash
        $this->publishes([
            __DIR__.'/../resources/views' => base_path('resources/views/vendor/backpack'),
        ], 'somecustomoperation.views');
```

That way, developers define config values for your custom operation the same way they define them for a default Backpack operation.

**Translations**

If your Operation has an interface, it most likely also needs a translation file, so that strings are translatable. To add a translation file:
- inside your ServiceProvider's ```boot()``` method, include:
```bash
$this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'backpack');
```
- inside your ServiceProvider's ```bootFromConsole()``` method, include:
```bash
        $this->publishes([
            __DIR__.'/../resources/lang' => resource_path('lang/vendor/backpack'),
        ], 'somecustomoperation');
```
- create the ```resources/lang/en``` folder;
- create a PHP file with a shorter representative name inside that folder, for example ```somecustom.php```, with the translation lines there;
- you'll be able to use ```trans('somecustomoperation::somecustom.line_key')``` throughout your operation's controller/views;


<a name="delete-files-you-dont-need"></a>
### Step 5. Delete the package files you don't need

- in most cases you won't need a Facade for the operation, so you can delete the ```src/Facades``` folder; if you do that, also remove the alias to that Facade, at the bottom of your package's ```composer.json``` file;
- in most cases you won't need the ```register()```, ```provides()``` methods in your ServiceProvider; it's best to remove them;


<a name="customize-markdown-files"></a>
### Step 6. Customize Markdown Files

Inside your package folder, go through all markdown files and make them your own. At the very least, go through:
- LICENSE.md - use the [MIT license](https://opensource.org/licenses/MIT) if unsure;
- README.md - write a clear description and instructions for how to use your operation; if you include clear documentation and screenshots, more people will use your package, guaranteed;


<a name="make-your-first-git-commit"></a>
### Step 7. Make your first git commit

Inside your package folder, run:
```bash
cd packages/myname/somecustomoperation
git init
git add .
git commit -m "first commit"
```


<a name="put-the-package-online"></a>
## Part B. Put The Package Online


<a name="put-it-on-github"></a>
### Put it on GitHub

First, [create a new GitHub Repository](https://github.com/new) for it. Remember to use the same name you defined in your package's ```composer.json```. If in doubt, double-check.

Second, add that new GitHub Repo as a remote, and push your code to your new GitHub repo.

```bash
git remote add origin git@github.com:yourusername/yourrepository.git
git push -u origin master
git tag -a 1.0.0 -m 'First version'
git push --tags
```

The tags are the way you will version your package, so it's important you do it.

<a name="put-it-on-packagist"></a>
### Put it on Packagist

In order for people to be able to install your package using composer, your package needs to be registered with Packagist, Composer's free package registry.

On [Packagist.org](https://packagist.org/), submit a new package. Enter your package's GitHub URL and click Check. If any errors occur, follow the onscreen instructions. When you're done, you're taken to your package's packagist page.

**Congrats, you have a working package online**, you can now install it using Composer.

Note: On the package page, you might get a notice like this: _This package is not auto-updated. Please set up the [GitHub Service Hook](https://packagist.org/profile/) for Packagist so that it gets updated whenever you push!_ Let's take care of that. Click that link, get your API token and go to your package's GitHub page, in Settings / Webhooks & Services / Add a new service. Search for Packagist. Enter your username and the token and hit Submit. Your error in Packagist should disappear in 5–10 minutes.

<a name="feedback-and-promotion"></a>
### Feedback and Promotion

Congratulations on your new Backpack addon! 

To get feedback, ask people to try it on:
- [our subreddit](https://www.reddit.com/r/BackpackForLaravel/)
- [our Gitter chatroom](https://gitter.im/BackpackForLaravel/Lobby)

Make sure you write something nice, so people are interested to click.

After you've got some feedback, and a few users have installed your package and everything seems fine, time to promote it big time:
- post it to [laravel-news.com/links](https://laravel-news.com/links)
- show it off in the [Laracasts forum](https://laracasts.com/discuss)
- ask people to try it in [laravel.io](https://laravel.io/forum)
