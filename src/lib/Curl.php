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
     *
     */
    final private function setOptions()
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
        if (is_array($parameters)) {
            $parameters = json_encode($parameters);
            $parameters = gzdeflate($parameters, 9);
        }

        //
        $curl = curl_init($url);

        //
        $this->options[CURLOPT_POST] = true;
        $this->options[CURLOPT_POSTFIELDS] = $parameters;

        //
        if (!empty($headers)) {
            foreach ($headers as $header) {
                $this->options[CURLOPT_HTTPHEADER][] = $header;
            }
        }

        //
        curl_setopt_array($curl, $this->options);

        //
        $body = curl_exec($curl);

        if (curl_error($curl)) {
            return serialize([
                "url"   => $url,
                "error" => curl_error($curl),
                "code"  => curl_getinfo($curl, CURLINFO_HTTP_CODE)
            ]);
        }

        //
        curl_close($curl);

        //
        return $body;
    }
}
