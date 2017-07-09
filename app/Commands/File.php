<?php

namespace App\Commands;

use App\FileManagers\HomesteadFileManager;
use App\FileManagers\HostsFileManager;
use App\Support\Vagrant\Vagrant as VagrantSupport;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class File extends Command
{

    private $inputInterface;
    private $outputInterface;

    private $hostPath;
    private $homesteadPath;
    private $homesteadBoxPath;
    private $homesteadAccessDirectoryCommand;
    private $file;

    private $vagrant;

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
        $this->updateFromConfig();
        $vagrantAccessDirectoryCommand = 'cd '.$this->homesteadBoxPath;
        if(!empty($this->homesteadAccessDirectoryCommand)){
            $vagrantAccessDirectoryCommand = $this->homesteadAccessDirectoryCommand;
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

    private function updateFromConfig(){
        $this->homesteadBoxPath = getenv('HOMESTEAD_BOX_PATH');
        $this->homesteadAccessDirectoryCommand = getenv('HOMESTEAD_ACCESS_DIRECTORY_COMMAND');
        $this->hostPath = getenv('HOSTS_FILE_PATH');
        $this->homesteadPath = getenv('HOMESTEAD_FILE_PATH');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->init($input, $output);
        $this->updateFromOptions();
        if($this->file == 'hosts'){
            $fileManager = new HostsFileManager($this->hostPath);
            $this->outputInterface->writeLn($fileManager->getFileContents());
        }
        if($this->file == 'homestead'){
            $fileManager = new HomesteadFileManager($this->homesteadPath);
            $this->outputInterface->writeLn($fileManager->getFileContents());
        }
        return;
    }

    private function updateFromOptions(){
        $this->file = $this->inputInterface->getArgument('file');

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

}