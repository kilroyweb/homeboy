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

    public function getAccessDirectoryCommand(){
        return $this->accessDirectoryCommand;
    }

    public function provision(){
        return $this->runAction('provision');
    }

    public function getActionCommand($action){
        return $this->getAccessDirectoryCommand().' && vagrant '.$action;
    }

    public function runAction($action){
        return shell_exec($this->getActionCommand($action));
    }

}