<?php

class HostsFileManagerTest extends \Tests\AppTestCase\AppTestCase
{
    public function test_add_map_line_to_sites()
    {
        $hostIP = '192.168.10.10';
        $domain = 'testproject.app';
        $fileManager = new \App\FileManagers\HomesteadFileManager($this->basePath('stubs/hosts'),[
            'allowWriteFile' => false,
        ]);
        $response = $fileManager->appendLine($hostIP.' '.$domain);
        $expectedString = $hostIP.' '.$domain;
        $this->assertContains($expectedString, $response);
    }

}