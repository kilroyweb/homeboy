<?php

namespace App\Commands;

use App\Configuration\Config;
use App\FileManagers\HomesteadFileManager;
use App\FileManagers\HostsFileManager;
use App\Support\Traits\RequireEnvFile;
use App\Support\Vagrant\Vagrant as VagrantSupport;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class File extends Command
{

    use RequireEnvFile;

    private $inputInterface;
    private $outputInterface;
    private $file;
    private $config;
    private $vagrant;

    public function __construct($name = null, Config $config)
    {
        $this->config = $config;
        parent::__construct($name);
    }

    protected function configure()
    {
        $this
            ->setName('file')
            ->setDescription('View a file contents')
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
            'file',
            InputArgument::REQUIRED,
            'File to view (homestead or hosts)'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->init($input, $output);
        $this->updateFromOptions();
        if($this->file == 'hosts'){
            $fileManager = new HostsFileManager($this->config->getHostsPath());
            $this->outputInterface->writeLn($fileManager->getFileContents());
        }
        if($this->file == 'homestead'){
            $fileManager = new HomesteadFileManager($this->config->getHomesteadPath());
            $this->outputInterface->writeLn($fileManager->getFileContents());
        }
        return;
    }

    private function updateFromOptions(){
        $this->file = $this->inputInterface->getArgument('file');
    }

}