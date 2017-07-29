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
use App\Support\Traits\RequireEnvFile;
use App\Support\Vagrant\Vagrant;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
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

    private function addOptionFromClassName($className){
        $instance = new $className;
        $this->addOption(
            $instance->getName(),
            $instance->getShortcut(),
            $instance->getMode(),
            $instance->getDescription(),
            $instance->getDefault()
        );
    }

    private function addCommandOptions(){
        $this->addOptionFromClassName(UseDefaultsOption::class);
        $this->addOptionFromClassName(SkipConfirmationOption::class);
        $this->addOptionFromClassName(UseComposerOption::class);
        $this->addOptionFromClassName(ProjectNameOption::class);
        $this->addOptionFromClassName(DatabaseOption::class);
        $this->addOptionFromClassName(DomainOption::class);
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
            $this->database = DatabaseNameFormatter::make($this->name);
        }
        if($this->inputInterface->getOption('domain')){
            $this->domain = $this->inputInterface->getOption('domain');
        }else{
            $this->domain = DomainFormatter::make($this->name, $this->config->getDomainExtension());
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
            $this->database = DatabaseNameFormatter::make($this->name);
            $this->domain = DomainFormatter::make($this->name,$this->config->getDomainExtension());

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
                $this->database = DatabaseNameFormatter::make($this->name);
                $this->database = $this->interrogator->ask(
                    'Database Name?',
                    $this->database
                );
            }

            if(!$this->inputInterface->getOption('domain')) {
                $this->domain = DomainFormatter::make($this->name,$this->config->getDomainExtension());
                $this->domain = $this->interrogator->ask(
                    'Development Domain?',
                    $this->domain
                );
            }

        }
    }

    private function composerCreateProjectAction(){
        if(!empty($this->config->getAccessLocalSitesDirectoryCommand())){
            $accessCommand = $this->config->getAccessLocalSitesDirectoryCommand();
        }else{
            $accessCommand = 'cd '.$this->folder;
        }
        return new ComposerCreateProject($accessCommand, $this->composerProject, $this->name);
    }

    private function hostsAddLineAction(){
        return new HostsAddLine($this->config->getHostsPath(), $this->config->getHostIP(), $this->domain);
    }

    private function homesteadMapSite(){
        return new HomesteadMapSite($this->config->getHomesteadPath(), $this->domain, $this->config->getHomesteadSitesPath().$this->name.$this->folderSuffix);
    }

    private function homesteadAddDatabase(){
        return new HomesteadAddDatabase($this->config->getHomesteadPath(), $this->database);
    }

    private function provisionHomesteadAction(){
        return new ProvisionHomestead($this->vagrant);
    }

    private function getTaskConfirmationFromQuestion(){
        $this->outputInterface->writeln('<info>The following tasks will be executed:</info>');

        if($this->useComposer){
            $this->outputInterface->writeln("- ".$this->composerCreateProjectAction()->confirmationMessage());
        }

        $this->outputInterface->writeln('- '.$this->hostsAddLineAction()->confirmationMessage());
        $this->outputInterface->writeln('- '.$this->homesteadMapSite()->confirmationMessage());
        $this->outputInterface->writeln('- '.$this->homesteadAddDatabase()->confirmationMessage());

        $this->outputInterface->writeln('- '.$this->provisionHomesteadAction()->confirmationMessage() );

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
            $this->outputInterface->writeln('<info>'.$this->composerCreateProjectAction()->actionMessage().'...</info>');
            $this->composerCreateProjectAction()->run();
        }

        $this->outputInterface->writeln('<info>'.$this->hostsAddLineAction()->actionMessage().'...</info>');
        $this->hostsAddLineAction()->run();

        $this->outputInterface->writeln('<info>'.$this->homesteadMapSite()->actionMessage().'...</info>');
        $this->homesteadMapSite()->run();

        $this->outputInterface->writeln('<info>'.$this->homesteadAddDatabase()->actionMessage().'...</info>');
        $this->homesteadAddDatabase()->run();

        $this->outputInterface->writeln('<info>'.$this->provisionHomesteadAction()->actionMessage().'...</info>');
        $this->provisionHomesteadAction()->run();

        $this->outputInterface->writeln('');
        $this->outputInterface->writeln('<info>Complete! Visit: http://'.$this->domain.'</info>');
    }

}