<?php

namespace App\Support\Traits;

trait RequireEnvFile{

    private function hasDotEnvFile(){
        if($this->config->hasDotEnvFile()){
            $this->outputInterface->writeln('<error>No .env file found. Run "homeboy setup"</error>');
            die();
        }
    }

}