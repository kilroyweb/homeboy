<?php

namespace App\Actions;

use App\Actions\Interfaces\ActionInterface;

class ComposerCreateProjectAction extends BaseAction implements ActionInterface {

    private $commandExecutor;
    private $accessCommand;
    private $project;
    private $name;

    public function __construct($commandExecutor, $accessCommand, $project, $name)
    {
        $this->commandExecutor = $commandExecutor;
        $this->accessCommand = $accessCommand;
        $this->project = $project;
        $this->name = $name;
    }

    private function command(){
        return $this->accessCommand.' && composer create-project '.$this->project.' '.$this->name;
    }

    public function confirmationMessage(){
        return 'Run Command: '.$this->command();
    }

    public function actionMessage(){
        return 'Creating project';
    }

    public function run(){
        $this->commandExecutor->run($this->command());
    }

}