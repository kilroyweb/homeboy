<?php

namespace App\Commands;

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
        $this->updateFromConfig();
        $this->inputInterface = $input;
        $this->outputInterface = $output;
        $this->questionHelper = $this->getHelper('question');
    }

    private function updateFromConfig(){
        $this->folder = getenv('PROJECTS_FOLDER');
        $this->folderSuffix = getenv('DEFAULT_FOLDER_SUFFIX');
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

        $this->composerProject = $this->getComposerProjectFromQuestion();

        $this->folder = $this->getFolderFromQuestion();

        if($this->composerProject != 'laravel/laravel')
        {
            $this->folderSuffix = $this->getFolderSuffixFromQuestion();
        }

        $this->database = $this->getDatabaseFromQuestion();

        $this->domain = $this->getDomainFromQuestion();

        $output->writeln('<info>Creating project..</info>');
        $this->createProject();

        $output->writeln('<info>Create host ('.$this->domain.')...</info>');
        $this->updateHostsFile();

        $output->writeln('<info>Update Vagrant site mapper (/'.$this->folder.')</info>');
        $this->updateHomesteadSites();

        $output->writeln('<info>Update Vagrant database ('.$this->database.')</info>');
        $this->updateHomesteadDatabases();

        $output->writeln('<info>Provision Vagrant</info>');
        $this->provisionHomestead();

        $output->writeln('<success>Complete!</success>');

        $output->writeln('Visit: http://' . $this->domain);

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
        $question = new Question("What is your project's folder? ($this->folder): ", $this->folder);
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
        $hostAppendLine = $this->hostIP.' '.$this->domain;
        file_put_contents($this->hostPath, PHP_EOL.$hostAppendLine, FILE_APPEND | LOCK_EX);
    }

    private function updateHomesteadSites(){
        $homesteadContents = file_get_contents($this->homesteadPath);
        $tabSpacing = "    ";
        $mapLine = $tabSpacing."- map: ".$this->domain;
        $toLine = $tabSpacing."  to: ".$this->homesteadSitesPath.$this->name.$this->folderSuffix;
        $newLines = $mapLine.PHP_EOL.$toLine;
        $search = "sites:";
        $homesteadContents = str_replace($search,$search.PHP_EOL.$newLines,$homesteadContents);
        file_put_contents($this->homesteadPath, $homesteadContents);
    }

    private function updateHomesteadDatabases(){
        $homesteadContents = file_get_contents($this->homesteadPath);
        $tabSpacing = "    ";
        $line = $tabSpacing."- ".$this->database;
        $search = "databases:";
        $homesteadContents = str_replace($search,$search.PHP_EOL.$line,$homesteadContents);
        file_put_contents($this->homesteadPath, $homesteadContents);
    }

    private function provisionHomestead(){
        if(!is_null($this->homesteadProvisionCommand)){
            $shellOutput = shell_exec($this->homesteadProvisionCommand);
        }else{
            var_dump('cd '.$this->homesteadBoxPath.' && vagrant provision'); die;

            $shellOutput = shell_exec('cd '.$this->homesteadBoxPath.' && vagrant provision');
        }
    }


}