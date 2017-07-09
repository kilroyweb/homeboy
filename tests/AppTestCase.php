<?php

namespace Tests\AppTestCase;

use PHPUnit\Framework\TestCase;

class AppTestCase extends TestCase{

    public function basePath($path=null){
        $testCaseDirectory = dirname(__FILE__);
        $basePath = preg_replace('/\/tests$/', '', $testCaseDirectory);
        if(empty($path)){
            return $basePath;
        }
        if(substr($path,0,1)){
            $path = '/'.$path;
        }
        $basePath = $basePath.$path;
        return $basePath;
    }

}