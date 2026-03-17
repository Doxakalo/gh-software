<?php

namespace App\Model;

use App\Service\APIService;
use Nette;

class PushoverModel
{
    // MODELS AND SERVICES
    private $api;

    // CONFIG
    public $config;

    // OTHER

    public function __construct($config, APIService $api)
    {
        $this->api = $api;
        $this->config = $config;
    }

    public function pushNotification($data)
    {
        $requestSetting = [
            "api_url" => $this->config["api_url"],
            "endpoint" => "/messages.json",
            "method" => "POST",
            "headers" => [
                "Content-Type" => "application/x-www-form-urlencoded"
            ]
        ];

        return $this->api->request($requestSetting, $data);
    }

    public function isResultError($result)
    {
        if (isset($result["response"]["errors"]) && !empty($result["response"]["errors"])) {
            throw new \Exception(json_encode($result));
        }
    }


}
