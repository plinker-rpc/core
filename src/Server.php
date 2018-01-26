<?php

namespace Plinker\Core;

/**
 * Server endpoint class.
 */
class Server
{
    private $post = [];
    private $config = [];
    private $publicKey = '';
    private $privateKey = '';

    /**
     * @param string $post
     * @param string $publicKey
     * @param string $privateKey
     * @param array  $config
     */
    public function __construct(
        $post = [],
        $publicKey = '',
        $privateKey = '',
        $config = []
    ) {
        // define vars
        $this->post = $post;
        $this->config = $config;
        $this->publicKey = hash('sha256', gmdate('h').$publicKey);
        $this->privateKey = hash('sha256', gmdate('h').$privateKey);

        // init signer
        $this->signer = new Signer(
            $this->publicKey,
            $this->privateKey,
            (!empty($this->post['encrypt']) ? true : false)
        );
    }
    
    /**
     * Check client IP is in allowed list if allowed list is set.
     *
     * @return bool
     */
    final private function checkAllowedIps()
    {
        return !(
            !empty($this->config['allowed_ips']) &&
            !in_array($_SERVER['REMOTE_ADDR'], $this->config['allowed_ips'])
        );
    }
    
    /**
     * Check client IP is in allowed list if allowed list is set.
     *
     * @return bool
     */
    final private function verifyRequestToken()
    {
        return !(
            empty($this->post['token']) ||
            empty($_SERVER['HTTP_TOKEN']) ||
            hash_hmac('sha256', $this->post['token'], $this->privateKey) != $_SERVER['HTTP_TOKEN']
        );
    }

    /**
     * Server exection method.
     *
     * @return string
     */
    public function execute()
    {
        // set response header
        header('Content-Type: text/plain; charset=utf-8');

        // check allowed ips
        if (!$this->checkAllowedIps()) {
            return serialize($this->signer->encode([
                'response' => [
                    'error' => 'IP not in allowed list: '.$_SERVER['REMOTE_ADDR'],
                ]
            ]));
        }

        // verify request token
        if (!$this->verifyRequestToken()) {
            return serialize($this->signer->encode([
                'response' => [
                    'error' => 'invalid request token',
                ]
            ]));
        }

        // decode post payload
        $data = $this->signer->decode(
            $this->post
        );

        // check client post is an array
        if (!is_array($data)) {
            return serialize($data);
        }

        // check data params array or set
        if (!isset($data['params'])) {
            $data['params'] = [];
        }

        // check data config array, set into scope
        if (!empty($data['config'])) {
            $this->config = $data['config'];
        }

        // check for empty
        if (empty($data['component']) || empty($data['action'])) {
            $error = empty($data['component']) ? 'component class' : 'action';
            return serialize($this->signer->encode([
                'response' => [
                    'error' => $error.' cannot be empty',
                ]
            ]));
        }

        $class = '\\Plinker\\'.$data['component'];

        if (class_exists($class)) {
            $componentClass = new $class($this->config + $data + $this->post);

            if (method_exists($componentClass, $data['action'])) {
                $return = call_user_func(
                    [
                        $componentClass,
                        $data['action']
                    ],
                    $data['params']
                );
            } else {
                $return = 'action not implemented';
            }
        } else {
            $return = 'not implemented';
        }

        return serialize($this->signer->encode([
            'response' => $return,
        ]));
    }
}
