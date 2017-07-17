<?php

namespace App\Commands;

use App\Configuration\Config;
use App\Support\Traits\RequireEnvFile;
use App\Support\Vagrant\Vagrant as VagrantSupport;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Vagrant extends Command
{

    use RequireEnvFile;

    private $inputInterface;
    private $outputInterface;

    private $action;

    private $vagrant;
    private $config;

    public function __construct($name = null, Config $config)
    {
        $this->config = $config;
        parent::__construct($name);
    }

    protected function configure()
    {
        $this
            ->setName('vagrant')
            ->setDescription('Run a vagrant command')
            ->setHelp("")
            ->addCommandArguments();
    }

    private function init(InputInterface $input, OutputInterface $output){
        $this->inputInterface = $input;
        $this->outputInterface = $output;
        $this->hasEnvFile();
        $vagrantAccessDirectoryCommand = 'cd '.$this->config->getHomesteadBoxPath();
        if(!empty($this->config->getHomesteadAccessDirectoryCommand())){
            $vagrantAccessDirectoryCommand = $this->config->getHomesteadAccessDirectoryCommand();
        }
        $this->vagrant = new VagrantSupport($vagrantAccessDirectoryCommand);
    }

    private function addCommandArguments(){
        $this->addArgument(
            'action',
            InputArgument::REQUIRED,
            'Vagrant command to run'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->init($input, $output);
        $this->updateFromOptions();
        $this->outputInterface->writeLn($this->vagrant->runAction($this->action));
        return;
    }

    private function updateFromOptions(){
        $this->action = $this->inputInterface->getArgument('action');
    }

}