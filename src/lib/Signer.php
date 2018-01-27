<?php

namespace Plinker\Core\Lib;

final class Signer
{
    private $config;

    /**
     *
     */
    public function __construct($config = [])
    {
        //
        $this->config = array_merge([
            "secret" => null
        ], $config);

        // hash secret
        if (isset($this->config["secret"])) {
            $this->config["secret"] = hash("sha256", gmdate("h").$this->config["secret"]);
        }
    }

    /**
     *
     */
    private function encrypt($plaintext, $password)
    {
        $method     = "AES-256-CBC";
        $key        = (string) hash("sha256", $password, true);
        $iv         = (string) openssl_random_pseudo_bytes(16);
        $ciphertext = (string) openssl_encrypt($plaintext, $method, $key, OPENSSL_RAW_DATA, $iv);
        
        $hash = (string) hash_hmac("sha256", $ciphertext, $key, true);

        return base64_encode($iv . $hash . $ciphertext);
    }
    
    /**
     *
     */
    private function decrypt($ciphertext, $password)
    {
        $ciphertext    = base64_decode($ciphertext);
        
        $method     = "AES-256-CBC";
        $iv         = substr($ciphertext, 0, 16);
        $hash       = substr($ciphertext, 16, 32);
        $ciphertext = substr($ciphertext, 48);
        $key        = (string) hash("sha256", $password, true);

        if (hash_hmac("sha256", $ciphertext, $key, true) !== $hash) {
            return null;
        }

        return openssl_decrypt($ciphertext, $method, $key, OPENSSL_RAW_DATA, $iv);
    }
    
    /**
     *
     */
    public function encode($data)
    {
        $data = serialize($data);
            
        return [
            "data"  => $this->encrypt($data, $this->config["secret"]),
            "token" => hash_hmac(
                "sha256",
                $data,
                $this->config["secret"]
            )
        ];
    }

    /**
     *
     */
    public function decode($data)
    {
        $data["data"] = $this->decrypt($data["data"], $this->config["secret"]);
        
        //
        if (hash_hmac(
            "sha256",
            $data["data"],
            $this->config["secret"]
        ) == $data["token"]) {
            return (array) unserialize($data["data"]);
        } else {
            return null;
        }
    }
}
