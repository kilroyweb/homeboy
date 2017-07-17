<?php

namespace App\Commands;

use App\Configuration\Config;
use App\FileManagers\EnvFileManager;
use App\FileManagers\HomesteadFileManager;
use App\FileManagers\HostsFileManager;
use App\Input\Interrogator;
use App\Support\Git\ApplicationVersion;
use App\Support\Vagrant\Vagrant;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Setup extends Command
{

    private $questionHelper;
    private $inputInterface;
    private $outputInterface;

    private $config;

    private $composerProject;

    private $database;
    private $domain;

    private $interrogator;

    private $installType;
    private $systemUserName;
    private $baseUserDirectory;
    private $folder;

    public function __construct($name = null, Config $config)
    {
        $this->config = $config;
        parent::__construct($name);
    }

    protected function configure()
    {
        $this
            ->setName('setup')
            ->setDescription('Setup Homeboy')
            ->setHelp("");
    }

    private function init(InputInterface $input, OutputInterface $output){
        $this->inputInterface = $input;
        $this->outputInterface = $output;
        $this->questionHelper = $this->getHelper('question');
        $this->interrogator = new Interrogator($input, $output, $this->getHelper('question'));
    }

    private function installTypes(){
        return [
            'Windows',
            'Mac',
            'Linux',
        ];
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->init($input, $output);
        $this->outputLogo();
        if(Config::hasEnvFile()){
            $this->outputInterface->writeln('<error>.env file already exists!</error>');
            die();
        }
        $this->outputInterface->writeln('<info>This process attempt to create a new .env file in your homeboy directory.</info>');
        $this->outputInterface->writeln('<info>Please review it for accuracy after this process is complete</info>');
        $this->interrogate();
        $this->runTasks();
        return;

    }

    private function outputLogo(){
        $this->outputInterface->writeln(' 
 _                          _                 
| |__   ___  _ __ ___   ___| |__   ___  _   _ 
| \'_ \ / _ \| \'_ ` _ \ / _ \ \'_ \ / _ \| | | |
| | | | (_) | | | | | |  __/ |_) | (_) | |_| |
|_| |_|\___/|_| |_| |_|\___|_.__/ \___/ \__, |
                                        |___/ ');
        $this->outputInterface->writeln('<comment>Version: '.ApplicationVersion::get().'</comment>');
    }

    private function interrogate(){
        $this->installType = $this->interrogator->choice(
            'OS',
            $this->installTypes()
        );
        $this->systemUserName = $this->interrogator->ask(
            'What is your system user/folder name',
            'Jeff'
        );
        $this->baseUserDirectory = $this->interrogator->ask(
            'The path to your User directory',
            $this->defaultBaseUserDirectory()
        );
    }

    private function runTasks(){
        $envPath = basePath('.env');
        $envContents = $this->generateEnvContents();
        $this->outputInterface->writeln('Writing content to '.$envPath.':');
        $this->outputInterface->writeln($envContents);
        $fileManager = new EnvFileManager($envPath);
        $fileManager->newFileContents($envContents);
        $this->outputInterface->writeln('<info>Complete! Review your generated .env file at '.$envPath.'</info>');
    }

    private function generateEnvContents(){
        $lines = [];
        $lines[] = 'USE_COMPOSER=true';
        $lines[] = 'DEFAULT_COMPOSER_PROJECT=laravel/laravel';
        $lines[] = 'DEFAULT_FOLDER_SUFFIX=/public';
        $lines[] = 'DEFAULT_DOMAIN_EXTENSION=.app';
        $lines[] = 'HOSTS_FILE_PATH='.$this->defaultHostsFilePath();
        $lines[] = 'HOMESTEAD_HOST_IP=192.168.10.10';
        $lines[] = 'HOMESTEAD_FILE_PATH='.$this->defaultHomesteadFilePath();
        $lines[] = 'HOMESTEAD_SITES_PATH=/home/vagrant/Code/';
        $lines[] = 'HOMESTEAD_BOX_PATH='.$this->defaultHomesteadBoxPath();
        $lines[] = 'LOCAL_SITES_PATH='.$this->defaultFolder();
        return implode(PHP_EOL,$lines);
    }

    private function defaultBaseUserDirectory(){
        if($this->installType == 'Windows'){
            return 'C:\\Users\\'.$this->systemUserName;
        }elseif($this->installType == 'Linux'){
            return '/home/'.$this->systemUserName;
        }else{
            return '/Users/'.$this->systemUserName;
        }
    }

    private function defaultFolder(){
        if($this->installType == 'Windows'){
            return $this->defaultBaseUserDirectory().'\\Code';
        }else{
            return $this->defaultBaseUserDirectory().'/Code';
        }
    }

    private function defaultHostsFilePath(){
        if($this->installType == 'Windows'){
            return 'C:\Windows\System32\drivers\etc\hosts';
        }else{
            return '/etc/hosts';
        }
    }

    private function defaultHomesteadBoxPath(){
        if($this->installType == 'Windows'){
            return $this->baseUserDirectory.'\Homestead';
        }else{
            return $this->baseUserDirectory.'/Homestead';
        }
    }

    private function defaultHomesteadFilePath(){
        if($this->installType == 'Windows'){
            return $this->baseUserDirectory.'\Homestead\Homestead.yaml';
        }else{
            return $this->baseUserDirectory.'/Homestead/Homestead.yaml';
        }
    }


}