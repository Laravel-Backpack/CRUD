### google_map [PRO]

Shows a map and allows the user to navigate and select a position on that map (using the Google Places API). The field stores the latitude, longitude and the address string as a JSON in the database ( eg. `{lat: 123, lng: 456, formatted_address: 'Lisbon, Portugal'}`). If you want to save the info in separate db columns, continue reading below.

```php
CRUD::field([
    'name' => 'location',
    'type' => 'google_map',
    // optionals
    'map_options' => [
        'default_lat' => 123,
        'default_lng' => 456,
        'locate' => false, // when false, only a map is displayed. No value for submition.
        'height' => 400 // in pixels
    ]
]);
```

Using Google Places API is dependent on using an API Key. Please [get an API key](https://console.cloud.google.com/apis/credentials) - you do have to configure billing, but you qualify for $200/mo free usage, which covers most use cases. Then copy-paste that key as your ```services.google_places.key``` value. So inside your ```config/services.php``` please add the items below:

```php
'google_places' => [
    'key' => 'the-key-you-got-from-google-places'
],
```

**IMPORTANT NOTE**: Your key needs access to the following APIS:
- Maps JavaScript API;
- Places API;
- Geocoding API.

While developing you can use an "unrestricted key" (no restrictions for where the key is used), but for production you should use a separate key, and **MAKE SURE** you restrict the usage of that key to your own domain. 

**How to save in multiple inputs?**

There are cases where you rather save the information on separate inputs in the database. In that scenario you should use [Laravel mutators and accessors](https://laravel.com/docs/10.x/eloquent-mutators). Using the same field as previously shown (**field name is `location`**), and having `latitude`, `longitude`, `full_address` as the database columns, we can save and retrieve them separately too:
```php

//add all the fields to model fillable property, including the one that we are not going to save (location in the example)
$fillable = ['location', 'latitude', 'longitude', 'full_address'];

//
protected function location(): \Illuminate\Database\Eloquent\Casts\Attribute
{
    return \Illuminate\Database\Eloquent\Casts\Attribute::make(
        get: function($value, $attributes) {
            return json_encode([
            'lat' => $attributes['lat'],
            'lng' => $attributes['lng'],
            'formatted_address' => $attributes['full_address'] ?? ''
            ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_THROW_ON_ERROR);
        },
        set: function($value) {
            $location = json_decode($value);
            return [
                'lat' => $location->lat,
                'lng' => $location->lng,
                'full_address' => $location->formatted_address ?? ''
            ];
        }
    );
}

```
