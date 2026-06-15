# Breadcrumbs

---

<a name="about"></a>
## About

Breadcrumbs show a path to the current page in the top-right corner of the screen, and can be a useful part of the UI for deeply nested admin panels.

Breadcrumbs are loaded in the default layout _before_ the ```header``` section.

<a name="enable-disable-breadcrumbs"></a>
## Enable / Disable Breadcrumbs

You can hide or show breadcrumbs on ALL of your admin panel pages by changing a boolean variable in your ```config/backpack/ui.php``` to change it for any active Backpack theme, or in that theme's config file:

```php
    // Show / hide breadcrumbs on admin panel pages.
    'breadcrumbs' => true,
```

<a name="how-breadcrumbs-work"></a>
## How Breadcrumbs Work

The ```inc.breadcrumbs``` view will show all breadcrumbs from the ```$breadcrumbs``` variable, if it's present on the page. The ```$breadcrumbs``` variable should be a simple associative array ```[ $label1 => $link1, $label2 => $link2 ]```. Examples:

```php
  $breadcrumbs = [
      trans('backpack::crud.admin') => backpack_url('dashboard'),
      trans('backpack::base.dashboard') => false,
  ];

  $breadcrumbs = [
      trans('backpack::crud.admin') => backpack_url('dashboard'),
      trans('backpack::base.dashboard') => false,
  ];

  $breadcrumbs = [
      'Admin' => route('dashboard'),
      'Dashboard' => false,
  ];
```

Notice the last item should NOT have a link, it should be ```false```.

<a name="how-to-define-breadcrumbs"></a>
## How to Define Breadcrumbs

<a href="define-breadcrumbs-in-the-controller"></a>
### Define Breadcrumbs Inside the Controller

Make sure a ```$breadcrumbs``` variable is present inside your views:
```php
    /**
     * Show the admin dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function dashboard()
    {
        $this->data['title'] = trans('backpack::base.dashboard'); // set the page title
        $this->data['breadcrumbs'] = [
            trans('backpack::crud.admin') => backpack_url('dashboard'),
            trans('backpack::base.dashboard') => false,
        ];

        return view('backpack::dashboard', $this->data);
    }
```

<a href="define-breadcrumbs-in-the-view"></a>
### Define Breadcrumbs Inside the View

Make sure a ```$breadcrumbs``` variable is present:

```php
@extends('backpack::layout')

@php
  $breadcrumbs = [
      'Admin' => backpack_url('dashboard'),
      'Dashboard' => false,
  ];
@endphp

@section('content')
    some other content here
@endsection
```
