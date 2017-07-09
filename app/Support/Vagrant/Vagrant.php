<?php

namespace App\Support\Vagrant;

class Vagrant{

    private $accessDirectoryCommand;

    public function __construct($accessDirectoryCommand)
    {
        $this->setAccessDirectoryCommand($accessDirectoryCommand);
    }

    public function setAccessDirectoryCommand($accessDirectoryCommand){
        $this->accessDirectoryCommand = $accessDirectoryCommand;
    }

    public function provision(){
        return $this->runAction('provision');
    }

    public function runAction($action){
        return shell_exec($this->accessDirectoryCommand.' && vagrant '.$action);
    }

}