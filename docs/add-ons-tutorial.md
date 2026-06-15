# How to Create a Backpack Add-on

---


### Intro

So you've created a custom field, column, filter, or an entire CRUD. Great! If you want to re-use it across projects, or you think _other people_ would like to use it too, there's a good way to do that. 

The process below will involve creating a new package on GitHub, Composer & Packagist - which is a little challenging to wrap your head around, the first time you do it. But if you were able to create a custom field, you will be able to do that too. And in doing this, you'll learn the basics of creating and maintaining a PHP package. That's something that not all PHP developers can do, so it's pretty cool, I think. 

> If you already know how to create & maintain a PHP package, this tutorial might be too easy for you. Try to skim it, because we give useful tips. But you can also just go to [:DigitallyHappy/toggle-field-for-backpack](https://github.com/DigitallyHappy/toggle-field-for-backpack), clone the repo and make the changes you see fit.

Requirements:
- a working installation of Laravel & Backpack 4 (alternative: you can install the [Backpack demo](https://github.com/laravel-backpack/demo));
- a GitHub account (free or paid);
- 15-30 minutes;


<a name="scope-and-constants"></a>
## Step 0. Scope and Constants

**Decide what your package is going to do.** Try to keep the package as small as possible. If you're trying to share multiple fields/columns/etc, we recommend you create a different package for each field type. This will make it:
- easier for you to communicate what the package does;
- easier for you to maintain a field type (or abandon it);
- more likely for people to install & use your package;

In the tutorial below, we'll assume you're trying to share one custom field - ```dummy.blade.php```. But the process will be the same no matter what you're building, starting from the skeletons packages below.

Once you know what you're building, there are a few constants which you need to decide upon. Names that once you've chosen, it will be _possible_, but _very difficult_ to change:

**Package Name.** Try to find a name that is as explicit as possible. So that users, just by reading the name, will pretty much understand what the package does. Also, try to include "_for Backpack_" - that way it will stand out to developers who use Backpack (your target audience). 
- Good Examples: ```json-editor-field-for-backpack```, ```user-column-for-backpack```, ```users-crud-for-backpack```;
- Bad Examples: ```custom-field``` (too general), ```cbf``` (too cryptic), ```aurora``` (this is not the place to be creative :smile: );
Since in this example we're trying to build a package for the new ```dummy``` field type, we'll choose ```dummy-field-for-backpack``` as the package name.

**Vendor Name.** You noticed how every time you install a composer package, it's ```composer install something/package-name```? Well that's what that "something" is - the _vendor name_. It's usually the name of the company or person behind the project. The easiest to remember would be your GitHub username, or your company's GitHub username. But, if you're not happy with those, you _can_ choose a different vendor name - basically a brand name, under which you build packages. This could be the place to be creative :smile:, if you don't have a company name already. In the example below we'll use ```company-name```.

**Class Namespace.** When people reference your package's classes, this is what they see first. It's a good practice to use VendorName/PackageName as the namespace for your package. But notice it's no longer kebab-case (using dashes - ```my-company/dummy-field-for-backpack```), it is PascalCase (```MyCompany/DummyFieldForBackpack```).


<a name="fork-the-skeleton-package"></a>
## Step 1. Clone the skeleton package

To get your package online ASAP, we've prepared a few "skeleton" packages, that you can fork and modify:
- [:DigitallyHappy/toggle-field-for-backpack](https://github.com/DigitallyHappy/toggle-field-for-backpack) - field add-on example;
- TODO - column add-on example;
- TODO - filter add-on example;
- TODO - button add-on example;
- TODO - CRUD add-on example;
- TODO - multiple CRUD add-on example;
- TODO - CRUD with dependencies add-on example;

Pick the skeleton package that's as similar as possible to what you want to build. On its GitHub page, under if you click the green **Clone or Download** button, you'll get the path to that repo. In your project, let's clone that repo:
```bash
# git clone {REPO URL} packages/{VENDOR NAME}/{PACKAGE NAME}
git clone git@github.com:DigitallyHappy/toggle-field-for-backpack.git packages/my-company/dummy-field-for-backpack
```

<a name="make-it-your-own"></a>
## Step 2. Make it your own

Take a look at the files you've copied - it's a very simple package. In you package's root folder we have:
- ```README.md``` - your package's "home page" on Github; this should hold all the information needed to use your package;
- ```composer.json``` - configuration file that tells Composer (the PHP package installer) more about your package;
- ```CHANGELOG.md``` - where you should write every time you make changes to the field; 
- ```LICENSE.md``` - so that people know how they're allowed to use your package (MIT is the default); 

Take a look at all of them and modify to fit your needs. It should be faster to modify by hand, and pretty intuitive. But, if it's the first time you create a PHP package, you can use the process below, to make sure you don't mess up anything, since making a casing mistake somewhere (```my-vendor``` instead of ```MyVendor```) could take you very long to debug:
- **namespace**  - find ```DigitallyHappy\ToggleFieldForBackpack``` and replace with your _VendorName\PackageName_ (ex: ```MyCompany\DummyFieldForBackpack```);
- **escaped namespace**  - find ```DigitallyHappy\\ToggleFieldForBackpack``` and replace with your _VendorName\\PackageName_ (ex: ```MyCompany\\DummyFieldForBackpack```);
- **vendor and package name** - find ```digitallyhappy/toggle-field-for-backpack``` and replace with your _vendor-name/package-name_ (ex: ```my-company/dummy-field-for-backpack```);
- **package name** - find ```toggle-field-for-backpack``` and replace with your _package-name_ (ex: ```dummy-field-for-backpack```);
- **vendor name** - find ```digitallyhappy``` and replace with your _vendor-name_ (ex: ```my-company```);
- **author name** - find ```Cristian Tabacitu``` and replace with your name (ex: ```John Doe```);
- **author email** - find ```hello@tabacitu.ro``` and replace with your email (ex: ```john@example.com```);
- **author website** - find ```https://tabacitu.ro``` and replace with your website or GitHub page (ex: ```http://example.com```);
- open ```changelog.md``` and keep only the 1.0.0 version; put today's date;
- open ```composer.json``` and change the description of your package;
- open ```readme.md``` and change the text to better describe _your_ package; don't worry about the screenshot, we'll change that one in a future step;

In ```/src/``` you'll find your service provider, which does one thing: it loads the views in your ```src/resources/views``` under the ```dummy-field-for-backpack``` view namespace. So that anybody who installs your package can use a view that your package includes, by referencing ```dummy-field-for-backpack::path.to.view```.

Also in ```/src/``` you'll notice ```src/resources/views/fields/toggle.blade.php```. This is the example field, which you can rename and use to start coding you field. Or if you already have your field ready, you can just delete this file, and copy-paste the finished blade file from your project

<a name="step-3-put-it-on-github"></a>
## Step 3. Put it on GitHub

```
# make sure you replace the below with your actual vendor name and package name
cd packages/my-company/dummy-field-for-backpack/
git add .
git commit -m "turned the skeleton package into dummy-field package"
```

Create [a new GitHub repository](https://github.com/new). Then get the Git URL the same way you did for the Toggle package, from the green "Clone or Download" button. With that Git URL:

```
# remove the old origin (pointing to the toggle package)
git remote rm origin

# add a remote pointing to YOUR github repo
git remote add origin git@github.com:yourusername/yourrepository.git

# refresh the new origin remote everywhere
git config master.remote origin
git config master.merge refs/heads/master

# push your code to your github repo
git push -u origin master

# tag your release as 1.0.0
git tag -a 1.0.0 -m 'First version'

# push your tags to the Repo - this is very important to Packagist;
git push --tags
```

You might not have used git tags until now. Tags are the way you will version your package, so it's important you do it. For every new version, you need to:
- write your changes inside the ```changelog.md``` file, so people can easily see what's new;
- tag your release with the proper tag, so that Packagist will know you've pushed a new version;

---

<a name="step-4-put-it-on-packagist"></a>
## Step 4. Put it on Packagist

On [Packagist.org](http://packagist.org), create an account if you don't have one already, then click "Submit package" in the top-right corner. Enter your package's GitHub URL and click Check. If any errors occur, follow the onscreen instructions.

When you're done, you'll be taken to your Packagist page, where you'll probably get a notice like this:

>This package is not auto-updated. Please set up the [GitHub Service Hook](https://packagist.org/profile/) for Packagist so that it gets updated whenever you push!

Let's take care of that. Click [that link](https://packagist.org/profile/), click "Show API Token", copy it and go to _your package's GitHub page_, in ```Settings / Webhooks & Services / Add a new service```. Search for Packagist. Enter your username and the token and hit Submit. Your error in Packagist should disappear in 5–10 minutes.

Congrats! You now have a working package online. You can now require it with composer.


---

<a name="step-5-install-it"></a>
## Step 5. Install it

Since your package is now online, you can now install it using composer.

```bash
# go to the root of you Laravel app
cd ../../..

# delete the folder from packages
rm -r packages

composer require my-company/dummy-field-for-backpack --prefer-source
```

Notice we've installed it using the ```prefer-source``` flag. This will actually _clone the git repo_ inside your ```vendor/myvendor/mypackage``` directory. So you can do: 

```bash
cd /vendor/my-company/dummy-field-for-backpack
git checkout master
```

**That's it. Your package is online and installable!** You have most of the knowledge needed to build and maintain a PHP package.


<a name="step-6-double-and-triple-check"></a>
## Step 6. Double-check, then triple-check

### Are you sure it's working?
After your package is online and ready to use, double-check that it's working well. Then triple-check. 

### Your README is your home page
Afterwards go to your README file again, and make sure it's the best it can be. Remember, your README file is the first thing people see when they find your package. If it's not appealing, they won't use it. If it doesn't do a good job of explaining how to use it, they won't use it. Take this seriously. This is where A LOT of packages go wrong - the authors do not spend the right amount of time on their README page.

IMPORTANT. Make sure your README has a nice screenshot of the functionality you're offering. The easier it is for a developer to see the benefit of using your package, the more likely it is for them to install it. There's a trick in uploading images to GitHub, then using them in your README file. Go to your package's GitHub page, and add an issue. In that issue's body, drag&drop the screenshot image. GitHub will upload it, and give it an URL. Copy-paste that URL, submit the issue (you can also already close the issue), then use that image URL inside your README file. Boom! Free image hosting.

By now you should have made some changes to your files, inside your ```vendor/my-company/dummy-field-for-backpack``` directory.

After each change you want to publish, you should:
1. Write about that change in your ```CHANGELOG.md``` file. Increment the version sticking to SEMVER.

2. Commit and push your changes, remembering to also create a new tag with the version. 
```bash
git pull origin master
git add .
git commit -am "fixes #14189 - some problem or feature with an id from Github"
git tag 1.0.1
git push origin master --tags
```

<a name="step-7-feedback-and-promotion"></a>
## Step 7. Feedback and Promotion

Congratulations on your new Backpack addon! 

To get feedback, ask people to try it on:
- [our subreddit](https://www.reddit.com/r/BackpackForLaravel/)
- [our Gitter chatroom](https://gitter.im/BackpackForLaravel/Lobby)

Make sure you write something nice, so people are interested to click.

After you've got some feedback, and a few users have installed your package and everything seems fine, time to promote it big time:
- post it to [laravel-news.com/links](https://laravel-news.com/links)
- show it off in the [Laracasts forum](https://laracasts.com/discuss)
- ask people to try it in [laravel.io](https://laravel.io/forum)


## Extra Credits

If you're building a bigger package, with one CRUD or more, we recommend you follow the simple folder structure we use across all Backpack packages. Our rule of thumb: **organize your ```src``` folder like it were a Laravel application**. We do this because it's easier for developers to understand how the package works, and it makes it easy to copy-paste the code inside their apps and modify, for complicated use cases. That way, add-ons can be kept super-simple, with everybody adding functionality _in their own apps_. Example folder structure:
- [src]
    - [app]
      - [Http]
      - [Models]
      - [Requests]
    - [database]
      - [migrations]
      - [seeds]
    - [routes]
    - AddonServiceProvider.php
- [tests]
- composer.json
- CHANGELOG.md
- LICENSE.md
- README.md

For extra reading credits, these are the resources we've used to create this guide:
- https://laravel.com/docs/packages
- https://laracasts.com/discuss/channels/tips/developing-your-packages-in-laravel-5
- https://github.com/jaiwalker/setup-laravel5-package
- https://github.com/Jeroen-G/laravel-packager
- https://laracasts.com/lessons/package-development-101
