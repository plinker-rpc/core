<?php

namespace Plinker\Core;

use Requests;

/**
 * Plinker Client
 */
class Client
{
    private $endpoint;
    private $component;
    private $publicKey;
    private $privateKey;
    private $config;
    private $encrypt;
    private $response;
    private $signer;

    /**
     * @param string $endpoint
     * @param string $component
     * @param string $publicKey
     * @param string $privateKey
     * @param array  $config
     * @param bool   $encrypt
     */
    public function __construct(
        $endpoint,
        $component,
        $publicKey = '',
        $privateKey = '',
        $config = [],
        $encrypt = true
    ) {
        // define vars
        $this->endpoint = $endpoint;
        $this->component = $component;
        $this->publicKey = hash('sha256', gmdate('h').$publicKey);
        $this->privateKey = hash('sha256', gmdate('h').$privateKey);
        $this->config = $config;
        $this->encrypt = $encrypt;
        $this->response = null;

        // init signer
        $this->signer = new Signer($this->publicKey, $this->privateKey, $this->encrypt);
    }

    /**
     * Helper which changes the server component on the fly without changing
     * the connection.
     *
     * @param string $component - component class namespace
     * @param array  $config    - component array
     */
    public function useComponent($component = '', $config = [], $encrypt = true)
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
     * Magic caller.
     *
     * @param string $action
     * @param array  $params
     * @return mixed
     */
    public function __call($action, $params)
    {
        if (!is_scalar($action)) {
            throw new \InvalidArgumentException('Method name has no scalar value');
        }

        if (!is_array($params)) {
            throw new \InvalidArgumentException('Arguments must be given as array');
        }

        // change arguments array into numeric indexed
        $params = array_values($params);

        // unset local private key
        unset($this->config['plinker']['private_key']);

        // encode payload
        $encoded = $this->signer->encode([
            'time'      => microtime(true),
            'self'      => $this->endpoint,
            'component' => $this->component,
            'config'    => $this->config,
            'action'    => $action,
            'params'    => $params,
        ]);

        // send request and store in response
        if (getenv('APP_ENV') !== 'testing') {
            $this->response = Requests::post(
                $this->endpoint,
                [
                    // send plinker header
                    'plinker' => true,
                    // sign token generated from encoded packet, send as header
                    'token'   => hash_hmac('sha256', $encoded['token'], $this->privateKey),
                ],
                $encoded,
                [
                    'timeout' => (!empty($this->config['timeout']) ? (int) $this->config['timeout'] : 60),
                ]
            );
        } else {
            // testing
            $this->response = new \stdClass();
            $this->response->body = serialize($this->signer->encode([
                'response' => $params,
            ]));
        }

        // check response is a serialized string
        if (@unserialize($this->response->body) === false) {
            if (empty($this->response->body)) {
                $message = $this->response->raw;
            } else {
                $message = $this->response->body;
            }

            throw new \Exception('Could not unserialize response: '.$message);
        }

        // initial unserialize response body
        $this->response->body = unserialize($this->response->body);

        // decode response
        $response = $this->signer->decode(
            $this->response->body
        );

        // verify response packet timing validity
        $response['packet_time'] = microtime(true) - $this->response->body['time'];
        if ($response['packet_time'] >= 1) {
            throw new \Exception('Response timing packet check failed');
        }

        // verify data timing validity
        $response['data_time'] = (microtime(true) - $response['time']);
        if ($response['data_time'] >= 1) {
            throw new \Exception('Response timing data check failed');
        }

        // decode response data
        if (is_string($response['response'])) {
            // empty data response
            if (empty($response['response'])) {
                return '';
            }
            // response should be a serialized string
            if (@unserialize($response['response']) === false) {
                throw new \Exception('Could not unserialize response: '.$response['response']);
            }
            $response['response'] = unserialize($response['response']);
        }

        // check for errors
        if (is_array($response['response']) && !empty($response['response']['error'])) {
            throw new \Exception(ucfirst($response['response']['error']));
        }

        // unserialize data
        return $response['response'];
    }
}
