### video [PRO]

Allow the user to paste a YouTube/Vimeo link. That will get the video information with JavaScript and store it as a JSON in the database.

Field definition:
```php
CRUD::field([   // URL
    'name'            => 'video',
    'label'           => 'Link to video file on YouTube or Vimeo',
    'type'            => 'video',
    'youtube_api_key' => 'AIzaSycLRoVwovRmbIf_BH3X12IcTCudAErRlCE',
]);
```

An entry stored in the database will look like this:
```
$video = {
    id: 234324,
    title: 'my video title',
    image: 'https://provider.com/image.jpg',
    url: 'http://provider.com/video',
    provider: 'youtube'
}
```

So you should use [attribute casting](https://mattstauffer.com/blog/laravel-5.0-eloquent-attribute-casting) in your model, to cast the video as ```array``` or ```object```.

Vimeo does not require an API key to query their DB, but YouTube does, even though their free quota is generous. You can get a free YouTube API Key inside [Google Developers Console](https://console.developers.google.com/) ([video tutorial here](https://www.youtube.com/watch?v=pP4zvduVAqo)). Please DO NOT use our API Key - create your own. The key above is there just for your convenience, to try out the field. As soon as you decide to use this field type, create an API Key and use _your_ API Key. Our key hits its ceiling every month, so if you use our key most of the time it won't work.
