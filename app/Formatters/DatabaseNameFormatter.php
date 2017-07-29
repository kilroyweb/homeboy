<?php

namespace App\Formatters;

class DatabaseNameFormatter {

    public static function make($original)
    {
        $value = strtolower($original);
        $value = str_replace(' ','-',$value);
        $value = str_replace('_','-',$value);
        $value = preg_replace("/[^A-Za-z0-9\-]/", '', $value);
        return $value;
    }


}