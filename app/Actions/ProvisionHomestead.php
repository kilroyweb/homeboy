<?php

namespace App\Actions;

use App\Actions\Interfaces\ActionInterface;
use App\Support\Vagrant\Vagrant;

class ProvisionHomestead extends BaseAction implements ActionInterface {

    private $vagrant;

    public function __construct(Vagrant $vagrant)
    {
        $this->vagrant = $vagrant;
    }

    public function confirmationMessage(){
        return 'Run Command: '.$this->vagrant->getActionCommand('provision');
    }

    public function actionMessage(){
        return 'Provisioning Vagrant';
    }

    public function run(){
        $this->vagrant->provision();
    }

}