**Plinker-RPC - Core**
=========

[![Build Status](https://travis-ci.org/plinker-rpc/core.svg?branch=master)](https://travis-ci.org/plinker-rpc/core)
[![StyleCI](https://styleci.io/repos/103975908/shield?branch=master)](https://styleci.io/repos/103975908)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/plinker-rpc/core/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/plinker-rpc/core/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/plinker-rpc/core/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/plinker-rpc/core/code-structure/master/code-coverage)
[![Packagist Version](https://img.shields.io/packagist/v/plinker/core.svg?style=flat-square)](https://github.com/plinker-rpc/core/releases)
[![Packagist Downloads](https://img.shields.io/packagist/dt/plinker/core.svg?style=flat-square)](https://packagist.org/packages/plinker/core)


**Version 3 - Does not work with version 2 components, they will be converted shortly.**

Plinker PHP RPC client/server makes it really easy to link and execute PHP components on remote systems, while maintaining the feel of a local method call.

**New changes in version 3 include:**

 - Now compaible with [PHP extension](https://github.com/plinker-rpc/php-ext).
 - Built-in core components and info method added so components can be discovered.
 - Only one client instance is now needed, made use of __get() to dynamically set component.
 - User defined components/classes, so you can call your own code.
 - Encryption always.


## Install

Require this package with composer using the following command:

``` bash
$ composer require plinker/core
```


### Initialize Client

Creating a client instance is done as follows:


    <?php
    require 'vendor/autoload.php';

    /**
     * Initialize plinker client.
     *
     * @param string $server
     * @param string $config
     */
    $client = new \Plinker\Client(
        'http://example.com/server.php',
        [
            'secret' => 'a secret password'
        ]
    );
    
    echo '<pre>'.print_r($client->test->this(), true).'</pre>';


### Initialize Server

Creating a server listener is done as follows:

**Optional features:**

 - Set a secret, which all clients will require. 
 - Lock down to specific client IP addresses for addtional security.
 - You can also define your own classes in the `classes` array then access like above `$client->class->method()`.
 - You can define addtional key values for database connections etc, or you could pass the parameters through the client connection.

<!-- after list code block fix -->

    <?php
    require 'vendor/autoload.php';

    /**
     * Initialize plinker server.
     */
    if (isset($_SERVER['HTTP_PLINKER'])) {
        // init plinker server
        (new \Plinker\Server([
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
                // ...
            ]
        ]))->listen();
    }
    
    
## Testing

``` bash
$ composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING) for details.

## Security

If you discover any security related issues, please contact me via https://cherone.co.uk instead of using the issue tracker.

## Credits

- [Lawrence Cherone](https://github.com/lcherone)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.

See [organisations page](https://github.com/plinker-rpc) for additional components.
