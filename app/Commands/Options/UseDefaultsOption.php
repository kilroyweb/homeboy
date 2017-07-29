<?php

namespace App\Commands\Options;

use Symfony\Component\Console\Input\InputOption;

class UseDefaultsOption extends BaseOption {

    protected $name = 'use-defaults';
    protected $mode = InputOption::VALUE_NONE;
    protected $description = 'Ignore questions and use defaults';

}