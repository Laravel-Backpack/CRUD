### address_google [PRO]

Use [Google Places Search](https://developers.google.com/places/web-service/search) to help users type their address faster. With the ```store_as_json``` option, it will store the address, postcode, city, country, latitude and longitude in a JSON in the database. Without it, it will just store the complete address string.

```php
CRUD::field([   // Address google
    'name'          => 'address',
    'label'         => 'Address',
    'type'          => 'address_google',
    // optional
    'store_as_json' => true
]);
```

Using Google Places API is dependent on using an API Key. Please [get an API key](https://console.cloud.google.com/apis/credentials) - you do have to configure billing, but you qualify for $200/mo free usage, which covers most use cases. Then copy-paste that key as your ```services.google_places.key``` value. 

**IMPORTANT NOTE**: Your key needs access to the following APIS:
- Maps JavaScript API;
- Places API;
- Geocoding API.

While developing you can use an "unrestricted key" (no restrictions for where the key is used), but for production you should use a separate key, and **MAKE SURE** you restrict the usage of that key to your own domain. 

So inside your ```config/services.php``` please add the items below:
```php
'google_places' => [
    'key' => 'the-key-you-got-from-google-places'
],
```
Alternatively you can set the key in your field definition, but we do **not recommend** it: 
```php
[
    'name' => 'google_field',
    'api_key' => 'the-key-you-got-from-google-places'
]
```

> **Use attribute casting.** For information stored as JSON in the database, it's recommended that you use [attribute casting](https://mattstauffer.com/blog/laravel-5.0-eloquent-attribute-casting) to ```array``` or ```object```. That way, every time you get the info from the database you'd get it in a usable format. Also, it is heavily recommended that your database column can hold a large JSON - so use `text` rather than `string` in your migration (in MySQL this translates to `text` instead of `varchar`).
