<?php

namespace App\Configuration;

class Config{

    private $composerProject;
    private $useComposer;
    private $folder;
    private $folderSuffix;
    private $domainExtension;
    private $hostIP;
    private $hostsPath;
    private $homesteadPath;
    private $homesteadSitesPath;
    private $homesteadBoxPath;
    private $homesteadAccessDirectoryCommand;

    public function __construct()
    {
        $this->updateFromEnvironment();
    }

    private function updateFromEnvironment(){
        $this->folder = getenv('LOCAL_SITES_PATH');
        $this->folderSuffix = getenv('DEFAULT_FOLDER_SUFFIX');
        $this->useComposer = boolval(getenv('USE_COMPOSER'));
        $this->composerProject = getenv('DEFAULT_COMPOSER_PROJECT');
        $this->hostsPath = getenv('HOSTS_FILE_PATH');
        $this->hostIP = getenv('HOMESTEAD_HOST_IP');
        $this->homesteadPath = getenv('HOMESTEAD_FILE_PATH');
        $this->homesteadSitesPath = getenv('HOMESTEAD_SITES_PATH');
        $this->homesteadBoxPath = getenv('HOMESTEAD_BOX_PATH');
        $this->homesteadAccessDirectoryCommand = getenv('HOMESTEAD_ACCESS_DIRECTORY_COMMAND');
        $this->domainExtension = getenv('DEFAULT_DOMAIN_EXTENSION');
    }

    public static function hasEnvFile(){
        return file_exists(basePath('.env'));
    }

    public function getFolder(){
        return $this->folder;
    }

    public function getFolderSuffix(){
        return $this->folderSuffix;
    }

    public function getUseComposer(){
        return $this->useComposer;
    }

    public function getComposerProject(){
        return $this->composerProject;
    }

    public function getHostsPath(){
        return $this->hostsPath;
    }

    public function getHostIP(){
        return $this->hostIP;
    }

    public function getHomesteadPath(){
        return $this->homesteadPath;
    }

    public function getHomesteadSitesPath(){
        return $this->homesteadSitesPath;
    }

    public function getHomesteadBoxPath(){
        return $this->homesteadBoxPath;
    }

    public function getHomesteadAccessDirectoryCommand(){
        return $this->homesteadAccessDirectoryCommand;
    }

    public function getDomainExtension(){
        return $this->domainExtension;
    }

}