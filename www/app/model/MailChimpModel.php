<?php

namespace App\Model;

use App\Service\APIService;
use http\Exception;
use Nette;

class MailChimpModel
{
    // MODELS AND SERVICES
    private $api;

    // CONFIG
    public $config;

    public function __construct($config, APIService $api)
    {
        $this->api = $api;
        $this->config = $config;
    }

    public function addSubscriberToList($data, $listId){
        $requestSetting = [
            "api_url" => $this->config["api_url"],
            "endpoint" => "/lists/" . $listId. "/members/",
            "method" => "POST",
            "headers" => [
                "Content-Type: application/json"
            ],
            "auth" => [
                "user" => $this->config["auth"]["user"],
                "password" => $this->config["auth"]["token"]
            ]
        ];
        return $this->api->request($requestSetting, json_encode($data));
    }

    public function getDetailOfListMember( $listId, $memberEmail){
        $requestSetting = [
            "api_url" => $this->config["api_url"],
            "endpoint" => "/lists/" . $listId. "/members/" . md5($memberEmail),
            "method" => "GET",
            "auth" => [
                "user" => $this->config["auth"]["user"],
                "password" => $this->config["auth"]["token"]
            ]
        ];
        return $this->api->request($requestSetting);
    }

    public function updateMemberInList($data, $listId, $memberEmail){
        $requestSetting = [
            "api_url" => $this->config["api_url"],
            "endpoint" => "/lists/" . $listId. "/members/" . md5($memberEmail),
            "method" => "PATCH",
            "headers" => [
                "Content-Type: application/json"
            ],
            "auth" => [
                "user" => $this->config["auth"]["user"],
                "password" => $this->config["auth"]["token"]
            ]
        ];
        return $this->api->request($requestSetting, json_encode($data));
    }

    public function setSubscriberTag($email, $tag, $listId){

        // Documentation: https://mailchimp.com/developer/reference/lists/list-members/list-member-tags/
        $subscriberHash = md5(strtolower($email));
        $requestSetting = [
            "api_url" => $this->config["api_url"],
            "endpoint" => "/lists/" . $listId. "/members/" . $subscriberHash . "/tags",
            "method" => "POST",
            "headers" => [
                "Content-Type: application/json"
            ],
            "auth" => [
                "user" => $this->config["auth"]["user"],
                "password" => $this->config["auth"]["token"]
            ]
        ];

        return $this->api->request($requestSetting, json_encode(["tags" => [["name" => $tag, "status" => "active"]]]));
    }

    public function isResultError($result){
        if(isset($result["response"]["status"]) && $result["response"]["status"] >= 400){
            throw new \Exception(json_encode($result), $result["response"]["status"]);
        }
    }

}
