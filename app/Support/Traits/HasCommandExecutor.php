<?php

namespace App\Support\Traits;

trait HasCommandExecutor{

    public function setCommandExecutor($commandExecutor){
        $this->commandExecutor = $commandExecutor;
    }

    public function getCommandExecutor(){
        if(!$this->commandExecutor){
            $this->commandExecutor = new \App\Support\Shell\CommandExecutor();
        }
        return $this->commandExecutor;
    }

}