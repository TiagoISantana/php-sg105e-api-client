<?php

require_once __DIR__.'\vendor\autoload.php';

use SG105E\Client;

//Connect to the Switch
$switch = new Client('192.168.1.1','admin','password');

//Turn off the switch LED
$switch->led_control(0);

