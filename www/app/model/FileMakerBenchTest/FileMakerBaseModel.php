<?php

namespace App\Models\FileMaker;

class FileMakerBaseModel
{
    const FIELD_DATA = "fieldData";
    const PORTAL_DATA = "portalData";
    const RECORD_ID = "recordId";

    public function scriptRusultSuccesful($result){
        if(isset($result["response"]["scriptResult"]) && !empty($result["response"]["scriptResult"])){
            $scriptResultJson = $result["response"]["scriptResult"];
            $scriptResult = json_decode($scriptResultJson, true);
            if($scriptResult["error"] === 0){
                return true;
            }
        }
        return false;
    }

    // GET DATA FROM FIRST RECORD IN RESULT
    public function getFirstRecordData($result, $data){
        if (isset($result["response"]["data"][0][$data])) {
            return $result["response"]["data"][0][$data];
        }
        return false;
    }

    public function isRecordExist($result){
        if(isset($result["messages"][0]["code"]) && $result["messages"][0]["code"] != 401 && !empty($result["response"])){
            return true;
        }

        return false;
    }

    public function isResultError($result){
        if(!isset($result["result"]["messages"][0]["code"]) || $result["result"]["messages"][0]["code"] != 0 && $result["result"]["messages"][0]["code"] != 401){
            return true;
        }

        return false;
    }


    public function getDataFromResult($result){
        if(isset($result["response"]["data"])){
            return $result["response"]["data"];
        }
        return null;
    }
}
