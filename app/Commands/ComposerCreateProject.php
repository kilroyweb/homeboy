<?php

namespace App\Commands;

use App\Actions\ComposerCreateProjectAction;
use App\Configuration\Config;
use App\Input\Interrogator;
use App\Support\Traits\HasCommandExecutor;
use App\Support\Traits\HasCommandOptions;
use App\Support\Traits\RequireEnvFile;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ComposerCreateProject extends Command
{

    use RequireEnvFile;
    use HasCommandOptions;
    use HasCommandExecutor;

    private $questionHelper;
    private $inputInterface;
    private $outputInterface;

    private $config;

    private $projectDirectory;
    private $composerProject;
    private $projectName;

    private $interrogator;

    private $commandExecutor;

    public function __construct($name = null, Config $config)
    {
        $this->config = $config;
        parent::__construct($name);
    }

    protected function configure()
    {
        $this
            ->setName('composer:create-project')
            ->setDescription('Create a new composer project')
            ->setHelp("");
    }

    private function init(InputInterface $input, OutputInterface $output){
        $this->inputInterface = $input;
        $this->outputInterface = $output;
        $this->hasDotEnvFile();
        $this->questionHelper = $this->getHelper('question');
        $this->interrogator = new Interrogator($input, $output, $this->getHelper('question'));
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

        $this->projectDirectory = $this->interrogator->ask(
            'Directory to create project?',
            $this->config->getFolder()
        );

        $this->composerProject = $this->interrogator->ask(
            'Composer project',
            $this->config->getComposerProject()
        );

        $this->projectName = $this->interrogator->ask(
            'Project directory name',
            'project-'.time()
        );

    }

    private function composerCreateProjectAction(){
        if(!empty($this->config->getAccessLocalSitesDirectoryCommand())){
            $accessCommand = $this->config->getAccessLocalSitesDirectoryCommand();
        }else{
            $accessCommand = 'cd '.$this->projectDirectory;
        }
        return new ComposerCreateProjectAction($this->commandExecutor,$accessCommand, $this->composerProject, $this->projectName);
    }

    private function getTaskConfirmationFromQuestion(){
        $this->outputInterface->writeln('<info>The following tasks will be executed:</info>');

        $this->outputInterface->writeln('- '.$this->composerCreateProjectAction()->confirmationMessage());

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

        $this->outputInterface->writeln('<info>'.$this->composerCreateProjectAction()->actionMessage().'...</info>');
        $this->composerCreateProjectAction()->run();
        $this->outputInterface->writeln('');
        $this->outputInterface->writeln('<info>Complete!</info>');
    }

}