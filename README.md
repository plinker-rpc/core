**Plinker-RPC - Core**
=========

[![Build Status](https://travis-ci.org/plinker-rpc/core.svg?branch=master)](https://travis-ci.org/plinker-rpc/core)
[![StyleCI](https://styleci.io/repos/103975908/shield?branch=master)](https://styleci.io/repos/103975908)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/plinker-rpc/core/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/plinker-rpc/core/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/plinker-rpc/core/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/plinker-rpc/core/?branch=master)
[![Packagist Version](https://img.shields.io/packagist/v/plinker/core.svg?style=flat-square)](https://github.com/plinker-rpc/core/releases)
[![Packagist Downloads](https://img.shields.io/packagist/dt/plinker/core.svg?style=flat-square)](https://packagist.org/packages/plinker/core)


Plinker PHP RPC client/server makes it really easy to link and execute PHP component classes on remote systems, while maintaining the feel of a local method call.

Required base component which contains the client and server. (Its all you need if you just want the client).

## Install

Require this package with composer using the following command:

``` bash
$ composer require plinker/core
```


### Making a remote call.


    <?php
    require 'vendor/autoload.php';

    /**
     * Initialize plinker client.
     *
     * @param string $url to host
     * @param string $component namespace of class to interface to
     * @param string $public_key to authenticate on host
     * @param string $private_key to authenticate on host
     * @param string $config component construct config
     */
    $plink = new Plinker\Core\Client(
        'http://example.com',
        'Test\Demo',
        'username',
        'password',
        array(
            'time' => time()
        )
    );
    echo '<pre>'.print_r($plink->test(), true).'</pre>';


**then the server part...**

    <?php
    require 'vendor/autoload.php';

    /**
     * POST Server Part
     */
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $server = new Plinker\Core\Server(
            $_POST,
            'username',
            'password'
        );
        exit($server->execute());
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
