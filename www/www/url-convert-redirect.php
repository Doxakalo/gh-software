<?php
$server = $_SERVER["SERVER_NAME"];

if(isset($_GET["path"])){
    $path = strtolower($_GET["path"]);
    unset($_GET["path"]);
    header("Location: http://".$server."/" . $path . (!empty($_GET) ? "?" . http_build_query($_GET) : ''));
} else {
    header("Location: http://".$server."/");
}