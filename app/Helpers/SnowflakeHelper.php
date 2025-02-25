<?php

namespace App\Helpers;

class SnowflakeGenerator
{
    private static $epoch = 1672531200000; 

    public static function generateId()
    {
        $timestamp = floor(microtime(true) * 1000) - self::$epoch;
        $random = mt_rand(0, 4095); 

        return ($timestamp << 12) | $random;
    }
}
