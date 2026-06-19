### model_function

The model_function column will output a function on your main model. Its definition is:
```php
[
   // run a function on the CRUD model and show its return value
   'name'  => 'url',
   'label' => 'URL', // Table column heading
   'type'  => 'model_function',
   'function_name' => 'getSlugWithLink', // the method in your Model
   // 'function_parameters' => [$one, $two], // pass one/more parameters to that method
   // 'limit' => 100, // Limit the number of characters shown
   // 'escaped' => false, // echo using {!! !!} instead of {{ }}, in order to render HTML
],
```
For this example, if your model would feature this method, it would return the link to that entity:
```php
public function getSlugWithLink() {
    return '<a href="'.url($this->slug).'" target="_blank">'.$this->slug.'</a>';
}
```
