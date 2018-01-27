<?php

require 'vendor/autoload.php';

$client = new \Plinker\Client(
    'http://example.com/server.php',
    [
        'secret' => 'a secret password'
    ]
);

print_r($client);

//echo '<pre>'.print_r($client->testclass->foobar(), true).'</pre>';