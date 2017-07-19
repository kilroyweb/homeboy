<?php

namespace App\Support\Traits;

trait RequireEnvFile{

    private function hasDotEnvFile(){
        if($this->config->hasDotEnvFile()){
            $this->outputInterface->writeln('<error>('.$this->config->dotEnvFilePath().') Not found. Run "homeboy setup"</error>');
            die();
        }
    }

}