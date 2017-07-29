<?php

namespace App\Commands\Options;

use Symfony\Component\Console\Input\InputOption;

class SkipConfirmationOption extends BaseOption {

    protected $name = 'skip-confirmation';
    protected $mode = InputOption::VALUE_NONE;
    protected $description = 'Skip Confirmation';

}