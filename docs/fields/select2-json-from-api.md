### select2_json_from_api [PRO]

Display a select2 that takes its values from an AJAX call.
Similar to [select2_from_ajax](#section-select2_from_ajax) above, but this one is not limited to a single entity. It can be used to select any JSON object from an API.

```php
CRUD::field([
    'label'                   => 'Airports', // Displayed column label
    'type'                    => 'select2_json_from_api',
    'name'                    => 'airports', // the column where this field will be stored
    'data_source'             => url('airports/fetch/list'), // the endpoint used by this field

    // OPTIONAL
    'delay'                   => 500, // the minimum amount of time between ajax requests when searching in the field
    'method'                  => 'POST', // route method, either GET or POST
    'placeholder'             => 'Select an airport', // placeholder for the select
    'minimum_input_length'    => 2, // minimum characters to type before querying results
    'multiple'                => true, // allow multiple selections
    'include_all_form_fields' => false, // only send the current field through AJAX (for a smaller payload if you're not using multiple chained select2s)
    
    // OPTIONAL - if the response is a list of objects (and not a simple array)
    'attribute'               => 'title', // attribute to show in the select2
    'attributes_to_store'       => ['id', 'title'], // attributes to store in the database
]);
```

You may create a controller and routes for the data_source above. Here's an example using the FetchOperation, including a search term:
Note that this example is for a non paginated response, but `select2_json_from_api` also accepts a paginated response.

```php
// use the FetchOperation to quickly create an endpoint
use \Backpack\CRUD\app\Http\Controllers\Operations\FetchOperation;
```

```php
public function fetchAirports()
{
    $types = [
        ['id' => 'OPO', 'city' => 'Porto', 'title' => 'Francisco Sá Carneiro Airport'],
        ['id' => 'LIS', 'city' => 'Lisbon', 'title' => 'Humberto Delgado Airport'],
        ['id' => 'FAO', 'city' => 'Faro', 'title' => 'Faro Airport'],
        ['id' => 'FNC', 'city' => 'Funchal', 'title' => 'Cristiano Ronaldo Airport'],
        ['id' => 'PDL', 'city' => 'Ponta Delgada', 'title' => 'João Paulo II Airport'],
    ];

    return collect($types)->filter(fn(array $value): bool => str_contains(strtolower($value['title']), strtolower(request('q'))));
}
```

A simple array with a key value pair will also work:

```php
public function fetchAirports()
{
    $types = [
        'OPO' => 'Francisco Sá Carneiro Airport',
        'LIS' => 'Humberto Delgado Airport',
        'FAO' => 'Faro Airport',
        'FNC' => 'Cristiano Ronaldo Airport',
        'PDL' => 'João Paulo II Airport',
    ];

    return collect($types)->filter(fn(string $value): bool => str_contains(strtolower($value), strtolower(request('q'))));
}
```

#### Storing only one the id in the database

A very common use case you may have is to store only the id of the selected item in the database instead of a `json` string. For those cases you can achieve that by setting the `attributes_to_store` attribute to an array with only one item, the id of the selected item and do a little trick with the model events to store the id you want, and to give the field that id in a way it understands. 

```php

CRUD::field([
    'label'               => 'Airports',
    'type'                => 'select2_json_from_api',
    'name'                => 'airport_id', // dont make your column json if not storing json on it!
     // .... the rest your field configuration
    'attribute'           => 'id', 
    'attributes_to_store' => ['id'],
    'events'              => [
        'saving' => function($entry) {
            $entry->airport_id = json_decode($entry->airport_id ?? [], true)['id'] ?? null;
        'retrieved' => function($entry) {
            $entry->airport_id = json_encode(['id' => $entry->airport_id]);
        }
    ]
]);

```
