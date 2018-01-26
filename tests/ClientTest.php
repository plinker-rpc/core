<?php

namespace Plinker\Core;

use PHPUnit\Framework\TestCase;

class ClientTest extends TestCase
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
     * Test that true does in fact equal true.
     */
    public function testTrueIsTrue()
    {
        $this->assertTrue(true);
    }

    /**
     * setup
     */
    public function setUp()
    {
        // define plinker config
        $this->plinker_config = [
            // plinker connection
            'plinker' => [
                'endpoint' => 'https://127.0.0.1/server.php',
                'public_key' => 'TestPublicKey',
                'private_key' => 'TestPrivateKey'
            ],
            // database connection
            'database' => [
                'dsn'      => 'sqlite:./.plinker/database.db',
                'host'     => '',
                'name'     => '',
                'username' => '',
                'password' => '',
                'freeze'   => false,
                'debug'    => false
            ]
        ];

        // init plinker endpoint client
        $this->plinker = new \Plinker\Core\Client(
            // where is the plinker server
            $this->plinker_config['plinker']['endpoint'],

            // component namespace to interface to
            'Test\Demo',

            // keys
            $this->plinker_config['plinker']['public_key'],
            $this->plinker_config['plinker']['private_key'],

            // construct values which you pass to the component
            $this->plinker_config
        );
    }

    /**
     *
     */
    public function testClientConstruct()
    {
        // check defined
        $this->assertClassHasAttribute('endpoint', '\Plinker\Core\Client');
        $this->assertClassHasAttribute('component', '\Plinker\Core\Client');
        $this->assertClassHasAttribute('publicKey', '\Plinker\Core\Client');
        $this->assertClassHasAttribute('privateKey', '\Plinker\Core\Client');
        $this->assertClassHasAttribute('config', '\Plinker\Core\Client');
        $this->assertClassHasAttribute('encrypt', '\Plinker\Core\Client');
        $this->assertClassHasAttribute('response', '\Plinker\Core\Client');
        $this->assertClassHasAttribute('signer', '\Plinker\Core\Client');
        
        // check client instance
        $this->assertInstanceOf('\Plinker\Core\Client', $this->plinker);
        
        // check signer class instance
        $this->assertInstanceOf(
            'Plinker\Core\Signer',
            \PHPUnit\Framework\Assert::readAttribute($this->plinker, 'signer')
        );
        
        // check keys
        // - public
        $this->assertEquals(
            hash('sha256', gmdate('h').$this->plinker_config['plinker']['public_key']),
            \PHPUnit\Framework\Assert::readAttribute($this->plinker, 'publicKey')
        );
        // - private
        $this->assertEquals(
            hash('sha256', gmdate('h').$this->plinker_config['plinker']['private_key']),
            \PHPUnit\Framework\Assert::readAttribute($this->plinker, 'privateKey')
        );
        
        // check types
        $this->assertInternalType('string', \PHPUnit\Framework\Assert::readAttribute($this->plinker, 'endpoint'));
        $this->assertInternalType('string', \PHPUnit\Framework\Assert::readAttribute($this->plinker, 'component'));
        $this->assertInternalType('string', \PHPUnit\Framework\Assert::readAttribute($this->plinker, 'publicKey'));
        $this->assertInternalType('string', \PHPUnit\Framework\Assert::readAttribute($this->plinker, 'privateKey'));
        $this->assertInternalType('array', \PHPUnit\Framework\Assert::readAttribute($this->plinker, 'config'));
        $this->assertInternalType('bool', \PHPUnit\Framework\Assert::readAttribute($this->plinker, 'encrypt'));
        $this->assertInternalType('null', \PHPUnit\Framework\Assert::readAttribute($this->plinker, 'response'));
    }
    
    /**
     *
     */
    public function testUseComponent()
    {
        // sanity
        $this->assertTrue(
          method_exists($this->plinker, 'useComponent'),
          'Go Mental! useComponent does not exist!'
        );
        
        // call new component
        $new_plinker = $this->plinker->useComponent('Foo\Bar', $this->plinker_config['plinker']);

        // check client instance
        $this->assertInstanceOf('\Plinker\Core\Client', $new_plinker);
        
        // check types
        $this->assertInternalType('string', \PHPUnit\Framework\Assert::readAttribute($new_plinker, 'component'));
        $this->assertInternalType('array', \PHPUnit\Framework\Assert::readAttribute($new_plinker, 'config'));
        $this->assertInternalType('bool', \PHPUnit\Framework\Assert::readAttribute($new_plinker, 'encrypt'));
        
        // check values
        $this->assertEquals('Foo\Bar', \PHPUnit\Framework\Assert::readAttribute($new_plinker, 'component'));
        $this->assertEquals($this->plinker_config['plinker'], \PHPUnit\Framework\Assert::readAttribute($new_plinker, 'config'));

        // check signer is still there
        $this->assertInstanceOf(
            'Plinker\Core\Signer',
            \PHPUnit\Framework\Assert::readAttribute($new_plinker, 'signer')
        );
    }
    
    /**
     *
     */
    public function testCallInvalidArgumentException()
    {
        // $action
        try {
            $this->plinker->__call([], []);
        } catch (\Exception $e) {
            $this->assertInstanceOf('InvalidArgumentException', $e);
            $this->assertEquals('Method name has no scalar value', $e->getMessage());
        }
        
        // $params
        try {
            $this->plinker->__call('', '');
        } catch (\Exception $e) {
            $this->assertInstanceOf('InvalidArgumentException', $e);
            $this->assertEquals('Arguments must be given as array', $e->getMessage());
        }
    }
    
    /**
     * 
     */
    public function testCallComponentMethod()
    {
        $expected_params = ['a', 'b', 'c'];
        
        // test __call
        $_call = $this->plinker->__call('componentMethod', $expected_params);
        $this->assertInternalType('array', $_call);
        $this->assertEquals($expected_params, $_call);
        
        // test normal - (argument unpacking)
        $_normal = $this->plinker->componentMethod(...$expected_params);
        $this->assertInternalType('array', $_normal);
        $this->assertEquals($expected_params, $_normal);
        
        // now both should be the same
        $this->assertEquals($_call, $_normal);
        
        // test normal - (normal arguments)
        $_normal = $this->plinker->componentMethod(
            $expected_params[0],
            $expected_params[1],
            $expected_params[2]
        );
        $this->assertInternalType('array', $_normal);
        $this->assertEquals($expected_params, $_normal);
        
        // now both should be the same
        $this->assertEquals($_call, $_normal);
    }
}
