<?php
namespace Plinker\Core;

use Requests;

class Client
{
    /**
     * @param string $url
     * @param string $component
     * @param string $publicKey
     * @param string $privateKey
     * @config array $config
     */
    public function __construct(
        $url,
        $component,
        $publicKey = '',
        $privateKey = '',
        $config = array(),
        $encrypt = true
    ) {
        $this->endpoint = $url;
        $this->component = $component;
        $this->publicKey = $publicKey;
        $this->privateKey = $privateKey;
        $this->config = $config;
        $this->encrypt = $encrypt;
    }

    /**
     * Helper which changes the server component on the fly without changing
     * the connection
     *
     * @param string $component - component class namespace
     * @param array  $config    - component array
     */
    public function useComponent($component = '', $config = array(), $encrypt = true)
    {
        $this->component = $component;
        $this->config = $config;
        $this->encrypt = $encrypt;

        return new $this(
            $this->endpoint,
            $this->component,
            $this->publicKey,
            $this->privateKey,
            $this->config,
            $this->encrypt
        );
    }

    /**
     * Magic caller
     *
     * @param string $action
     * @param array  $params
     */
    public function __call($action, $params)
    {
        if (!is_scalar($action)) {
            throw new \Exception('Method name has no scalar value');
        }

        if (!is_array($params)) {
            throw new \Exception('Params must be given as array');
        }

        $params = array_values($params);

        $signer = new Signer($this->publicKey, $this->privateKey, $this->encrypt);
        
        // unset local private key
        unset($this->config['plinker']['private_key']);

        $encoded = $signer->encode(array(
            'time' => microtime(true),
            'self' => $this->endpoint,
            'component' => $this->component,
            'config' => $this->config,
            'action' => $action,
            'params' => $params
        ));

        $response = Requests::post(
            $this->endpoint,
            array(),
            $encoded,
            array(
                'timeout' => (!empty($this->config['timeout']) ? (int) $this->config['timeout'] : 60),
            )
        );

        // check response is a serialized string
        if (@unserialize($response->body) === false) {
            throw new \Exception('Could not unserialize response: '.$response->body);
        }

        $response->body = unserialize($response->body);
        
        // decode response
        $data = $signer->decode(
            $response->body
        );        

        // handle response exceptions
        if ($response->body == 'unauthorised') {
            throw new \Exception('Unauthorised');
        }        

        if ($response->body == 'missing public key') {
            throw new \Exception('Missing public key');
        }

        if ($response->body == 'missing token key') {
            throw new \Exception('Missing token key');
        }        

        if ($response->body == 'empty data') {
            throw new \Exception('Missing data');
        }

        if ($response->body == 'unauthorised public key') {
            throw new \Exception('Unauthorised public key');
        }

        // unserialize data
        return unserialize($data['response']);
    }

}
