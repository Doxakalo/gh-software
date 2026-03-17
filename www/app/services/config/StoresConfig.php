<?php

namespace App\Service\Config;

use App\Utils\ArrayUtil;

class StoresConfig
{
    // CONFIG
    private $config;

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function getConfig()
    {
        return $this->config;
    }

    public function getStoreDetail($productCode){
        if(isset($this->config[$productCode])){
            return $this->config[$productCode];
        }

        return false;
    }
}
