<?php

namespace App\Support\Traits;

trait HasCommandOptions{

    private function addOptionFromClassName($className){
        $instance = new $className;
        $this->addOption(
            $instance->getName(),
            $instance->getShortcut(),
            $instance->getMode(),
            $instance->getDescription(),
            $instance->getDefault()
        );
    }

}