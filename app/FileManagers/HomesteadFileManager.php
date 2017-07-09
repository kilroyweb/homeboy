<?php

namespace App\FileManagers;

class HomesteadFileManager extends BaseFileManager {

    private function getTabSpacing(){
        return "    ";
    }

    public function addMapLineToSites($domain, $directory){
        $homesteadContents = $this->getFileContents();
        $mapLine = $this->getTabSpacing()."- map: ".$domain;
        $toLine = $this->getTabSpacing()."  to: ".$directory;
        $newLines = $mapLine.PHP_EOL.$toLine;
        $search = "sites:";
        $homesteadContents = str_replace($search,$search.PHP_EOL.$newLines,$homesteadContents);
        return $this->newFileContents($homesteadContents);
    }

    public function addDatabase($database){
        $homesteadContents = $this->getFileContents();
        $line = $this->getTabSpacing()."- ".$database;
        $search = "databases:";
        $homesteadContents = str_replace($search,$search.PHP_EOL.$line,$homesteadContents);
        return $this->newFileContents($homesteadContents);
    }
    

}