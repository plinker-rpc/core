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
 * Plinker\Core\Server
 */
final class Server
{
    /**
     * @var
     */
    protected $input;

    /**
     * @var
     */
    protected $config;

    /**
     * @var
     */
    protected $signer;
    
    /**
     * @var
     */
    protected $response;
    

    /**
     * @const - error strings
     */
    const ERROR_IP            = "IP not in allowed list (%s)";
    const ERROR_TOKEN         = "Plinker token mismatch";
    const ERROR_DECODE        = "Failed to decode payload, check secret";
    const ERROR_USR_COMPONENT = "User component class (%s) not found";
    const ERROR_EXT_COMPONENT = "Component (%s) not implemented";
    const ERROR_ACTION        = "Component action (%s) not implemented in: %s";

    /**
     * Class construct
     *
     * @param  array $config - config array which holds object configuration
     * @return void
     */
    public function __construct($config = [])
    {
        $this->config = array_merge([
            "debug"       => false,
            "secret"      => null,
            "allowed_ips" => []
        ], $config);

        // check and set client timeout
        if (!isset($this->config["timeout"]) || !is_numeric($this->config["timeout"])) {
            $this->config["timeout"] = 10;
        }
    }
    
    /**
     * Sets inbound input value into scope
     *
     * @return void
     */
    private function setInput()
    {
        $this->input = file_get_contents("php://input");
        $this->input = gzinflate($this->input);
        $this->input = json_decode($this->input, true);
    }
    
    /**
     * Check allowed IPs
     *
     * @return bool
     */
    private function checkAllowedIp($ip, $allowed_ips = [])
    {
        return !(!empty($allowed_ips) && !in_array($ip, $allowed_ips));
    }
    
    /**
     * Execute core component
     */
    private function executeCoreComponent($component, $action)
    {
        // component is plinker endpoint
        $ns = "\\Plinker\\Core\\Endpoint\\".ucfirst($component);

        if (class_exists($ns)) {
            //
            $response = $this->execute($ns, $action);
        } else {
            if (empty($component) && $action === "info") {
                $response = $this->info();
            } else {
                $response = sprintf(Server::ERROR_EXT_COMPONENT, $component);
            }
        }
        
        return $response;
    }

    /**
     * Execute user component
     */
    private function executeUserComponent($component, $action)
    {
        $ns = null;
        
        //
        if (!empty($this->config["classes"][$component][0])) {
            $ns = $this->config["classes"][$component][0];
        }

        //
        if (!empty($this->config["classes"][$component][1])) {
            $this->config = array_merge(
                $this->config,
                $this->config["classes"][$component][1]
            );
        }

        //
        if (!empty($ns) && !file_exists($ns)) {
            $this->response = serialize([
                "error" => sprintf(Server::ERROR_USR_COMPONENT, $component),
                "code"  => 422
            ]);
            return;
        }

        //
        require($ns);

        //
        if (!class_exists($component)) {
            $this->response = serialize([
                "error" => sprintf(Server::ERROR_USR_COMPONENT, $component),
                "code"  => 422
            ]);
            return;
        }

        //
        return $this->execute($component, $action);
    }

    /**
     * Listen method
     *
     * <code>
     *  $server->listen();
     * </code>
     *
     * @return void
     */
    public function listen()
    {
        $this->setInput();

        // check allowed ips
        if (!$this->checkAllowedIp($_SERVER["REMOTE_ADDR"], $this->config["allowed_ips"])) {
            $this->response = serialize([
                "error" => sprintf(Server::ERROR_IP, $_SERVER["REMOTE_ADDR"]),
                "code" => 403
            ]);
            return;
        }

        // check header token matches data token
        if ($_SERVER["HTTP_PLINKER"] != $this->input["token"]) {
            $this->response = serialize([
                "error" => Server::ERROR_TOKEN,
                "code" => 422
            ]);
            return;
        }

        // load signer
        if (!$this->signer) {
            $this->signer = new Lib\Signer($this->config);
        }

        // decode input payload
        $this->input = $this->signer->decode($this->input);

        // could not decode payload
        if ($this->input === null) {
            $this->response = serialize([
                "error" => Server::ERROR_DECODE,
                "code" => 422
            ]);
            return;
        }

        // import user config
        $this->config = array_merge(
            $this->config,
            $this->input
        );

        // user component
        if (array_key_exists($this->input["component"], $this->config["classes"])) {
            //
            $response = $this->executeUserComponent($this->input["component"], $this->input["action"]);
        }
        // core component
        else {
            $response = $this->executeCoreComponent($this->input["component"], $this->input["action"]);
        }

        $this->response = serialize($response);
    }

    /**
     * Return info about available classes
     *
     * <code>
     *  $client->info();
     * </code>
     *
     * @return array
     */
    private function info()
    {
        $response = [
            "class" => []
        ];
        foreach ($this->config["classes"] as $key => $val) {
            //
            require($val[0]);

            $reflection = new \ReflectionClass($key);

            foreach ($reflection->getMethods() as $method) {
                if (!in_array($method->getName(), ["__construct"])) {
                    $param = [];
                    foreach ($method->getParameters() as $parameter) {
                        $param[] = $parameter->getName();
                    }
                    $response["class"][$key]["methods"][$method->getName()] = $param;
                }
            }
        }

        return $response;
    }

    /**
     * Execute component
     *
     * @param  string $ns      component class namespace
     * @param  string $action  component action
     * @return string
     */
    private function execute($ns, $action)
    {
        $response  = null;
        $component = new $ns($this->config);

        if (method_exists($component, $action)) {
            $response = call_user_func_array(
                [
                    $component,
                    $action
                ],
                $this->input["params"]
            );
        } else {
            $response = sprintf(Server::ERROR_ACTION, $action, $ns);
        }

        return $response;
    }
    
    /**
     *
     */
    private function __destruct()
    {
        header("Content-Type: text/plain; charset=utf-8");
        echo $this->response;
    }
}
