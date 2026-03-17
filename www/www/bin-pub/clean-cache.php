<?php
/**
 * SCRIPT FOR DELETE NETTE CACHE FILES
 */

CONST AUTH_TOKEN = "8zzBCNuKzix4oagnLVQVARahKlfrCaL";

if (isset($_GET["token"]) && !empty($_GET["token"])) {
    $token = $_GET["token"];
    if (AUTH_TOKEN === $token) {
        $cacheDirectory = dirname(__DIR__, 2) . '/temp/cache';
        if (is_dir($cacheDirectory)) {
            rrmdir($cacheDirectory);
            echo "DELETE OK";
        } else {
            echo "MISSING CACHE DIRECTORY";
        }
    } else {
        echo "INVALID AUTH TOKEN";
    }
} else {
    echo "INVALID REQUEST";
}


// RECURSIVELY - DELETE FOLDER
function rrmdir($dir)
{
    if (is_dir($dir)) {
        $objects = scandir($dir);
        foreach ($objects as $object) {
            if ($object != "." && $object != "..") {
                if (filetype($dir . "/" . $object) == "dir") rrmdir($dir . "/" . $object); else unlink($dir . "/" . $object);
            }
        }
        reset($objects);
        rmdir($dir);
    }
}