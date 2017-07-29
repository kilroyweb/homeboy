<?php

namespace App\Formatters;

class DomainFormatter {

    public static function make($original, $extension)
    {
        $value = strtolower($original);
        $value = preg_replace("/[^A-Za-z0-9]/", '', $value);
        $value = $value.$extension;
        return $value;
    }


}