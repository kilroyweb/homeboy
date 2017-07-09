<?php

namespace App\Commands;

use App\FileManagers\HomesteadFileManager;
use App\FileManagers\HostsFileManager;
use App\Input\Interrogator;
use App\Support\Vagrant\Vagrant;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Host extends Command
{

    private $questionHelper;
    private $inputInterface;
    private $outputInterface;

    private $name;
    private $composerProject;
    private $useComposer;
    private $folder;
    private $folderSuffix;
    private $database;
    private $domain;
    private $domainExtension;
    private $hostIP;
    private $hostPath;
    private $homesteadPath;
    private $homesteadSitesPath;
    private $homesteadBoxPath;
    private $homesteadAccessDirectoryCommand;
    private $useDefaults=false;
    private $skipConfirmation=false;

    private $interrogator;

    private $vagrant;

    protected function configure()
    {
        $this
            ->setName('host')
            ->setDescription('Host a new site')
            ->setHelp("")
            ->addCommandOptions();
    }

    private function init(InputInterface $input, OutputInterface $output){
        $this->inputInterface = $input;
        $this->outputInterface = $output;
        $this->questionHelper = $this->getHelper('question');
        $this->interrogator = new Interrogator($input, $output, $this->getHelper('question'));
        $this->updateFromConfig();
        $vagrantAccessDirectoryCommand = 'cd '.$this->homesteadBoxPath;
        if(!empty($this->homesteadAccessDirectoryCommand)){
            $vagrantAccessDirectoryCommand = $this->homesteadAccessDirectoryCommand;
        }
        $this->vagrant = new Vagrant($vagrantAccessDirectoryCommand);
    }

    private function addCommandOptions(){
        $this->addOption(
            'use-defaults',
            null,
            InputOption::VALUE_NONE,
            'Ignore questions and use defaults'
        );
        $this->addOption(
            'skip-confirmation',
            null,
            InputOption::VALUE_NONE,
            'Skip Confirmation'
        );
        $this->addOption(
            'name',
            null,
            InputOption::VALUE_REQUIRED,
            'Project Name',
            null
        );
        $this->addOption(
            'database',
            null,
            InputOption::VALUE_REQUIRED,
            'Database',
            null
        );
        $this->addOption(
            'domain',
            null,
            InputOption::VALUE_REQUIRED,
            'Development Domain',
            null
        );
    }

    private function updateFromConfig(){
        $this->folder = getenv('LOCAL_SITES_PATH');
        $this->folderSuffix = getenv('DEFAULT_FOLDER_SUFFIX');
        $this->useComposer = boolval(getenv('USE_COMPOSER'));
        $this->composerProject = getenv('DEFAULT_COMPOSER_PROJECT');
        $this->hostPath = getenv('HOSTS_FILE_PATH');
        $this->hostIP = getenv('HOMESTEAD_HOST_IP');
        $this->homesteadPath = getenv('HOMESTEAD_FILE_PATH');
        $this->homesteadSitesPath = getenv('HOMESTEAD_SITES_PATH');
        $this->homesteadBoxPath = getenv('HOMESTEAD_BOX_PATH');
        $this->homesteadAccessDirectoryCommand = getenv('HOMESTEAD_ACCESS_DIRECTORY_COMMAND');
        $this->domainExtension = getenv('DEFAULT_DOMAIN_EXTENSION');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->init($input, $output);
        $this->updateFromOptions();
        $this->interrogate();

        if($this->skipConfirmation){
            $taskConfirmation = true;
        }else{
            $taskConfirmation = $this->getTaskConfirmationFromQuestion();
        }

        if($taskConfirmation){
            $this->runTasks();
        }else{
            $output->writeln('<error>Tasks cancelled</error>');
        }

        return;

    }

    private function updateFromOptions(){
        if($this->inputInterface->getOption('use-defaults')){
            $this->useDefaults = boolval($this->inputInterface->getOption('use-defaults'));
        }
        if($this->inputInterface->getOption('skip-confirmation')){
            $this->skipConfirmation = boolval($this->inputInterface->getOption('skip-confirmation'));
        }
        if($this->inputInterface->getOption('name')){
            $this->name = $this->inputInterface->getOption('name');
        }
        if($this->inputInterface->getOption('database')){
            $this->database = $this->inputInterface->getOption('database');
        }else{
            $this->database = $this->defaultDatabaseNameFromKey($this->name);
        }
        if($this->inputInterface->getOption('domain')){
            $this->domain = $this->inputInterface->getOption('domain');
        }else{
            $this->domain = $this->defaultDomainNameFromKey($this->name);
        }
    }

    private function interrogate(){
        if(!$this->useDefaults) {

            if(is_null($this->name)){
                $this->name = $this->interrogator->ask(
                    'What is your project\'s name?',
                    'project-' . time()
                );
            }

            if ($this->useComposer) {
                $this->composerProject = $this->interrogator->ask(
                    'What composer project?',
                    $this->composerProject
                );
            }

            $this->folder = $this->interrogator->ask(
                'What local directory will store your project?',
                $this->folder
            );

            if ($this->composerProject != 'laravel/laravel') {
                $this->folderSuffix = $this->interrogator->ask(
                    'Point site to?',
                    $this->folderSuffix
                );
            }

            if(!$this->inputInterface->getOption('database')) {
                $this->database = $this->defaultDatabaseNameFromKey($this->name);
                $this->database = $this->interrogator->ask(
                    'Database Name?',
                    $this->database
                );
            }

            if(!$this->inputInterface->getOption('domain')) {
                $this->domain = $this->defaultDomainNameFromKey($this->name);
                $this->domain = $this->interrogator->ask(
                    'Development Domain?',
                    $this->domain
                );
            }

        }
    }

    private function getTaskConfirmationFromQuestion(){
        $this->outputInterface->writeln('<info>The following tasks will be executed:</info>');
        if($this->useComposer && !empty($this->composerProject)){
            $this->outputInterface->writeln("- Run Command: cd {$this->folder} && composer create-project {$this->composerProject} {$this->name}");
        }
        $this->outputInterface->writeln('- ('.$this->hostPath.') add line: '.$this->hostIP.' '.$this->domain);
        $this->outputInterface->writeln('- ('.$this->homesteadPath.') map : '.$this->domain.' to '.$this->homesteadSitesPath.$this->name.$this->folderSuffix);
        $this->outputInterface->writeln('- ('.$this->homesteadPath.') add to databases: '.$this->database);
        if(!empty($this->homesteadProvisionCommand)){
            $this->outputInterface->writeln('- Run Command: '.$this->homesteadProvisionCommand);
        }else{
            $this->outputInterface->writeln('- Run Command: cd '.$this->homesteadBoxPath.' && vagrant provision');
        }
        $default = 'Y';

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
        if($this->useComposer && !empty($this->composerProject)){
            $this->outputInterface->writeln('<info>Creating project...</info>');
            $this->createProject();
        }

        $this->outputInterface->writeln('<info>Adding Domain to hosts file ('.$this->domain.')...</info>');
        $this->updateHostsFile();

        $this->outputInterface->writeln('<info>Mapping '.$this->domain.' in "'.$this->homesteadPath.'"...</info>');
        $this->updateHomesteadSites();

        $this->outputInterface->writeln('<info>Adding database ('.$this->database.') to "'.$this->homesteadPath.'"...</info>');
        $this->updateHomesteadDatabases();

        $this->outputInterface->writeln('<info>Provisioning Vagrant...</info>');
        $this->provisionHomestead();

        $this->outputInterface->writeln('<success>Complete! Visit: http://'.$this->domain.'</success>');
    }

    private function defaultDatabaseNameFromKey($key){
        $key = strtolower($key);
        $key = str_replace(' ','-',$key);
        $key = str_replace('_','-',$key);
        $key = preg_replace("/[^A-Za-z0-9\-]/", '', $key);
        return $key;
    }

    private function defaultDomainNameFromKey($key){
        $key = strtolower($key);
        $key = preg_replace("/[^A-Za-z0-9]/", '', $key);
        $key = $key.$this->domainExtension;
        return $key;
    }

    private function createProject()
    {
        $shellOutput = shell_exec("cd {$this->folder} && composer create-project {$this->composerProject} {$this->name}");
    }

    private function updateHostsFile(){
        $fileManager = new HostsFileManager($this->hostPath);
        $fileManager->appendLine($this->hostIP.' '.$this->domain);
    }

    private function updateHomesteadSites(){
        $fileManager = new HomesteadFileManager($this->homesteadPath);
        $fileManager->addMapLineToSites($this->domain, $this->homesteadSitesPath.$this->name.$this->folderSuffix);
    }

    private function updateHomesteadDatabases(){
        $fileManager = new HomesteadFileManager($this->homesteadPath);
        $fileManager->addDatabase($this->database);
    }

    private function provisionHomestead(){
        $this->vagrant->provision();
    }


}