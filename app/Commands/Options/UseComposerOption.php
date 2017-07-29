<?php

namespace App\Commands\Options;

use Symfony\Component\Console\Input\InputOption;

class UseComposerOption extends BaseOption {

    protected $name = 'use-composer';
    protected $mode = InputOption::VALUE_REQUIRED;
    protected $description = 'Use Composer';

}