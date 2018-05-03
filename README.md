**PlinkerRPC - Core**
=========

[![Build Status](https://travis-ci.org/plinker-rpc/core.svg?branch=master)](https://travis-ci.org/plinker-rpc/core)
[![StyleCI](https://styleci.io/repos/103975908/shield?branch=master)](https://styleci.io/repos/103975908)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/plinker-rpc/core/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/plinker-rpc/core/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/plinker-rpc/core/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/plinker-rpc/core/code-structure/master/code-coverage)
[![Packagist Version](https://img.shields.io/packagist/v/plinker/core.svg?style=flat-square)](https://github.com/plinker-rpc/core/releases)
[![Packagist Downloads](https://img.shields.io/packagist/dt/plinker/core.svg?style=flat-square)](https://packagist.org/packages/plinker/core)

Plinker PHP RPC client/server makes it really easy to link and execute generic PHP components on remote systems, while maintaining the feel of a local method call.

**Docs:** [https://plinker-rpc.github.io/development/core](https://plinker-rpc.github.io/development/core)

**New changes in version 3 include:**

 - Now compaible with [PHP extension](https://github.com/plinker-rpc/php-ext).
 - Built-in core components and info method added so components can be discovered.
 - Only one client instance is now needed, made use of __get() to dynamically set component.
 - User defined components/classes, so you can call your own code.
 - Both request and response is encrypted and signed.

## Install

Require this package with composer using the following command:

``` bash
$ composer require plinker/core
```

### Additional Setup

This component does not require any additional setup.

## Client

Creating a client instance is done as follows:


    <?php
    require 'vendor/autoload.php';

    /**
     * Initialize plinker client.
     *
     * @param string $server - URL to server listener.
     * @param string $config - server secret, and/or a additional component data
     */
    $client = new \Plinker\Core\Client(
        'http://example.com/server.php',
        [
            'secret' => 'a secret password',
        ]
    );
    

## Server

Creating a server listener is done as follows:

**Optional features:**

 - Set a secret, which all clients will require. 
 - Lock down to specific client IP addresses for addtional security.
 - Define your own classes in the `classes` array then access like above `$client->class->method()`, which can interface out of scope components or composer packages.
 - Define addtional key values for database connections etc, or you could pass the parameters through the client connection.

<!-- after list code block fix -->

    <?php
    require 'vendor/autoload.php';

    /**
     * Initialize plinker server.
     */
    if (isset($_SERVER['HTTP_PLINKER'])) {
        // init plinker server
        echo (new \Plinker\Server([
            'secret' => 'a secret password',
            'allowed_ips' => [
                '127.0.0.1'
            ],
            'classes' => [
                'test' => [
                    // path to file
                    'classes/test.php',
                    // addtional key/values
                    [
                        'key' => 'value'
                    ]
                ],
                // you can use namespaced classes
                'Foo\\Demo' => [
                    // path to file
                    'some_class/demo.php',
                    // addtional key/values
                    [
                        'key' => 'value'
                    ]
                ],
                // ...
            ]
        ]))->listen();
    }
    

## Methods

Once setup, you call the class though its namespace to its method.


### Info

The info method returns defined endpoint methods and their parameters.

**Call**


```
$result = $client->info();
```

**Response**
```
Array
(
    [class] => Array
        (
            [Foo\Demo] => Array
                (
                    [config] => Array
                        (
                            [key] => value
                        )

                    [methods] => Array
                        (
                            [config] => Array
                                (
                                )

                            [this] => Array
                                (
                                )

                            [test] => Array
                                (
                                    [0] => x
                                    [1] => y
                                )

                        )

                )

        )

)
```

## Testing

``` bash
$ composer test
```

## Contributing

Please see [CONTRIBUTING](https://github.com/plinker-rpc/core/blob/master/CONTRIBUTING) for details.

## Security

If you discover any security related issues, please contact me via [https://cherone.co.uk](https://cherone.co.uk) instead of using the issue tracker.

## Credits

- [Lawrence Cherone](https://github.com/lcherone)
- [All Contributors](https://github.com/plinker-rpc/core/graphs/contributors)


## Development Encouragement

If you use this code and make money from it and want to show your appreciation,
please feel free to make a donation [https://www.paypal.me/lcherone](https://www.paypal.me/lcherone), thanks.

## Sponsors

Get your company or name listed here.

## License

The MIT License (MIT). Please see [License File](https://github.com/plinker-rpc/core/blob/master/LICENSE) for more information.

See [organisations page](https://github.com/plinker-rpc) for additional components.
