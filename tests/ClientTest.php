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
        //
        $this->plinker_config = [
            'plinker' => [
                'server' => 'https://127.0.0.1/server.php',
                'secret' => 'a secret password'
            ]
        ];
    }
    
    /**
     *
     */
    public function testClientConstruct()
    {
        // check class properties
        $this->assertClassHasAttribute('component', '\Plinker\Core\Client');
        $this->assertClassHasAttribute('config', '\Plinker\Core\Client');
        $this->assertClassHasAttribute('curl', '\Plinker\Core\Client');
        $this->assertClassHasAttribute('signer', '\Plinker\Core\Client');
        
        // init client
        $this->plinker = new \Plinker\Core\Client(
            $this->plinker_config['plinker']['server'],
            [
                'secret' => $this->plinker_config['plinker']['secret']
            ]
        );

        // check client instance
        $this->assertInstanceOf('\Plinker\Core\Client', $this->plinker);
        
        // check types
        $this->assertInternalType('array', \PHPUnit\Framework\Assert::readAttribute($this->plinker, 'config'));
    }
    
    /**
     * 
     */
    public function testMagicGetterReturnsInstanceOfPlinker()
    {
        // init client
        $this->plinker = new \Plinker\Core\Client(
            $this->plinker_config['plinker']['server'],
            [
                'secret' => $this->plinker_config['plinker']['secret']
            ]
        );
        
        $this->assertInstanceOf('\Plinker\Core\Client', $this->plinker->random);
    }
    
    /**
     *
     */
    public function testMagicCallerInitialisesCurl()
    {
        // init client
        $this->plinker = new \Plinker\Core\Client(
            $this->plinker_config['plinker']['server'],
            [
                'secret' => $this->plinker_config['plinker']['secret']
            ]
        );
        
        // will return error
        $result = $this->plinker->info();

        //
        $this->assertEquals(0, $result['code']);
    }
    
    /**
     *
     */
    /*
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
    */

    /**
     *
     */
    /*
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
    */
    /**
     *
     */
    /*public function testCallComponentMethod()
    {
        $this->expected_params = ['a', 'b', 'c'];

        // test __call
        $_call = $this->plinker->__call('componentMethod', $this->expected_params);
        $this->assertInternalType('array', $_call);
        $this->assertEquals($this->expected_params, $_call);

        // test normal - (argument unpacking)
        $_normal = $this->plinker->componentMethod(...$this->expected_params);
        $this->assertInternalType('array', $_normal);
        $this->assertEquals($this->expected_params, $_normal);

        // now both should be the same
        $this->assertEquals($_call, $_normal);

        // test normal - (normal arguments)
        $_normal = $this->plinker->componentMethod(
            $this->expected_params[0],
            $this->expected_params[1],
            $this->expected_params[2]
        );
        $this->assertInternalType('array', $_normal);
        $this->assertEquals($this->expected_params, $_normal);

        // now both should be the same
        $this->assertEquals($_call, $_normal);
    }*/
    
    /*
    public function testCallEndpoint()
    {
        $response = new \stdClass();
        $response->body = serialize(\PHPUnit\Framework\Assert::readAttribute($this->plinker, 'signer')->encode([
            'response' => [],
        ]));

        // Create a stub for the SomeClass class.
        $stub = $this->getMockBuilder('\Plinker\Core\Client')
                     ->disableOriginalConstructor()
                     ->disableOriginalClone()
                     ->disableArgumentCloning()
                     ->getMock();

        // Configure the stub.
        $stub->method('callEndpoint')
             ->willReturn($response);

        print_r($stub->callEndpoint('encoded', []));

        // Calling $stub->doSomething() will now return
        // 'foo'.
        $this->assertEquals($response, $stub->callEndpoint('encoded', []));
    }
*/
    /**
     *
     */
    /*
    public function testHttpError()
    {
        $this->expected_params = ['a', 'b', 'c'];

        // set test fail condition - http_empty_response
        putenv('TEST_CONDITION=http_empty_response');

        try {
            $_failed = $this->plinker->componentMethod(...$this->expected_params);
        } catch (\Exception $e) {
            $this->assertInstanceOf('Exception', $e);
            $this->assertEquals('Could not unserialize response: Testing fail', $e->getMessage());
        }

        // set test fail condition - http_invalid_response
        putenv('TEST_CONDITION=http_invalid_response');

        try {
            $_failed = $this->plinker->componentMethod(...$this->expected_params);
        } catch (\Exception $e) {
            $this->assertInstanceOf('Exception', $e);
            $this->assertEquals('Could not unserialize response: Invalid text response', $e->getMessage());
        }

        // set test fail condition - http_slow_packet_response
        putenv('TEST_CONDITION=http_slow_packet_response');

        try {
            $_failed = $this->plinker->componentMethod(...$this->expected_params);
        } catch (\Exception $e) {
            $this->assertInstanceOf('Exception', $e);
            $this->assertEquals('Response timing packet check failed', $e->getMessage());
        }
    }
    */
    
    /**
     *
     */
    /*
    public function testDataError()
    {
        $this->expected_params = ['a', 'b', 'c'];

        // set test fail condition - http_slow_data_response
        putenv('TEST_CONDITION=http_slow_data_response');

        try {
            $_failed = $this->plinker->componentMethod(...$this->expected_params);
        } catch (\Exception $e) {
            $this->assertInstanceOf('Exception', $e);
            $this->assertEquals('Response timing data check failed', $e->getMessage());
        }

        // set test fail condition - data_empty_response
        putenv('TEST_CONDITION=data_empty_response');

        $_failed = $this->plinker->componentMethod(...$this->expected_params);
        $this->assertEquals('', $_failed);

        // set test fail condition - data_empty_response
        putenv('TEST_CONDITION=data_invalid_response');

        try {
            $_failed = $this->plinker->componentMethod(...$this->expected_params);
        } catch (\Exception $e) {
            $this->assertInstanceOf('Exception', $e);
            $this->assertEquals('Could not unserialize response: Response not serialized', $e->getMessage());
        }
    }
    */
    
    /**
     *
     */
    /*
    public function testResponseError()
    {
        $this->expected_params = ['a', 'b', 'c'];

        // set test fail condition - data_error_response
        putenv('TEST_CONDITION=data_error_response');

        try {
            $_failed = $this->plinker->componentMethod(...$this->expected_params);
        } catch (\Exception $e) {
            $this->assertInstanceOf('Exception', $e);
            $this->assertEquals('Error from component', $e->getMessage());
        }
    }
    */
}
