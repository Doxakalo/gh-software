<?php

// error reporting, for disbale set to zero or just comment it
//error_reporting(E_ALL); 
//ini_set("display_errors", 1);;
// check if all mandatory parameters exists
$mandatoryParameters = ["token"];

foreach ($mandatoryParameters as $parameter) {
    if (!isset($_GET[$parameter]) || empty($_GET[$parameter])) {
        responseJson(-1, "Missing mandatory parameters");
    }
}

// load config file
$pathToConfigFile = __DIR__ . "/config/config.json";
if (!file_exists($pathToConfigFile)) {
    responseJson(-2, "Config file with versions doesn't exists");
}

$configContent = @file_get_contents($pathToConfigFile);
if (@file_get_contents($configContent)) {
    responseJson(-2, "Config file could not be load. Check permissions");
}
$config = json_decode($configContent, true);
if (($config === false) || !isset($config) || empty($config)) {
    responseJson(-2, "Config json contain invalid json");
}

$token = $_GET["token"];
$salt = $config["salt"];

// create list of approved tokens
$myTokens = [];
$myTokens[] = createToken(0, $salt);
$myTokens[] = createToken(-1, $salt);
$myTokens[] = createToken(1, $salt);

if(!in_array($token, $myTokens)){
    responseJson(-3, "Invalid token");
}

// handle params to CP
use fmRESTor\fmRESTor;

session_start();
require_once 'fmRESTor.php';

$issue_json = file_get_contents('php://input');

$trackers = [
    "support" => 1,
    "complaint" => 2
];


$issue_array = json_decode($issue_json, true);

// ======= Create new instance of fmRESTor
$database = $config["database"];
$host = $config["host"];
$username = $config["username"];
$password = $config["password"];
$scriptIssues = $config["script_issue"];
$scriptFiles = $config["script_files"];
$scriptStartup = $config["script_startup"];
$layout = $config["layout"];
$options = [
    "autorelogin" => true,
    "allowInsecure" => false,
    "logType" => fmRESTor::LOG_TYPE_DEBUG,
    "logDir" => __DIR__ . "/logs/",
    "tokenStorage" => fmRESTor::TS_FILE,
    "tokenFilePath" => "restapi-token.txt"
];

$fm = new fmRESTor($host, $database, $layout, $username, $password, $options);

// ======= Create new user in case that user_id doesn't exists
if (!isset($issue_array["user_id"]) && empty($issue_array["user_id"])) {
    $dataForCreateNewUser = [
        "project_id" => $issue_array["project_id"],
        "email" => $issue_array["email"],
        "full_name" => $issue_array["fullname"],
        "inactive" => true
    ];
    $dataForCreateNewUserJson = json_encode($dataForCreateNewUser);

    $createdUserResponseRaw = $fm->createRecord([
        "fieldData" => [
            "parameter" => $dataForCreateNewUserJson
        ],
        "script" => "REST_USERScreateAccount",
        "script.param" => $dataForCreateNewUserJson,
        "script.prerequest" => $scriptStartup,
    ]);

    if (!$fm->isError($createdUserResponseRaw)) {
        $createUserResponseScriptResultJson = json_decode(isset($createdUserResponseRaw["result"]["response"]["scriptResult"]) ? $createdUserResponseRaw["result"]["response"]["scriptResult"] : [], true);
        if (isset($createUserResponseScriptResultJson["data"]["user_id"])) {
            $userId = $createUserResponseScriptResultJson["data"]["user_id"];
            $issue_array["user_id"] = $userId;
            $issue_array["author_id"] = $userId;
        } else {
            responseJson(-5, "Create the new user failed - Invalid results");
        }
    } else {
        responseJson(-5, "Create the new user failed");
    }
}

// Convert Tracker name to id
$tracker_id = $trackers[$issue_array["tracker"]];
$issue_array["tracker_id"] = $tracker_id;
$issue_json = json_encode($issue_array);

// perfom script for files upload

// set the parameters for the new record
$issue_array = json_decode($issue_json, true);
if (isset($issue_array["attachments"]) && !empty($issue_array["attachments"])) {
    $attachments_array = $issue_array["attachments"];
    $attachments_json = json_encode($attachments_array);

    $newRecord = array(
        "fieldData" => array(
            "parameter" => $attachments_json
        ),
        "script" => $scriptFiles,
        "script.param" => $attachments_json,
        "script.prerequest" => $scriptStartup,
    );

    $result = $fm->createRecord($newRecord);

    if ($fm->isError($result)) {
        if (!isset($result["result"]["messages"][0]["code"])) {
            responseJson(-4, "Error during creating new request");
        } else {
            responseJson($result["result"]["messages"][0]["code"], $result["result"]["messages"][0]["message"]);
        }
    }

// remove attachments key and add uploads
    $response = json_decode($result["result"]["response"]["scriptResult"], true);
    $uploads = $response["uploads"];
    unset($issue_array["attachments"]);
    $issue_array["uploads"] = $uploads;
    $issue_json = json_encode($issue_array);
}

//var_dump($issue_json);
//exit();

// perform scrip for issue process
//$result = $fm->runScript($script, ["script.param"=>$issue_json]);

// set the parameters for the new record
$newRecord = [];
$newRecord = array(
    "fieldData" => array(
        "parameter" => $issue_json
    ),
    "script" => $scriptIssues,
    "script.param" => $issue_json
);

$result = $fm->createRecord($newRecord);

if ($fm->isError($result)) {
    if (!isset($result["result"]["messages"][0]["code"])) {
        responseJson(-4, "Error during creating new request");
    } else {
        responseJson($result["result"]["messages"][0]["code"], $result["result"]["messages"][0]["message"]);
    }
} else {
    $response = $fm->getResponse($result);
    echo $response["response"]["scriptResult"];
}

$fm->logout();

// method create list of tokens
function createToken($hoursSum, $salt)
{
    $currentDateTime = new DateTime();
    $currentDateTime->setTimezone(new DateTimeZone('UTC'));

    return sha1($salt + intval($currentDateTime->format("d")) + intval($currentDateTime->format("m")) + intval($currentDateTime->format("Y")) + (intval($currentDateTime->format("H")) + $hoursSum));
}

// output response function
function responseJson($error, $message)
{
    header('Content-Type: application/json; charset=utf-8');
    $data = [
        "error" => $error,
        "message" => $message,
    ];
    echo json_encode($data);
    exit();
}