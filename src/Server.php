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
 *
 * @codeCoverageIgnore
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
            "secret"      => null,
            "allowed_ips" => []
        ], $config);
    }
    
    /**
     * Listen method
     *
     * <code>
     *  $server->listen();
     * </code>
     *
     * @return string
     */
    public function listen()
    {
        $this->setInput();
        
        // load signer
        if (!$this->signer) {
            $this->signer = new Lib\Signer($this->config);
        }

        // check allowed ips
        if (!$this->checkAllowedIp($_SERVER["REMOTE_ADDR"], $this->config["allowed_ips"])) {
            return json_encode($this->signer->encode([
                "error" => sprintf(Server::ERROR_IP, $_SERVER["REMOTE_ADDR"]),
                "code" => 403
            ]), JSON_PRETTY_PRINT);
        }

        // check header token matches data token
        if (empty($this->input["token"]) || $_SERVER["HTTP_PLINKER"] != $this->input["token"]) {
            return json_encode($this->signer->encode([
                "error" => Server::ERROR_TOKEN,
                "code" => 422
            ]), JSON_PRETTY_PRINT);
        }

        // decode input payload
        $this->input = $this->signer->decode($this->input);

        // could not decode payload
        if ($this->input === null) {
            return json_encode($this->signer->encode([
                "error" => Server::ERROR_DECODE,
                "code" => 422
            ]), JSON_PRETTY_PRINT);
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

        // sign response and return
        return json_encode($this->signer->encode($response), JSON_PRETTY_PRINT);
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
            'class' => []
        ];
        
        foreach ($this->config["classes"] as $key => $val) {
            
            // addtional config
            $response["class"][$key]["config"] = !empty($val[1]) ? $val[1] : [];
            
            // check class file exists
            if (!file_exists($val[0])) {
                $response["class"][$key]["methods"] = [];
                continue;
            }
            
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
     * Sets inbound input value into scope
     *
     * @return void
     */
    private function setInput()
    {
        $this->input = file_get_contents("php://input");
        if (!empty($this->input)) {
            $this->input = gzinflate($this->input);
            $this->input = json_decode($this->input, true);
        }
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
        // define component namespace
        $ns = "\\Plinker\\".ucfirst($component);

        if (class_exists($ns)) {
            //
            $response = $this->execute($ns, $action);
        } elseif (class_exists($ns."\\".ucfirst($component))) {
            //
            $ns = "\\Plinker\\".ucfirst($component)."\\".ucfirst($component);
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
            $this->config['config'] = array_merge(
                $this->config['config'],
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
     * Execute component
     *
     * @param  string $ns      component class namespace
     * @param  string $action  component action
     * @return string
     */
    private function execute($ns, $action)
    {
        // filter out (secret, server, timeout) from construct config
        $config = array_filter($this->config['config'], function ($key) {
            return !in_array($key, ['secret', 'server', 'timeout']);
        }, ARRAY_FILTER_USE_KEY);

        // init component
        $component = new $ns($config);

        // call method
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
}
