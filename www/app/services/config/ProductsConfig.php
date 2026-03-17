<?php

namespace App\Service\Config;

class ProductsConfig
{
    private $config;

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function getConfig()
    {
        return $this->config;
    }

    public function getProductByCode($code){
        if(isset($this->config[$code])){
            return $this->config[$code];
        }

        return false;
    }
}
