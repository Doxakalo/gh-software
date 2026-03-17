<?php

namespace App\Models\FileMaker;

use fmRESTor\fmRESTor;
use Nette\Http\FileUpload;

class ResultModel extends FileMakerBaseModel
{
    // CONST
    const LAYOUT = "data_results";

    // ATTR
    public $fm;

    public function __construct($config)
    {
        $this->fm = new fmRESTor($config["host"], $config["database"], self::LAYOUT, $config["username"], $config["password"], [
            "autorelogin" => true,
            "logType" => fmRESTor::LOG_TYPE_ERRORS,
            "logDir" => dirname(__DIR__,3) . "/log/filemaker-request/",
            "allowInsecure" => true,
            "tokenStorage" => fmRESTor::TS_FILE,
            "tokenFilePath" => dirname(__DIR__,3) . "/temp/filemaker-token.txt"
        ]);
    }

    public function getRecordsByCriterions($query, $sort = null, $limit = 1000000){
        $find["query"] = $query;

        if($sort !== null){
            $find["sort"] = $sort;
        }

        $find["limit"] = $limit;

        return $this->fm->findRecords($find);
    }


    public function getRecords($limit = 1000000){
        return $this->fm->getRecords([
            "_limit" => $limit
        ]);
    }
}
