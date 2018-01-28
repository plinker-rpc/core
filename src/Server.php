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
    protected $post;

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
        $this->post = file_get_contents("php://input");
        $this->post = gzinflate($this->post);
        $this->post = json_decode($this->post, true);

        // check allowed ips
        if (!empty($this->config["allowed_ips"]) &&
           !in_array($_SERVER["REMOTE_ADDR"], $this->config["allowed_ips"])) {
            $this->response = serialize([
                "error" => sprintf(Server::ERROR_IP, $_SERVER["REMOTE_ADDR"]),
                "code" => 403
            ]);
            return;
        }

        // check header token matches data token
        if ($_SERVER["HTTP_PLINKER"] != $this->post["token"]) {
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

        // decode post payload
        $this->post = $this->signer->decode($this->post);

        // could not decode payload
        if ($this->post === null) {
            $this->response = serialize([
                "error" => Server::ERROR_DECODE,
                "code" => 422
            ]);
            return;
        }

        //
        $response = null;
        $ns = null;
        $action = $this->post["action"];
        $this->config = array_merge(
            $this->config,
            $this->post
        );

        // component is in classes config
        if (array_key_exists($this->post["component"], $this->config["classes"])) {
            //
            if (!empty($this->config["classes"][$this->post["component"]][0])) {
                $ns = $this->config["classes"][$this->post["component"]][0];
            }

            //
            if (!empty($this->config["classes"][$this->post["component"]][1])) {
                $this->config = array_merge(
                    $this->config,
                    $this->config["classes"][$this->post["component"]][1]
                );
            }

            //
            if (!empty($ns) && !file_exists($ns)) {
                $this->response = serialize([
                    "error" => sprintf(Server::ERROR_USR_COMPONENT, $this->post["component"]),
                    "code"  => 422
                ]);
                return;
            }

            //
            require($ns);

            //
            if (!class_exists($this->post["component"])) {
                $this->response = serialize([
                    "error" => sprintf(Server::ERROR_USR_COMPONENT, $this->post["component"]),
                    "code"  => 422
                ]);
                return;
            }

            //
            $response = $this->execute($this->post["component"], $action);

            $this->response = serialize($response);
            return;
        }

        // component is plinker endpoint
        $ns = "\\Plinker\\Core\\Endpoint\\".ucfirst($this->post["component"]);

        if (class_exists($ns)) {
            //
            $response = $this->execute($ns, $action);
        } else {
            if (empty($this->post["component"]) && $action === "info") {
                $response = $this->info();
            } else {
                $response = sprintf(Server::ERROR_EXT_COMPONENT, $this->post["component"]);
            }
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
                $this->post["params"]
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
