### phone

The phone column will output the phone number from the database (truncated to 254 characters if needed), with a ```tel:``` link so that users on mobile can click them to call (or with Skype or similar browser extensions). Its definition is:
```php
[
   'name'     => 'phone', // The db column name
   'label'    => 'Phone number', // Table column heading
   'type'     => 'phone',
   // 'limit' => 10, // if you want to truncate the phone number to a different number of characters
],
```
