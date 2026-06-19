### Livewire

Add a Livewire component to a page. If you haven't created your component yet, head to [Livewire documentation](https://livewire.laravel.com/docs/components) and create the component you want to use.

**Note Livewire v2**: Livewire v2 does not automatically inject the `@livewireScripts` and `@livewireStyles` tags. If you **are NOT using** Livewire outside of this widget you can load them here by setting `livewireAssets => true`

```php
[
    'type'        => 'livewire',
    'content'   => 'my-livewire-component', // the component name
    'parameters'      => ['user' => backpack_user(), 'param2' => 'value2'], // optional: pass parameters to the component
    'livewireAssets' => false, // optional: set true to load livewire assets in the widget
]
```

**Note:** The ```parameters``` attribute will be passed to the component on initialization, and should be present in the `mount($user, $param2)`.

##### HelloWord Example:

```php
use Livewire\Component;

class HelloWorld extends Component
{
    public $name;

    public function mount(string $name)
    {
        $this->name = $name;
    }

    public function render()
    {
        return view('livewire.hello-world');
    }
}
```

```blade
<!-- livewire/hello-world.blade.php -->
<div>
    Hello {{ $name }}
</div>
```

```php
// add the widget to the page
Widget::add()->type('livewire')->content('hello-world')->parameters(['name' => 'John Doe'])->wrapperClass('col-md-12 text-center');
```

Widget Preview:
