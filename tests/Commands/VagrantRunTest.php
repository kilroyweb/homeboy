<?php

class VagrantRunTest extends \Tests\AppTestCase\AppTestCase
{

    public function test_run()
    {

        $vagrantDirectory = '/Vagrant';
        $action = 'up';

        $config = new \App\Configuration\Config();
        $config->setHomesteadBoxPath($vagrantDirectory);

        $commandExecutor = new \App\Support\Shell\CommandExecutor();
        $commandExecutor->setExecute(false);

        $vagrantRunCommand = new \App\Commands\Vagrant\VagrantRun(null, $config);
        $vagrantRunCommand->setCommandExecutor($commandExecutor);

        $application = new \Symfony\Component\Console\Application();
        $application->add($vagrantRunCommand);

        $command = $application->find($vagrantRunCommand->getName());
        $commandTester = new \Symfony\Component\Console\Tester\CommandTester($command);
        $commandTester->execute(array(
            'command'  => $command->getName(),
            'action' => $action,
        ));

        $expectedCommand = 'cd '.$vagrantDirectory.' && vagrant '.$action;

        $this->assertEquals($expectedCommand,$commandExecutor->getCommand());
    }

}