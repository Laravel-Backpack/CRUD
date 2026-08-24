### email

The email column will output the email address in the database (truncated to 254 characters if needed), with a ```mailto:``` link towards the full email. Its definition is:
```php
[
   'name'  => 'email', // The db column name
   'label' => 'Email Address', // Table column heading
   'type'  => 'email',
   // 'limit' => 500, // if you want to truncate the text to a different number of characters
],
```

>**Search** - the email column **is searchable by default**: the List operation's search bar will look for the search term inside the email database column, using a ```LIKE``` query. If you want to customize what the search does (custom ```searchLogic```), disable it, or make other column types searchable, check out the [Search documentation](search).
