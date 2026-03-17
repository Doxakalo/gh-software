<?php

namespace App\Model;

use App\Service\APIService;
use Nette;

class ClientPortalModel
{
    private $config;
    private $APIService;

    const SUCCESS_HTTP_CODE = [200];

    public function __construct($config, APIService $APIService)
    {
        $this->config = $config;
        $this->APIService = $APIService;
    }

    public function getConfig()
    {
        return $this->config;
    }

    public function createRequest($data)
    {
        $requestSetting = [
            "api_url" => $this->config["api_url"],
            "endpoint" => "?token=" . $this->createToken(0, $this->config["auth"]["token"]),
            "method" => "POST",
            "headers" => [
                "Content-Type: application/json"
            ]
        ];

        return $this->APIService->request($requestSetting, json_encode($data));
    }

    // method create list of tokens
    private function createToken($hoursSum, $salt) {
        $currentDateTime = new \DateTime();
        $currentDateTime->setTimezone(new \DateTimeZone('UTC'));

        return sha1($salt + intval($currentDateTime->format("d")) +  intval($currentDateTime->format("m")) + intval($currentDateTime->format("Y")) + (intval($currentDateTime->format("H")) + $hoursSum));
    }


    public function isResultError($result)
    {
        if (!isset($result["status"]["http_code"]) || !in_array($result["status"]["http_code"], self::SUCCESS_HTTP_CODE)) {
            throw new \Exception(json_encode($result));
        } else {
            if(!isset($result["response"]["error"]) || isset($result["response"]["error"]) && $result["response"]["error"] !== 0){
                throw new \Exception(json_encode($result));
            }
        }
    }
}
