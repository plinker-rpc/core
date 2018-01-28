<?php

require 'vendor/autoload.php';

/**
 * Its Plinker!
 */
if (isset($_SERVER['HTTP_PLINKER'])) {
    // init plinker server
    (new \Plinker\Core\Server([
        'secret' => 'a secret password',
        'allowed_ips' => [
            //'127.0.0.1'
        ],
        'classes' => [
            /*'demo' => [
                // path to file
                'user_classes/demo.php',
                // addtional key/values
                [
                    'key' => 'value'
                ]
            ],
            */
            // ...
        ]
    ]))->listen();
}