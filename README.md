# This is Whatsapp Wrapper For Whapify.id
This Package Created By Kayana Creative Team, For Adding Whatsapp Function To The Project
## Authors

- [@takuminaoya](https://github.com/takuminaoya)

## Features

- Send Message
- Get All Message by Status


## Installation

Requirement
- Laravel 10 or Above
- php 8.2 or above

Install my-project with npm

```bash
  composer require kayana/whapify
```

after it complete publish the config file
```bash
  php artisan vendor:publish kayana-whapify
```
    
## Usage/Examples

```php
use Kayana\Whapify\Whapify;

function test() {
    $whapify = new Whapify();

    $secret = "1234567890";
    $account = "yolow";

    // set credential
    $whapify->setCredential($account, $secret);

    $result = $whapify->send("+6281123456789", "test vendor original api by kayana dari oka");
    return $result
}
```


## Used By

This project is used by the following companies:

- Kayana Creative


## Important

This still in development so please use it at risk, this can only do send a single mesasage and get list of message. will add complete functionlaity in the future. while i using it in my project.

Thank you~