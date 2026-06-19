### model_function_attribute

If the function you're trying to use returns an object, not a string, you can use the model_function_attribute column, which will output the attribute on the function result. Its definition is:
```php
[
   'name'  => 'url',
   'label' => 'URL', // Table column heading
   'type'  => 'model_function_attribute',
   'function_name' => 'getSlugWithLink', // the method in your Model
   // 'function_parameters' => [$one, $two], // pass one/more parameters to that method
   'attribute' => 'route',
   // 'limit' => 100, // Limit the number of characters shown
   // 'escaped' => false, // echo using {!! !!} instead of {{ }}, in order to render HTML
],
```
