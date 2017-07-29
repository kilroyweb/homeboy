<?php

namespace App\Commands\Options;

class BaseOption{

    protected $name;
    protected $shortcut;
    protected $mode;
    protected $description;
    protected $default;

    public function getName(){
        return $this->name;
    }

    public function getShortcut(){
        return $this->shortcut;
    }

    public function getMode(){
        return $this->mode;
    }

    public function getDescription(){
        return $this->description;
    }

    public function getDefault(){
        return $this->default;
    }

}