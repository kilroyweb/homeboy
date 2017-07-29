<?php

namespace App\Commands\Options;

use Symfony\Component\Console\Input\InputOption;

class DomainOption extends BaseOption {

    protected $name = 'domain';
    protected $mode = InputOption::VALUE_REQUIRED;
    protected $description = 'Development Domain';

}