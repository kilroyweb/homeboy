<?php

class HomesteadFileManagerTest extends \Tests\AppTestCase\AppTestCase
{

    private function mockFileContents(){
        return '---
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
    }

    public function test_add_map_line_to_sites()
    {
        $homesteadFileContents = $this->mockFileContents();
        $homesteadFile = $this->createTemporaryFile($homesteadFileContents);
        $homesteadFilePath = $this->getTemporaryFilePath($homesteadFile);

        $projectName = 'test-project';
        $fileManager = new \App\FileManagers\HomesteadFileManager($homesteadFilePath);
        $fileManager->addMapLineToSites($projectName.'.app', '/home/vagrant/Code/'.$projectName.'/public');

        $newFileContents = file_get_contents($homesteadFilePath);
        $expectedString = 'sites:
    - map: '.$projectName.'.app
      to: /home/vagrant/Code/'.$projectName.'/public';

        $this->assertContains($expectedString, $newFileContents);
    }

    public function test_add_database_to_sites()
    {

        $homesteadFileContents = $this->mockFileContents();
        $homesteadFile = $this->createTemporaryFile($homesteadFileContents);
        $homesteadFilePath = $this->getTemporaryFilePath($homesteadFile);

        $projectName = 'test-project';
        $fileManager = new \App\FileManagers\HomesteadFileManager($homesteadFilePath);
        $fileManager->addDatabase($projectName);

        $expectedString = 'databases:
    - '.$projectName;
        $newFileContents = file_get_contents($homesteadFilePath);

        $this->assertContains($expectedString, $newFileContents);
    }

}