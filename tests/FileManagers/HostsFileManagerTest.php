<?php

class HostsFileManagerTest extends \Tests\AppTestCase\AppTestCase
{
    public function test_add_host_line()
    {
        $hostsFileContents = "192.168.10.10 example1.app";
        $hostsFile = $this->createTemporaryFile($hostsFileContents);
        $hostsFilePath = $this->getTemporaryFilePath($hostsFile);

        $hostIP = '192.168.10.10';
        $domain = 'testproject.app';

        $fileManager = new \App\FileManagers\HostsFileManager($hostsFilePath);
        $fileManager->appendLine($hostIP.' '.$domain);

        $expectedString = $hostsFileContents.PHP_EOL.$hostIP.' '.$domain;
        $newFileContents = file_get_contents($hostsFilePath);

        $this->assertEquals($expectedString, $newFileContents);
    }

}