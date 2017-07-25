<?php

namespace App\Commands;

use App\Configuration\Config;
use App\FileManagers\HomesteadFileManager;
use App\FileManagers\HostsFileManager;
use App\Input\Interrogator;
use App\Support\Traits\RequireEnvFile;
use App\Support\Vagrant\Vagrant;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Host extends Command
{

    use RequireEnvFile;

    private $questionHelper;
    private $inputInterface;
    private $outputInterface;

    private $config;

    private $name;
    private $useComposer;
    private $composerProject;
    private $folder;
    private $folderSuffix;
    private $database;
    private $domain;
    private $useDefaults=false;
    private $skipConfirmation=false;

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
            ->setName('host')
            ->setDescription('Host a new site')
            ->setHelp("")
            ->addCommandOptions();
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
            'use-composer',
            null,
            InputOption::VALUE_REQUIRED,
            'Use Composer',
            null
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
        if($this->inputInterface->getOption('use-composer')){
            $this->useComposer = boolval($this->inputInterface->getOption('use-composer'));
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

        $projectName = 'project-' . time();
        $this->useComposer = $this->config->getUseComposer();
        $this->composerProject = $this->config->getComposerProject();


        if($this->useDefaults){

            if(is_null($this->name)) {
                $this->name = $projectName;
            }
            $this->folder = $this->config->getFolder();
            $this->folderSuffix = $this->config->getFolderSuffix();
            $this->folderSuffix = rtrim($this->folderSuffix, "/");
            $this->database = $this->defaultDatabaseNameFromKey($this->name);
            $this->domain = $this->defaultDomainNameFromKey($this->name);

        }else{

            if(is_null($this->name)){
                $this->name = $this->interrogator->ask(
                    'What is your project\'s name?',
                    $projectName
                );
            }

            $useComposerDefault = 'Y';
            if(!$this->config->getUseComposer()){
                $useComposerDefault = 'N';
            }
            $useComposerInput = $this->interrogator->ask(
                'Use Composer?',
                $useComposerDefault
            );
            if(strtoupper($useComposerInput) == 'Y'){
                $this->useComposer = true;
            }else{
                $this->useComposer = false;
            }

            if ($this->useComposer) {
                $this->composerProject = $this->interrogator->ask(
                    'What composer project?',
                    $this->config->getComposerProject()
                );
            }

            $this->folder = $this->interrogator->ask(
                'What local directory will store your project?',
                $this->config->getFolder()
            );

            if ($this->composerProject != 'laravel/laravel') {
                $this->folderSuffix = $this->interrogator->ask(
                    'Point site to?',
                    $this->config->getFolderSuffix()
                );
            }else{
                $this->folderSuffix = $this->config->getFolderSuffix();
            }

            $this->folderSuffix = rtrim($this->folderSuffix, "/");

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
        if($this->useComposer){
            $this->outputInterface->writeln("- Run Command: cd {$this->folder} && composer create-project {$this->composerProject} {$this->name}");
        }
        $this->outputInterface->writeln('- ('.$this->config->getHostsPath().') add line: '.$this->config->getHostIP().' '.$this->domain);
        $this->outputInterface->writeln('- ('.$this->config->getHomesteadPath().') map : '.$this->domain.' to '.$this->config->getHomesteadSitesPath().$this->name.$this->folderSuffix);
        $this->outputInterface->writeln('- ('.$this->config->getHomesteadPath().') add to databases: '.$this->database);
        if(!empty($this->homesteadProvisionCommand)){
            $this->outputInterface->writeln('- Run Command: '.$this->homesteadProvisionCommand);
        }else{
            $this->outputInterface->writeln('- Run Command: cd '.$this->config->getHomesteadBoxPath().' && vagrant provision');
        }

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
        if($this->useComposer){
            $this->outputInterface->writeln('<info>Creating project...</info>');
            $this->createProject();
        }

        $this->outputInterface->writeln('<info>Adding Domain to hosts file ('.$this->domain.')...</info>');
        $this->updateHostsFile();

        $this->outputInterface->writeln('<info>Mapping '.$this->domain.' in "'.$this->config->getHomesteadPath().'"...</info>');
        $this->updateHomesteadSites();

        $this->outputInterface->writeln('<info>Adding database ('.$this->database.') to "'.$this->config->getHomesteadPath().'"...</info>');
        $this->updateHomesteadDatabases();

        $this->outputInterface->writeln('<info>Provisioning Vagrant...</info>');
        $this->provisionHomestead();

        $this->outputInterface->writeln('');
        $this->outputInterface->writeln('<info>Complete! Visit: http://'.$this->domain.'</info>');
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
        $key = $key.$this->config->getDomainExtension();
        return $key;
    }

    private function createProject()
    {
        if(!empty($this->config->getAccessLocalSitesDirectoryCommand())){
            $shellOutput = shell_exec($this->config->getAccessLocalSitesDirectoryCommand()." && composer create-project {$this->composerProject} {$this->name}");
        }else{
            $shellOutput = shell_exec("cd {$this->folder} && composer create-project {$this->composerProject} {$this->name}");
        }
    }

    private function updateHostsFile(){
        $fileManager = new HostsFileManager($this->config->getHostsPath());
        $fileManager->appendLine($this->config->getHostIP().' '.$this->domain);
    }

    private function updateHomesteadSites(){
        $fileManager = new HomesteadFileManager($this->config->getHomesteadPath());
        $fileManager->addMapLineToSites($this->domain, $this->config->getHomesteadSitesPath().$this->name.$this->folderSuffix);
    }

    private function updateHomesteadDatabases(){
        $fileManager = new HomesteadFileManager($this->config->getHomesteadPath());
        $fileManager->addDatabase($this->database);
    }

    private function provisionHomestead(){
        $this->vagrant->provision();
    }


}