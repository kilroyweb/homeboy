<?php

namespace App\Commands\Vagrant;

use App\Actions\VagrantRunAction;
use App\Configuration\Config;
use App\Support\Traits\HasCommandExecutor;
use App\Support\Traits\RequireEnvFile;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class VagrantRun extends Command
{

    use RequireEnvFile;
    use HasCommandExecutor;

    private $inputInterface;
    private $outputInterface;

    private $action;

    private $config;

    private $commandExecutor;

    public function __construct($name = null, Config $config)
    {
        $this->config = $config;
        parent::__construct($name);
    }

    protected function configure()
    {
        $this
            ->setName('vagrant:run')
            ->setDescription('Run a vagrant command')
            ->setHelp("")
            ->addCommandArguments();
    }

    private function init(InputInterface $input, OutputInterface $output){
        $this->inputInterface = $input;
        $this->outputInterface = $output;
        $this->hasDotEnvFile();
    }

    private function addCommandArguments(){
        $this->addArgument(
            'action',
            InputArgument::REQUIRED,
            'Vagrant command to run'
        );
    }

    private function vagrantRunAction(){
        if(!empty($this->config->getHomesteadAccessDirectoryCommand())){
            $accessCommand = $this->config->getHomesteadAccessDirectoryCommand();
        }else{
            $accessCommand = 'cd '.$this->config->getHomesteadBoxPath();
        }
        return new VagrantRunAction($this->getCommandExecutor(),$accessCommand, $this->action);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->init($input, $output);
        $this->updateFromOptions();
        $this->outputInterface->writeLn($this->vagrantRunAction()->actionMessage());
        $this->vagrantRunAction()->run();
        return;
    }

    private function updateFromOptions(){
        $this->action = $this->inputInterface->getArgument('action');
    }

}