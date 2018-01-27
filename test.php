<?php
error_reporting(E_ALL);
ini_set('diaply_errors', '1');

require 'src/lib/Signer.php';
require 'src/lib/Curl.php';
require 'src/Client.php';

$client = new \Plinker\Core\Client(
    'http://plinker.free.lxd.systems/vendor/plinker/core/server.php',
    [
        'secret' => 'a secret password'
    ]
);

header('Content-type: text/plain');

print_r($client);

echo '<pre>'.print_r($client->test->this(), true).'</pre>';
