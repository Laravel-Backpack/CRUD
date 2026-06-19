### slug [PRO]

Track the value of a different text input and turn it into a valid URL segment (aka. slug), as you type, using Javascript. Under the hood it uses [slugify](https://github.com/simov/slugify/blob/master/README.md) to generate the slug with some sensible defaults. 

```php
CRUD::field([   // Text
    'name'  => 'slug',
    'target'  => 'title', // will turn the title input into a slug
    'label' => "Slug",
    'type'  => 'slug',

    // optional
    'locale' => 'pt', // locale to use, defaults to app()->getLocale()
    'separator' => '', // separator to use
    'trim' => true, // trim whitespace
    'lower' => true, // convert to lowercase
    'strict' => true, // strip special characters except replacement
    'remove' => '/[*+~.()!:@]/g', // remove characters to match regex, defaults to null
    ]);
```

By default, it will also slugify when the target input is edited. If you want to stop that behaviour, you can do that by removing the `target` on your edit operation. For example:

```php
    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();

        // disable editing the slug when editing
        CRUD::field('slug')->target('')->attributes(['readonly' => 'readonly']);
    }
```
