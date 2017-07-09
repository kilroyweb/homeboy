<?php

namespace App\Input;

use Symfony\Component\Console\Question\Question;

class Interrogator{

    private $inputInterface;
    private $outputInterface;
    private $questionHelper;

    public function __construct($inputInterface, $outputInterface,$questionHelper)
    {
        $this->inputInterface = $inputInterface;
        $this->outputInterface = $outputInterface;
        $this->questionHelper = $questionHelper;
    }

    public function ask($question, $default=null){
        if(!is_null($default)){
            $question = $question.' ('.$default.') ';
        }
        $questionObject = new Question($question, $default);
        return $this->questionHelper->ask($this->inputInterface, $this->outputInterface, $questionObject);
    }

}