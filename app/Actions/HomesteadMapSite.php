<?php

namespace App\Actions;

use App\Actions\Interfaces\ActionInterface;
use App\FileManagers\HomesteadFileManager;

class HomesteadMapSite extends BaseAction implements ActionInterface {

    private $filePath;
    private $domain;
    private $projectPath;

    public function __construct($filePath, $domain, $projectPath)
    {
        $this->filePath = $filePath;
        $this->domain = $domain;
        $this->projectPath = $projectPath;
    }

    public function confirmationMessage(){
        return '('.$this->filePath.') map : '.$this->domain.' to '.$this->projectPath;
    }

    public function actionMessage(){
        return 'Mapping '.$this->domain.' to "'.$this->projectPath.'"';
    }

    public function run(){
        $fileManager = new HomesteadFileManager($this->filePath);
        $fileManager->addMapLineToSites($this->domain, $this->projectPath);
    }

}