<?php

namespace App\Actions;

use App\Actions\Interfaces\ActionInterface;

class VagrantRunAction extends BaseAction implements ActionInterface {

    private $commandExecutor;
    private $accessCommand;
    private $vagrantCommand;

    public function __construct($commandExecutor, $accessCommand, $vagrantCommand)
    {
        $this->commandExecutor = $commandExecutor;
        $this->accessCommand = $accessCommand;
        $this->vagrantCommand = $vagrantCommand;
    }

    private function command(){
        return $this->accessCommand.' && vagrant '.$this->vagrantCommand;
    }

    public function confirmationMessage(){
        return 'Run Command: '.$this->command();
    }

    public function actionMessage(){
        return 'Running ('.$this->command().')';
    }

    public function run(){
        $this->commandExecutor->run($this->command());
    }

}