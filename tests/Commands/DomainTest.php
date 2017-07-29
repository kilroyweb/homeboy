<?php

class DomainTest extends \Tests\AppTestCase\AppTestCase
{

    public function test_add()
    {

        $hostsFileContents = "192.168.10.10 example1.app";

        $hostsFile = $this->createTemporaryFile($hostsFileContents);
        $hostsFilePath = $this->getTemporaryFilePath($hostsFile);

        $config = new \App\Configuration\Config();
        $config->setHostsPath($hostsFilePath);

        $application = new \Symfony\Component\Console\Application();
        $application->add(new \App\Commands\Domain(null, $config));

        $command = $application->find('domain');
        $commandTester = new \Symfony\Component\Console\Tester\CommandTester($command);
        $commandTester->setInputs([
            'demo.dev',
            '10.10.10.10',
            'y'
        ]);
        $commandTester->execute(array(
            'command'  => $command->getName(),
        ));
        $output = $commandTester->getDisplay();
        $expectedHostFileContents = '10.10.10.10 demo.dev';
        $this->assertContains($expectedHostFileContents, file_get_contents($hostsFilePath));
    }

}