<?php

namespace App\FileManagers;

abstract class BaseFileManager{

    private $filePath;

    public function __construct($filePath, $options=[])
    {
        $this->setFilePath($filePath);
    }

    public function setFilePath($filePath){
        $this->filePath = $filePath;
    }

    public function getFileContents(){
        return file_get_contents($this->filePath);
    }

    public function newFileContents($contents){
        return file_put_contents($this->filePath, $contents);
    }

    public function appendLine($line){
        return file_put_contents($this->filePath, PHP_EOL.$line, FILE_APPEND | LOCK_EX);
    }

}