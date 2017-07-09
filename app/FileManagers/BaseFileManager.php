<?php

namespace App\FileManagers;

abstract class BaseFileManager{

    private $filePath;
    private $allowWriteFile=true;

    public function __construct($filePath, $options=[])
    {
        $this->setFilePath($filePath);
        $this->setOptions($options);
    }

    public function setFilePath($filePath){
        $this->filePath = $filePath;
    }

    public function setOptions($options=[]){
        if(isset($options['allowWriteFile'])){
            $this->setAllowWriteFile($options['allowWriteFile']);
        }
    }

    public function setAllowWriteFile($allowWriteFile){
        $this->allowWriteFile = $allowWriteFile;
    }

    public function getFileContents(){
        return file_get_contents($this->filePath);
    }

    protected function newFileContents($contents){
        if($this->allowWriteFile){
            return file_put_contents($this->filePath, $contents);
        }else{
            return $contents;
        }
    }

    public function appendLine($line){
        if($this->allowWriteFile){
            return file_put_contents($this->filePath, PHP_EOL.$line, FILE_APPEND | LOCK_EX);
        }else{
            $contents = $this->getFileContents();
            $contents .= PHP_EOL.$line;
            return $contents;
        }

    }

}