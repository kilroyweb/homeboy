<?php

class ComposerCreateProjectTest extends \Tests\AppTestCase\AppTestCase
{

    public function test_create_project()
    {

        $projectDirectory = '/Code';
        $composerProject = 'laravel/laravel';
        $projectName = 'new-project';

        $config = new \App\Configuration\Config();

        $commandExecutor = new \App\Support\Shell\CommandExecutor();
        $commandExecutor->setExecute(false);

        $composerCreateProjectCommand = new \App\Commands\ComposerCreateProject(null, $config);
        $composerCreateProjectCommand->setCommandExecutor($commandExecutor);

        $application = new \Symfony\Component\Console\Application();

        $application->add($composerCreateProjectCommand);

        $command = $application->find($composerCreateProjectCommand->getName());
        $commandTester = new \Symfony\Component\Console\Tester\CommandTester($command);
        $commandTester->setInputs([
            $projectDirectory,
            $composerProject,
            $projectName,
            'y',
        ]);
        $commandTester->execute(array(
            'command'  => $command->getName(),
        ));

        $expectedCommand = 'cd '.$projectDirectory.' && composer create-project '.$composerProject.' '.$projectName;

        $this->assertEquals($expectedCommand,$commandExecutor->getCommand());
    }

}