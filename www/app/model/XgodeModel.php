<?php

namespace App\Model;

use App\Service\APIService;
use Nette;

class XgodeModel
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

    private function createAuthToken($endpoint)
    {
        $datetime = new \DateTime();
        $datetime->setTimezone(new \DateTimeZone('Europe/Prague'));

        return sha1($this->config["salt"] . $endpoint . '/' . $datetime->format("d-m-Y") . $this->config["salt"]);
    }

    public function addSubscription($data)
    {
        $endpoint = "/api-esellerate/subscription";
        $requestSetting = [
            "api_url" => $this->config["api_url"],
            "endpoint" => $endpoint,
            "method" => "POST",
        ];

        $data["token"] = $this->createAuthToken($endpoint);

        return $this->api->request($requestSetting, $data);
    }
}
