<?php
namespace Plinker\Core;

class Server
{
    /**
     * @param string $post
     * @param string $publicKey
     * @param string $privateKey
     * @param array  $config
     */
    public function __construct(
        $post,
        $publicKey = '',
        $privateKey = '',
        $config = array()
    ) {
        $this->post = $post;
        $this->publicKey = $publicKey;
        $this->privateKey = $privateKey;
        $this->config = $config;
    }

    /**
     *
     */
    public function execute()
    {
        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json; charset=utf-8');

        $encrypt = !empty($this->post['encrypt']) ? true : false;
        
        $signer = new Signer($this->publicKey, $this->privateKey, $encrypt);

        $data = $signer->decode(
            $this->post
        );

        if (!is_array($data)) {
            return serialize($data);
        }

        if (!isset($data['params'])) {
            $data['params'] = array();
        }

        if (!empty($data['config'])) {
            $this->config = $data['config'];
        }

        if (empty($data['component'])) {
            return serialize('component class cannot be empty');
        }

        if (empty($data['action'])) {
            return serialize('action cannot be empty');
        }

        $class = '\\Plinker\\'.$data['component'];

        if (class_exists($class)) {
            $componentClass = new $class($this->config+$data+$this->post);

            if (method_exists($componentClass, $data['action'])) {
                $return = call_user_func(
                    array(
                        $componentClass,
                        $data['action']
                    ),
                    $data['params']
                );
            } else {
                $return = 'action not implemented';
            }
        } else {
            $return = 'not implemented';
        }

        $encoded = $signer->encode(array(
            'time' => microtime(true),
            'response' => serialize($return)
        ));

        return serialize($encoded);
    }

}
