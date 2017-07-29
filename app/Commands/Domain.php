<?php

namespace App\Commands;

use App\Actions\ComposerCreateProject;
use App\Actions\HomesteadAddDatabase;
use App\Actions\HomesteadMapSite;
use App\Actions\HostsAddLine;
use App\Actions\ProvisionHomestead;
use App\Commands\Options\DatabaseOption;
use App\Commands\Options\DomainOption;
use App\Commands\Options\ProjectNameOption;
use App\Commands\Options\SkipConfirmationOption;
use App\Commands\Options\UseComposerOption;
use App\Commands\Options\UseDefaultsOption;
use App\Configuration\Config;
use App\Formatters\DatabaseNameFormatter;
use App\Formatters\DomainFormatter;
use App\Input\Interrogator;
use App\Support\Traits\HasCommandOptions;
use App\Support\Traits\RequireEnvFile;
use App\Support\Vagrant\Vagrant;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Domain extends Command
{

    use RequireEnvFile;
    use HasCommandOptions;

    private $questionHelper;
    private $inputInterface;
    private $outputInterface;

    private $config;

    private $ipAddress;
    private $domain;

    private $interrogator;

    private $vagrant;

    public function __construct($name = null, Config $config)
    {
        $this->config = $config;
        parent::__construct($name);
    }

    protected function configure()
    {
        $this
            ->setName('domain')
            ->setDescription('Update hosts file to map new domain')
            ->setHelp("");
    }

    private function init(InputInterface $input, OutputInterface $output){
        $this->inputInterface = $input;
        $this->outputInterface = $output;
        $this->hasDotEnvFile();
        $this->questionHelper = $this->getHelper('question');
        $this->interrogator = new Interrogator($input, $output, $this->getHelper('question'));
        $vagrantAccessDirectoryCommand = 'cd '.$this->config->getHomesteadBoxPath();
        if(!empty($this->config->getHomesteadAccessDirectoryCommand())){
            $vagrantAccessDirectoryCommand = $this->config->getHomesteadAccessDirectoryCommand();
        }
        $this->vagrant = new Vagrant($vagrantAccessDirectoryCommand);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->init($input, $output);
        $this->interrogate();

        $taskConfirmation = $this->getTaskConfirmationFromQuestion();

        if($taskConfirmation){
            $this->runTasks();
        }else{
            $output->writeln('<error>Tasks cancelled</error>');
        }

        return;

    }

    private function interrogate(){

        $this->domain = $this->interrogator->ask(
            'Domain?',
            'project-'.time().$this->config->getDomainExtension()
        );

        $this->ipAddress = $this->interrogator->ask(
            'IP Address?',
            $this->config->getHostIP()
        );

    }

    private function hostsAddLineAction(){
        return new HostsAddLine($this->config->getHostsPath(), $this->ipAddress, $this->domain);
    }

    private function getTaskConfirmationFromQuestion(){
        $this->outputInterface->writeln('<info>The following tasks will be executed:</info>');

        $this->outputInterface->writeln('- '.$this->hostsAddLineAction()->confirmationMessage());

        $response = $this->interrogator->ask(
            'Run tasks?',
            'Y'
        );
        if(strtoupper($response) == 'Y'){
            return true;
        }
        return false;
    }

    private function runTasks(){

        $this->outputInterface->writeln('<info>'.$this->hostsAddLineAction()->actionMessage().'...</info>');
        $this->hostsAddLineAction()->run();
        $this->outputInterface->writeln('');
        $this->outputInterface->writeln('<info>Complete!</info>');
    }

}