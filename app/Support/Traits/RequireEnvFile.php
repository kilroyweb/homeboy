<?php

namespace App\Support\Traits;

use App\Configuration\Config;

trait RequireEnvFile{

    private function hasEnvFile(){
        if(!Config::hasEnvFile()){
            $this->outputInterface->writeln('<error>No .env file found. Run "homeboy setup"</error>');
            die();
        }
    }

}