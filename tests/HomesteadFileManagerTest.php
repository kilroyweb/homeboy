<?php

class HomesteadFileManagerTest extends \Tests\AppTestCase\AppTestCase
{
    public function test_add_map_line_to_sites()
    {
        $projectName = 'test-project';
        $fileManager = new \App\FileManagers\HomesteadFileManager($this->basePath('stubs/Homestead.yaml'),[
            'allowWriteFile' => false,
        ]);
        $response = $fileManager->addMapLineToSites($projectName.'.app', '/home/vagrant/Code/'.$projectName.'/public');
        $expectedString = 'sites:
    - map: '.$projectName.'.app
      to: /home/vagrant/Code/'.$projectName.'/public';
        $this->assertContains($expectedString, $response);
    }

    public function test_add_database_to_sites()
    {
        $projectName = 'test-project';
        $fileManager = new \App\FileManagers\HomesteadFileManager($this->basePath('stubs/Homestead.yaml'),[
            'allowWriteFile' => false,
        ]);
        $response = $fileManager->addDatabase($projectName);
        $expectedString = 'databases:
    - '.$projectName;
        $this->assertContains($expectedString, $response);
    }

}