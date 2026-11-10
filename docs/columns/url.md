### url

Show a link which opens in the new tab by default.

```php
[
    'name'  => 'url',
    'type'  => 'url',
    'label' => 'URL',
    //'target' => '_blank' // let's you change link target window.
    //'element' => 'a' // let's you change the element of the link.
    //'rel' => false OR 'rel' => 'noopener' // let's you disable or change the rel attribute of the link.
],
```

Only relative URLs and `http`, `https`, `ftp`, `mailto` or `tel` URLs are used as the link. Any other URL (eg. `javascript:`) is not used as the link target.
