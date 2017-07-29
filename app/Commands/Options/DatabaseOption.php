<?php

namespace App\Commands\Options;

use Symfony\Component\Console\Input\InputOption;

class DatabaseOption extends BaseOption {

    protected $name = 'database';
    protected $mode = InputOption::VALUE_REQUIRED;
    protected $description = 'Database';

}