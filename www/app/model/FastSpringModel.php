<?php

namespace App\Model;

use App\Service\APIService;
use Nette;
use Tracy\Logger;

class FastSpringModel
{
    private $config;
    private $APIService;
    private $logger;
    
    const ERROR_HTTP_CODE = [500, 400, 403, 404, 401];

    public function __construct($config, APIService $APIService, \Monolog\Logger $logger)
    {
        $this->config = $config;
        $this->APIService = $APIService;
        $this->logger = $logger;
    }

    public function getConfig()
    {
        return $this->config;
    }


    public function createSession($rawData, $countryISO, $languageISO )
    {
        $contact = $rawData["contact"];
        $contact["country"] = $countryISO;

        $contact["language"] = $languageISO;

        $data = [
            "contact" => $contact,
            "items" => $rawData["items"],
            "tags" => $rawData["tags"],
        ];

        if(isset($rawData["coupon"]) && !empty($rawData["coupon"])) {
            $data["coupon"] = $rawData["coupon"];
        }

       $requestSetting = [
            "api_url" => $this->config["api_url"],
            "endpoint" => "/sessions",
            "method" => "POST",
            "auth" => [
                "user" => $this->config["auth"]["user"],
                "password" => $this->config["auth"]["password"],
            ],
            "headers" => [
                "Accept" => "application/json",
                "Content-Type: application/json",
            ]
        ];

        $responseRaw = $this->APIService->request($requestSetting, json_encode($data));

        $this->isResultError($responseRaw);

        return $responseRaw;
    }

    public function getSessionUrlById($id){
        return $this->getConfig()["store_front_url"] . "/session/" .  $id;
    }


    public function cancelSubscription($subscriptionId){
        $requestSetting = [
            "api_url" => $this->config["api_url"],
            "endpoint" => "/subscriptions/"  . $subscriptionId,
            "method" => "DELETE",
            "auth" => [
                "user" => $this->config["auth"]["user"],
                "password" => $this->config["auth"]["password"],
            ],
            "headers" => [
                "Accept" => "application/json",
                "Content-Type: application/json",
            ]
        ];

        $responseRaw = $this->APIService->request($requestSetting);

        if (!$this->isResultError($responseRaw)) {
            if(isset($responseRaw["response"]["subscriptions"][0]["result"]) && $responseRaw["response"]["subscriptions"][0]["result"] == "success"){
                return true;
            }
        }

        return false;
    }

    public function getAllSubscriptions($params = []){
        $data = [];
        $initPage = 1;
        $responseRaw = $this->getAllSubscriptionsOnPage($initPage, $params);

        if (!$this->isResultError($responseRaw)) {
            $response = $responseRaw["response"];
            $data = array_merge($data, $response["subscriptions"]);

            $nextPage = $response["nextPage"];

            if(!empty($nextPage)) {
                while (!empty($nextPage)) {
                    $responseRaw = $this->getAllSubscriptionsOnPage($nextPage, $params);
                    $response = $responseRaw["response"];
                    $data = array_merge($data, $response["subscriptions"]);

                    $nextPage = $response["nextPage"];
                }
            }

        }

        return $data;
    }

    private function getAllSubscriptionsOnPage($pageNumber, $params)
    {
        $requestSetting = [
            "api_url" => $this->config["api_url"],
            "endpoint" => "/subscriptions/"  . "?scope=all&page=" . $pageNumber . "&" . http_build_query($params),
            "method" => "GET",
            "auth" => [
                "user" => $this->config["auth"]["user"],
                "password" => $this->config["auth"]["password"],
            ],
            "headers" => [
                "Accept" => "application/json",
                "Content-Type: application/json",
            ]
        ];

        return $this->APIService->request($requestSetting);
    }

