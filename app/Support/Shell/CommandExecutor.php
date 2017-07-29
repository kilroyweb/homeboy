<?php

namespace App\Support\Shell;

class CommandExecutor{

    protected $command;
    protected $execute=true;

    public function setExecute($execute){
        $this->execute = $execute;
    }

    public function getExecute(){
        return $this->execute;
    }

    public function setCommand($command){
        $this->command = $command;
    }

    public function getCommand(){
        return $this->command;
    }

    public function run($command=null){
        if($command){
            $this->setCommand($command);
        }
        if($this->execute){
            shell_exec($this->getCommand());
        }
    }

}