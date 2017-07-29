<?php

namespace App\Actions;

use App\Actions\Interfaces\ActionInterface;
use App\FileManagers\HostsFileManager;

class HostsAddLine extends BaseAction implements ActionInterface {

    private $filePath;
    private $ipAddress;
    private $domain;

    public function __construct($filePath, $ipAddress, $domain)
    {
        $this->filePath = $filePath;
        $this->ipAddress = $ipAddress;
        $this->domain = $domain;
    }

    public function confirmationMessage(){
        return '('.$this->filePath.') add line: '.$this->ipAddress.' '.$this->domain;
    }

    public function actionMessage(){
        return 'Adding Domain to hosts file ('.$this->domain.')';
    }

    public function run(){
        $fileManager = new HostsFileManager($this->filePath);
        $fileManager->appendLine($this->ipAddress.' '.$this->domain);
    }

}