<?php

namespace App\Actions;

use App\Actions\Interfaces\ActionInterface;
use App\FileManagers\HomesteadFileManager;

class HomesteadAddDatabase extends BaseAction implements ActionInterface {

    private $filePath;
    private $database;

    public function __construct($filePath, $database)
    {
        $this->filePath = $filePath;
        $this->database = $database;
    }

    public function confirmationMessage(){
        return '('.$this->filePath.') add to databases: '.$this->database;
    }

    public function actionMessage(){
        return 'Adding database ('.$this->database.') to "'.$this->filePath.'"';
    }

    public function run(){
        $fileManager = new HomesteadFileManager($this->filePath);
        $fileManager->addDatabase($this->database);
    }

}