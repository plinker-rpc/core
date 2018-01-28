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

namespace Plinker\Core\Lib;

/**
 * Plinker\Core\Lib\Curl
 */
final class Curl
{
    /**
     * @var
     */
    private $config;

    /**
     * @var
     */
    private $options;

    /**
     * Class construct
     *
     * @param  array  $config  - config array which holds object configuration
     * @return void
     */
    public function __construct(array $config = [])
    {
        //
        $this->config = $config;
    }

    /**
     * @return void
     */
    private function setOptions()
    {
        //
        $this->options = [
            CURLOPT_FAILONERROR    => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_ENCODING       => "gzip",
            CURLOPT_HTTPHEADER     => [
                "Content-Type: application/json"
            ]
        ];
    }

    /**
     *  POST
     *
     * @param string $url        - url of the plinker server
     * @param array  $parameters - post parameters
     */
    public function post($url, $parameters = [], $headers = [])
    {
        //
        $this->setOptions();

        //
        $parameters = json_encode($parameters);
        $parameters = gzdeflate($parameters, 9);

        //
        $curl = curl_init($url);

        //
        $this->options[CURLOPT_POST] = true;
        $this->options[CURLOPT_POSTFIELDS] = $parameters;

        // set request headers
        if (!empty($headers)) {
            foreach ($headers as $header) {
                $this->options[CURLOPT_HTTPHEADER][] = $header;
            }
            $headers = [];
        }

        //
        curl_setopt_array($curl, $this->options);

        //
        $body = curl_exec($curl);

        //
        $return = [
            'body'    => $body,
            'code'    => curl_getinfo($curl, CURLINFO_HTTP_CODE),
            'error'   => (curl_error($curl) ? curl_error($curl) : null)
        ];

        //
        curl_close($curl);

        //
        return $return;
    }
}
