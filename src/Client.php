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

namespace Plinker\Core;

/**
 * Plinker\Core\Client
 */
final class Client
{
    /**
     * @var
     */
    private $component;

    /**
     * @var
     */
    private $response;

    /**
     * @var
     */
    private $config;

    /**
     * @var
     */
    private $curl;

    /**
     * @var
     */
    private $signer;

    /**
     * Class construct
     *
     * @param  string $server  - server enpoint url
     * @param  array  $config  - config array which holds object configuration
     * @return void
     */
    public function __construct($server, array $config = [])
    {
        $this->config = array_merge([
            "server" => $server,
            "secret" => null
        ], $config);

        // check and set client timeout
        if (!isset($this->config["timeout"]) || !is_numeric($this->config["timeout"])) {
            $this->config["timeout"] = 10;
        }
    }

    /**
     * Magic getter method, which sets component
     *
     * @param  string $component
     * @return object
     */
    public function __get($component)
    {
        $this->component = $component;

        return $this;
    }

    /**
     * Magic caller method, which calls component
     *
     * @param string $action
     * @param array  $params
     */
    public function __call($action, $params)
    {
        // load curl
        if (!$this->curl) {
            $this->curl = new Lib\Curl($this->config);
        }

        // load signer
        if (!$this->signer) {
            $this->signer = new Lib\Signer($this->config);
        }

        // change params array into numeric
        $params = array_values($params);

        // unset local private key
        unset($this->config["plinker"]["private_key"]);

        // encode payload
        $payload = $this->signer->encode([
            "component" => $this->component,
            "config" => $this->config,
            "action" => $action,
            "params" => $params
        ]);

        // post request to plinker server
        $this->response = $this->curl->post($this->config["server"], $payload, [
            "PLINKER: ".$payload["token"]
        ]);

        return unserialize($this->response);
    }
}
