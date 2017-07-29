<?php

class DomainTest extends \Tests\AppTestCase\AppTestCase
{

    public function test_add()
    {

        /*$hostsFileContents = "192.168.10.10 example1.app";

        $hostsFile = $this->createTemporaryFile($hostsFileContents);
        $hostsFilePath = $this->getTemporaryFilePath($hostsFile);

        $config = new \App\Configuration\Config();
        $config->setHostsPath($hostsFilePath);

        $application = new \Symfony\Component\Console\Application();
        $application->add(new \App\Commands\Domain(null, $config));

        $command = $application->find('domain');
        $commandTester = new \Symfony\Component\Console\Tester\CommandTester($command);
        $commandTester->execute(array(
            'command'  => $command->getName(),
        ));
        $output = $commandTester->getDisplay();
        print_r($output);
        //$this->assertContains($hostsFileContents, $output);*/
    }

}