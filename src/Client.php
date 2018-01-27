<?php

namespace Plinker\Core;

use Plinker\Core\Lib\Curl;
use Plinker\Core\Lib\Signer;

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
     * @param  string server  - server enpoint url
     * @param  array  config  - config array which holds object configuration
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
     * @param  string method  - component name
     * @return <Plinker\Client>
     */
    public function __get($method)
    {
        $this->component = $method;

        return $this;
    }

    /**
     * Magic caller method, which calls component
     *
     * @param string action
     * @param array  params
     */
    public function __call($action, array $params)
    {
        if (!is_scalar($action)) {
            throw new \Exception("Method name has no scalar value");
        }
        
        if (!is_array($params)) {
            throw new \Exception("Params must be given as array");
        }
        
        // load curl
        if (!$this->curl) {
            $this->curl = new Curl($this->config);
        }
        
        // load signer
        if (!$this->signer) {
            $this->signer = new Signer($this->config);
        }

        // change params array into numeric
        $params = array_values($params);

        // unset local private key
        //unset(this->config["plinker"]["private_key"]);

        $payload = $this->signer->encode([
            "component" => $this->component,
            "config" => $this->config,
            "action" => $action,
            "params" => $params
        ]);

        $this->response = $this->curl->post($this->config["server"], $payload, [
            "PLINKER: ".$payload["token"]
        ]);
        
        print_r($this->response);
        
    
        // unserialize data
        return unserialize($this->response);
    }
}
