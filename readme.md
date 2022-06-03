## PHP API Client for TPLINK sg105e

###### Installation

Download the files and run composer

`composer install`

Or add this library using packagist

`composer require tisantan/php-sg105e-api-client`

###### Running example.php

```php
<?php

require_once __DIR__.'\vendor\autoload.php';

use SG105E\Client;

//Connect to the Switch
$switch = new Client('192.168.1.1','admin','password');

//Turn off the switch LED
$switch->led_control(0);
