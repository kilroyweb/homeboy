<?php

function basePath($path=null){
    $basePath = dirname(__FILE__);
    if(empty($path)){
        return $basePath;
    }
    if(substr($path,0,1)){
        $path = '/'.$path;
    }
    $basePath = $basePath.$path;
    return $basePath;
}