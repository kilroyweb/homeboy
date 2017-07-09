<?php

namespace App\Commands;

use App\FileManagers\HomesteadFileManager;
use App\FileManagers\HostsFileManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

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
    private $homesteadProvisionCommand;

    protected function configure()
    {
        $this
            ->setName('host')
            ->setDescription('Host a new site')
            ->setHelp("");
    }

    private function init(InputInterface $input, OutputInterface $output){
        $this->inputInterface = $input;
        $this->outputInterface = $output;
        $this->updateFromConfig();
        $this->questionHelper = $this->getHelper('question');
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
        $this->homesteadProvisionCommand = getenv('HOMESTEAD_PROVISION_COMMAND');
        $this->domainExtension = getenv('DEFAULT_DOMAIN_EXTENSION');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->init($input, $output);

        $this->name = $this->getNameFromQuestion();

        if($this->useComposer){
            $this->composerProject = $this->getComposerProjectFromQuestion();
        }

        $this->folder = $this->getFolderFromQuestion();

        if($this->composerProject != 'laravel/laravel')
        {
            $this->folderSuffix = $this->getFolderSuffixFromQuestion();
        }

        $this->database = $this->getDatabaseFromQuestion();

        $this->domain = $this->getDomainFromQuestion();

        $taskConfirmation = $this->getTaskConfirmationFromQuestion();

        if($taskConfirmation){

            if($this->useComposer && !empty($this->composerProject)){
                $output->writeln('<info>Creating project..</info>');
                $this->createProject();
            }

            $output->writeln('<info>Create host ('.$this->domain.')...</info>');
            $this->updateHostsFile();

            $output->writeln('<info>Update Vagrant site mapper ('.$this->folder.')</info>');
            $this->updateHomesteadSites();

            $output->writeln('<info>Update Vagrant database ('.$this->database.')</info>');
            $this->updateHomesteadDatabases();

            $output->writeln('<info>Provision Vagrant</info>');
            $this->provisionHomestead();

            $output->writeln('<success>Complete!</success>');

            $output->writeln('Visit: http://' . $this->domain);

        }else{
            $output->writeln('<error>Tasks cancelled</error>');
        }

        return;

    }

    private function getNameFromQuestion(){
        $question = new Question('What is your project\'s name? ', 'project-' . time());
        return $this->questionHelper->ask($this->inputInterface, $this->outputInterface, $question);
    }

    private function getComposerProjectFromQuestion(){
        $question = new Question("What composer project? ({$this->composerProject}): ", $this->composerProject);
        return $this->questionHelper->ask($this->inputInterface, $this->outputInterface, $question);
    }

    private function getFolderFromQuestion(){
        $question = new Question("What local directory will store your project? ($this->folder): ", $this->folder);
        return $this->questionHelper->ask($this->inputInterface, $this->outputInterface, $question);
    }

    private function getFolderSuffixFromQuestion(){        
        $question = new Question("Point site to? ({$this->folderSuffix}): ", $this->folderSuffix);
        return $this->questionHelper->ask($this->inputInterface, $this->outputInterface, $question);
    }

    private function getDatabaseFromQuestion(){
        $default = $this->defaultDatabaseNameFromKey($this->name);
        $question = new Question('Database Name? ('.$default.'): ', $default);
        return $this->questionHelper->ask($this->inputInterface, $this->outputInterface, $question);
    }

    private function getDomainFromQuestion(){
        $default = $this->defaultDomainNameFromKey($this->database);
        $question = new Question('Development Domain? ('.$default.'): ', $default);
        return $this->questionHelper->ask($this->inputInterface, $this->outputInterface, $question);
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
        $question = new Question('Run tasks? ('.$default.'): ', $default);
        $response = $this->questionHelper->ask($this->inputInterface, $this->outputInterface, $question);
        if(strtoupper($response) == 'Y'){
            return true;
        }
        return false;
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
        $this->outputInterface->writeln("cd {$this->folder} && composer create-project {$this->composerProject} {$this->name}");
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
        if(!empty($this->homesteadProvisionCommand)){
            $shellOutput = shell_exec($this->homesteadProvisionCommand);
        }else{
            $shellOutput = shell_exec('cd '.$this->homesteadBoxPath.' && vagrant provision');
        }
    }


}