<?php
/*
 +------------------------------------------------------------------------+
 | Plinker-RPC PHP                                                        |
 +------------------------------------------------------------------------+
 | Copyright (c)2017-2018 (https://github.com/plinker-rpc/core)           |
 +------------------------------------------------------------------------+
 | This source file is subject to MIT License                             |
 | that is bundled with this package in the file LICENSE.                 |
 |                                                                        |
 | If you did not receive a copy of the license and are unable to         |
 | obtain it through the world-wide-web, please send an email             |
 | to license@cherone.co.uk so we can send you a copy immediately.        |
 +------------------------------------------------------------------------+
 | Authors: Lawrence Cherone <lawrence@cherone.co.uk>                     |
 +------------------------------------------------------------------------+
 */

namespace Plinker\Core\Endpoint;

/**
 * Plinker\Core\Endpoint\Test
 */
final class Test
{
    /**
     * Class construct
     *
     * @param  array $config - Config array which holds object configuration
     * @return void
     */
    public function __construct($config)
    {
        $this->config = $config;
    }

    /**
     *
     */
    public function this()
    {
        return $this;
    }
}
