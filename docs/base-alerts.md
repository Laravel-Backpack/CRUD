# Alerts

---

<a name="about"></a>
## About

When building custom functionality, you'll probably need to give feedback to the admin for something that happened in the background. You can easily do that in Backpack by triggering alerts (aka notification bubbles, aka notifications). You can do that both from JavaScript and from PHP - and they will look exactly the same. In fact, Backpack operations use this same API. By using Alerts in your custom pages too, you make sure your alerts look the same across your admin panel - and your UI will be consistent.


<a name="notification-bubbles-in-php"></a>
### Triggering Alerts in PHP

We use [prologue/alerts](https://github.com/prologuephp/alerts#adding-alerts-through-alert-levels) to trigger notifications. Check out its documentation for the entire syntax. 

Most of the time, all you need to do is trigger a notification of a certain type, or trigger a notification using flash data, so that it shows after a redirect:

```php
public function foo() 
{
  \Alert::add('info', 'This is a blue bubble.');
  \Alert::add('warning', 'This is a yellow/orange bubble.');
  \Alert::add('error', 'This is a red bubble.');
  \Alert::add('success', '<strong>Got it</strong><br>This is HTML in a green bubble.');
  \Alert::add('primary', 'This is a dark blue bubble.');
  \Alert::add('secondary', 'This is a grey bubble.');
  \Alert::add('light', 'This is a light grey bubble.');
  \Alert::add('dark', 'This is a black bubble.');
  
  // the layout will make sure to show your notifications
  return view('some_view');
}

public function bar()
{
  \Alert::add('success', 'You have successfully logged in')->flash();
 
  // please note the above flash() method; this will store your notification in a session variable, so that you can redirect to another page, but the notification will still be shown (on the page you redirect to)
  return Redirect::to('some-url');
}
```

<a name="notification-bubbles-in-javascript"></a>
### Triggering Alerts in JavaScript

We use [Noty](https://ned.im/noty/#/) to show notifications from JavaScript, on the same page. Check out its docs for detailed use. Most of the time you'll only need to do:

```php
new Noty({
    type: "success",
    text: 'Some notification text',
}).show();

// available types: 
// - success
// - info
// - warning/notice
// - error/danger
// - primary
// - secondary
// - dark
// - light
```