    public function getAllOrders($params = []){
        $orders = [];
        $initPage = 1;
        $responseRaw = $this->getAllOrdersOnPage($initPage, $params);

        if (!$this->isResultError($responseRaw)) {
            $response = $responseRaw["response"];
            $orders = array_merge($orders, $response["orders"]);

            $nextPage = $response["nextPage"];

            if(!empty($nextPage)) {
                while (!empty($nextPage)) {
                    $responseRaw = $this->getAllOrdersOnPage($nextPage, $params);
                    $response = $responseRaw["response"];
                    $orders = array_merge($orders, $response["orders"]);

                    $nextPage = $response["nextPage"];
                }
            }

        }

        return $orders;
    }

    private function getAllOrdersOnPage($pageNumber, $params)
    {
        $requestSetting = [
            "api_url" => $this->config["api_url"],
            "endpoint" => "/orders/"  . "?scope=all&page=" . $pageNumber,
            "method" => "GET",
            "auth" => [
                "user" => $this->config["auth"]["user"],
                "password" => $this->config["auth"]["password"],
            ],
            "headers" => [
                "Accept" => "application/json",
                "Content-Type: application/json",
            ]
        ];

        return $this->APIService->request($requestSetting);
    }


    public function getAllAccountsBasedOnEmail($email)
    {
        $requestSetting = [
            "api_url" => $this->config["api_url"],
            "endpoint" => "/accounts/"  . "?email=" . urlencode($email),
            "method" => "GET",
            "auth" => [
                "user" => $this->config["auth"]["user"],
                "password" => $this->config["auth"]["password"],
            ],
            "headers" => [
                "Accept" => "application/json",
                "Content-Type: application/json",
            ]
        ];

        $responseRaw = $this->APIService->request($requestSetting);

        $accounts = [];
        if (!$this->isResultError($responseRaw)) {
            $accounts = $responseRaw["response"]["accounts"];
        }

        return $accounts;
    }

    public function getAccountById($id)
    {
        $requestSetting = [
            "api_url" => $this->config["api_url"],
            "endpoint" => "/accounts/". $id,
            "method" => "GET",
            "auth" => [
                "user" => $this->config["auth"]["user"],
                "password" => $this->config["auth"]["password"],
            ],
            "headers" => [
                "Accept" => "application/json",
                "Content-Type: application/json",
            ]
        ];

        $responseRaw = $this->APIService->request($requestSetting);

        $accounts = [];
        if (!$this->isResultError($responseRaw)) {
            $accounts = $responseRaw["response"];
        }

        return $accounts;
    }

    public function getAllProductsPath(){
            $requestSetting = [
                "api_url" => $this->config["api_url"],
                "endpoint" => "/products",
                "method" => "GET",
                "auth" => [
                    "user" => $this->config["auth"]["user"],
                    "password" => $this->config["auth"]["password"],
                ],
                "headers" => [
                    "Accept" => "application/json",
                ]
            ];

            $resultRaw =  $this->APIService->request($requestSetting);

        if(!$this->isResultError($resultRaw)){
            $productPaths = $resultRaw["response"]["products"];

            return $productPaths;
        }

        return false;
    }

    public function getAllProducts()
    {
        $productPaths = $this->getAllProductsPath();

        if(!empty($productPaths)){
            $results = [];
            foreach($productPaths as $productPath){
                $productResultRaw = $this->getProduct($productPath);

                if(!$this->isResultError($productResultRaw)) {
                    $results[$productPath] = $productResultRaw["response"]["products"][0];
                }
            }

            return $results;
        }

        return false;
    }


    public function getProduct($productPath)
    {
        $requestSetting = [
            "api_url" => $this->config["api_url"],
            "endpoint" => "/products/" . $productPath,
            "method" => "GET",
            "auth" => [
                "user" => $this->config["auth"]["user"],
                "password" => $this->config["auth"]["password"],
            ],
            "headers" => [
                "Accept" => "application/json",
            ]
        ];

        return $this->APIService->request($requestSetting);
    }

    public function isResultError($result)
    {

        if (!isset($result["status"]["http_code"]) || in_array($result["status"]["http_code"], self::ERROR_HTTP_CODE)) {
            $this->logger->error("Curl Request - FastSpring", $result);
            return true;

        }
        return false;

    }
}
