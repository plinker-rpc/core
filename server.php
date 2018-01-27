<?php
require 'src/lib/Signer.php';
require 'src/lib/Curl.php';
require 'src/Server.php';

/**
 * Its Plinker!
 */
if (isset($_SERVER['HTTP_PLINKER'])) {
    // init plinker server
    (new \Plinker\Core\Server([
        'secret' => 'a secret password',
        'allowed_ips' => [
           // '127.0.0.1'
        ],
        'classes' => [
            /*'test' => [
                // path to file
                'classes/test.php',
                // addtional key/values
                [
                    'key' => 'value'
                ]
            ],*/
            // ...
        ]
    ]))->listen();
}