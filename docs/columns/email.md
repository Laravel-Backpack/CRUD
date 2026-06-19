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
