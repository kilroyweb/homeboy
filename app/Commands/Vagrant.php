<?php

namespace App\Commands;

use App\Support\Vagrant\Vagrant as VagrantSupport;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Vagrant extends Command
{

    private $questionHelper;
    private $inputInterface;
    private $outputInterface;

    private $homesteadBoxPath;
    private $homesteadAccessDirectoryCommand;
    private $action;

    private $vagrant;

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
        $this->questionHelper = $this->getHelper('question');
        $this->updateFromConfig();
        $vagrantAccessDirectoryCommand = 'cd '.$this->homesteadBoxPath;
        if(!empty($this->homesteadAccessDirectoryCommand)){
            $vagrantAccessDirectoryCommand = $this->homesteadAccessDirectoryCommand;
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

    private function updateFromConfig(){
        $this->homesteadBoxPath = getenv('HOMESTEAD_BOX_PATH');
        $this->homesteadAccessDirectoryCommand = getenv('HOMESTEAD_ACCESS_DIRECTORY_COMMAND');
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