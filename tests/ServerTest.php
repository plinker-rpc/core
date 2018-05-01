<?php

namespace Plinker\Core;

use PHPUnit\Framework\TestCase;

class ServerTest extends TestCase
{
    /**
     * @var class instance
     */
    private $plinker;

    /**
     * @var class config
     */
    private $plinker_config;

    /**
     * setup
     */
    public function setUp()
    {
        //
        $this->plinker_config = [
            'plinker' => [
                'server' => 'http://127.0.0.1/server.php',
                'secret' => 'a secret password'
            ]
        ];
    }
    
    /**
     *
     */
    public function testServerConstruct()
    {
        // check class properties
        $this->assertClassHasAttribute('input', '\Plinker\Core\Server');
        $this->assertClassHasAttribute('config', '\Plinker\Core\Server');
        $this->assertClassHasAttribute('signer', '\Plinker\Core\Server');
        $this->assertClassHasAttribute('response', '\Plinker\Core\Server');
        
        // init client
        $this->server = new \Plinker\Core\Server([
            'secret' => $this->plinker_config['plinker']['secret'],
            'allowed_ips' => [
                //'127.0.0.1'
            ],
            'classes' => [
                'demo' => [
                    // path to file
                    'user_classes/demo.php',
                    // addtional key/values
                    [
                        'key' => 'value'
                    ]
                ],
                // ...
            ]
        ]);

        // check client instance
        $this->assertInstanceOf('\Plinker\Core\Server', $this->server);
        
        // check types
        $this->assertInternalType('array', \PHPUnit\Framework\Assert::readAttribute($this->server, 'config'));
    }
    
    /**
     *
     */
    public function testListen()
    {
        // init client
        $this->server = new \Plinker\Core\Server([
            'secret' => $this->plinker_config['plinker']['secret'],
            'allowed_ips' => [
                //'127.0.0.1'
            ],
            'classes' => [
                'demo' => [
                    // path to file
                    'user_classes/demo.php',
                    // addtional key/values
                    [
                        'key' => 'value'
                    ]
                ],
                // ...
            ]
        ]);
        
        $response = $this->server->listen();
        
        print_r($response);
        
        //
        $this->assertTrue(!empty($response));

        //
        $this->assertTrue(is_array(json_decode($response, true)));
    }
}
