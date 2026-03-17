<?php

namespace App\Utils;

class ArrayUtil
{
    public static function contains($str, $arr)
    {
        foreach ($arr as $a) {
            if (stripos($str, $a) !== false){
                return true;
            }
        }
        return false;
    }
}