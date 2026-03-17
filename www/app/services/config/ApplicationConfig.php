<?php

namespace App\Service\Config;

use App\Utils\ArrayUtil;

class ApplicationConfig
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
}
