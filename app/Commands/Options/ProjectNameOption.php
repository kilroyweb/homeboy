<?php

namespace App\Commands\Options;

use Symfony\Component\Console\Input\InputOption;

class ProjectNameOption extends BaseOption {

    protected $name = 'name';
    protected $mode = InputOption::VALUE_REQUIRED;
    protected $description = 'Project Name';

}