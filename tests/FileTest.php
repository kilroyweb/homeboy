<?php

class FileTest extends \Tests\AppTestCase\AppTestCase
{

    public function test_display_hosts_file_contents()
    {

        $hostsFileContents = "192.168.10.10 example1.app";

        $hostsFile = $this->createTemporaryFile($hostsFileContents);
        $hostsFilePath = $this->getTemporaryFilePath($hostsFile);

        $config = new \App\Configuration\Config();
        $config->setHostsPath($hostsFilePath);

        $application = new \Symfony\Component\Console\Application();
        $application->add(new \App\Commands\File(null, $config));

        $command = $application->find('file');
        $commandTester = new \Symfony\Component\Console\Tester\CommandTester($command);
        $commandTester->execute(array(
            'command'  => $command->getName(),
            'file' => 'hosts',
        ));
        $output = $commandTester->getDisplay();
        $this->assertContains($hostsFileContents, $output);
    }

    public function test_display_homestead_file_contents()
    {

        $homesteadFileContents = '---
ip: "192.168.10.10"
memory: 2048
cpus: 1
provider: virtualbox

authorize: ~/.ssh/id_rsa.pub

keys:
    - ~/.ssh/id_rsa

folders:
    - map: ~/Code
      to: /home/vagrant/Code

sites:
    - map: example-1.app
      to: /home/vagrant/Code/example-1/public
    - map: example-2.app
      to: /home/vagrant/Code/example-2/public

databases:
    - example-1
    - example-2


# blackfire:
#     - id: foo
#       token: bar
#       client-id: foo
#       client-token: bar

# ports:
#     - send: 50000
#       to: 5000
#     - send: 7777
#       to: 777
#       protocol: udp
';

        $homesteadFile = $this->createTemporaryFile($homesteadFileContents);
        $homesteadFilePath = $this->getTemporaryFilePath($homesteadFile);

        $config = new \App\Configuration\Config();
        $config->setHomesteadPath($homesteadFilePath);

        $application = new \Symfony\Component\Console\Application();
        $application->add(new \App\Commands\File(null, $config));

        $command = $application->find('file');
        $commandTester = new \Symfony\Component\Console\Tester\CommandTester($command);
        $commandTester->execute(array(
            'command'  => $command->getName(),
            'file' => 'homestead',
        ));
        $output = $commandTester->getDisplay();
        $this->assertContains($homesteadFileContents, $output);
    }

}