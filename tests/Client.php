<?php
require 'vendor/autoload.php';

// working
$client = new \Plinker\Core\Client(
    'http://127.0.0.1/server.php',
    [
        'secret' => 'a secret password'
    ]
);

//$client->info();