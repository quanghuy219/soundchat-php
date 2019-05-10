<?php


namespace App\Utils;


use Illuminate\Support\Str;

class Helper
{
    public static function createRoomFingerprint() {
        return Str::random(8);
    }
}