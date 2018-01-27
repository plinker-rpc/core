<?php

namespace Plinker\Core\Lib;

final class Curl
{
    private $config;
    private $options;

    /**
     *
     */
    public function __construct(array $config = [])
    {
        //
        $this->config = $config;
    }

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
     */
    public function post($url, $parameters = null, $headers = [])
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
        if (!empty(headers)) {
            foreach($headers as $header) {
                $this->options[CURLOPT_HTTPHEADER][] = header;
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