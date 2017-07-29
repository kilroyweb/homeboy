# Homeboy

Tool for automating sites using Laravel Homestead. With one command, Homeboy will:

- Install Laravel into a new directory via composer
- Update your host file to point your dev domain to your homestead IP
- Update your Homestead.yaml file mapping to include your new project
- Update your Homestead.yaml file to create a database for your project
- Re-provision Vagrant

![](https://j.gifs.com/y8KL0n.gif)

## Table of Contents
  - [Requirements](#requirements)
  - [Installation](#installation)
    - [Install as a global composer package (recommended)](#install-as-a-global-composer-package)
    - [Install using git clone](#install-using-git-clone)
  - [Setting Configuration](#setting-configuration)
  - [Homeboy Commands](#homeboy-commands)
    - [Host](#host)
    - [File](#file)
    - [Vagrant](#vagrant)
    - [Domain](#domain)
  - [Troubleshooting](#troubleshooting)

## Requirements

On your local (non homestead) machine, Homeboy requires:
 - Git
 - PHP
 - Composer
 
## Installation

### Install as a global composer package
 
```
composer global require "kilroyweb/homeboy" dev-master
```

Make sure to place the $HOME/.composer/vendor/bin directory (or the equivalent directory for your OS) 
in your $PATH so the laravel executable can be located by your system.

Once installed, you can use ``homeboy`` command from anywhere on your system.

Run the setup command to generate a .env file

```
php homeboy setup
```

### Install using git clone

- On your local machine, clone Homeboy

```
cd ~
git clone https://github.com/kilroyweb/homeboy.git homeboy
```
- Within the homeboy directoy, run composer

```
cd homeboy
composer install
```

Run the setup command to generate a .env file

```
php homeboy setup
```

**By this point, you should be able to run homeboy within the directory using the command:**

```
php homeboy
```

- Create an alias to run homeboy from any directory:

On mac (in ~/.bash_profile) add:

```
alias homeboy='php ~/homeboy/homeboy'
```

On windows, an alias can be added using something like if using a tool like [Cmder](http://cmder.net)

```
homeboy=php C:\Users\[USER]\homeboy\homeboy $*
```

*be sure to edit the path based on where homeboy is installed*

## Setting Configuration

After running ```homeboy setup```, you should have a .env file in your homeboy directory with a few defaults that have been generated.

These options will likely need to be updated based on your particular setup, details about each available setting are listed below:

#### USE_COMPOSER 
Default option to determine if homeboy should create a new composer project

```
USE_COMPOSER=true
```

#### DEFAULT_COMPOSER_PROJECT 
Default package used when creating a composer project

```
DEFAULT_COMPOSER_PROJECT=laravel/laravel
```

#### DEFAULT_FOLDER_SUFFIX
When hosting a new project, the default directory that nginx would define as the document root. For Laravel projects, by default this is "/public"

```
DEFAULT_FOLDER_SUFFIX=/public
```

#### DEFAULT_DOMAIN_EXTENSION
When creating development domains, the default domain suffix given when a domain is generated

```
DEFAULT_DOMAIN_EXTENSION=.app
```

#### HOSTS_FILE_PATH
The location to your local hosts file that homeboy will add to when hosting new projects

This file will need to have permissions adjusted to be editable by homeboy

```
HOSTS_FILE_PATH=/etc/hosts
```

#### HOMESTEAD_HOST_IP

The ip address given to your homestead virtual machine. This is likely found in your Homestead.yaml file

```
HOMESTEAD_HOST_IP=192.168.10.10
```

#### HOMESTEAD_FILE_PATH

The location to your Homestead.yaml file. Homeboy will update this file's sites list and database list when creating new projects

```
HOMESTEAD_FILE_PATH=/Users/Username/Homestead/Homestead.yaml
```

#### HOMESTEAD_SITES_PATH

The root path to your websites in your homestead virtual machine 

```
HOMESTEAD_SITES_PATH=/home/vagrant/Code/
```

#### HOMESTEAD_BOX_PATH

The path to your Homestead directory

```
HOMESTEAD_BOX_PATH=/Users/Username/Homestead
```

#### LOCAL_SITES_PATH

The path on your local machine to where you store your projects. Homeboy will cd into this directory when running "composer create-project ..."

```
LOCAL_SITES_PATH=/Users/Username/Code
```

#### HOMESTEAD_ACCESS_DIRECTORY_COMMAND

In Windows if you are running Homestead on a different drive, an extra command may be required to switch drives before cd'ing into your directory.

This option will cause Homeboy to ignore the option: HOMESTEAD_BOX_PATH

```
HOMESTEAD_ACCESS_DIRECTORY_COMMAND="cd /d D: && cd /Homestead"
```

#### ACCESS_LOCAL_SITES_DIRECTORY_COMMAND

Similar to HOMESTEAD_ACCESS_DIRECTORY_COMMAND, In Windows if your root project directory is on a different drive, an extra command may be required to switch drives before cd'ing into your directory.

This option will cause Homeboy to ignore the option: LOCAL_SITES_PATH

```
ACCESS_LOCAL_SITES_DIRECTORY_COMMAND="cd /d D: && cd /Code"
```

## Homeboy Commands

### Host

```
homeboy host
```

Because this is the default command, you can run host by simply running:

```
homeboy
```

Running this command automates the task of creating a new composer project, updating your hosts file and vagrant files when provisioning a new website

When the command runs, it prompts for the sites directory name, database name, and dev url to update the needed files and then provisions vagrant

A few options have been added to speed up the command. However because the command "homeboy" is simply a shortcut for "homeboy host" (allowing us to add additional commands in the future), options only work when calling "homeboy host" rather than just "homeboy".

Some options available:

- ```--use-defaults``` : Automatically accept defaults before provisioning
- ```--skip-confirmation``` : Automatically confirm before running task
- ```--name``` : Directory name to create project in
- ```--database``` : Database to add to homestead
- ```--domain``` : Development domain

The following example will run all tasks automatically without any question prompts:

```
homeboy host --name=my-project --use-defaults --skip-confirmation
```

### Vagrant

Homeboy also contains the "vagrant" command, allowing you to quickly run any vagrant command without having to cd into that directory, ie:

```
homeboy vagrant status
```

### File

Quickly view a file contents by running:

```
homeboy file hosts
```
or

```
homeboy file homestead
```

### Domain

The domain command lets you quickly add a new domain record to your hosts file.

```
homeboy domain
```

Homeboy will prompt the domain as well as the ip address

## Troubleshooting

### Homeboy is running composer install within my current directory, rather than my defined LOCAL_SITES_PATH

A common cause of this on Windows is when LOCAL_SITES_PATH is in a different drive than where the homeboy command is being run. Since Windows must first change drives before cd'ing into the directory, you can use the "ACCESS_LOCAL_SITES_DIRECTORY_COMMAND" to overwrite how homeboy accesses this directory. For example:

```
ACCESS_LOCAL_SITES_DIRECTORY_COMMAND="cd /d D: && cd /Code"
```

### Vagrant returns "A Vagrant environment or target machine is required to run this command"

This message will be displayed when Homeboy is unable to cd into the proper vagrant directory when running vagrant commands. Please verify your "HOMESTEAD_BOX_PATH" value to ensure it is correct.

On Windows, if your HOMESTEAD_BOX_PATH is in a different drive than the drive you are running your homeboy command, you may need to add the "HOMESTEAD_ACCESS_DIRECTORY_COMMAND" to add any additional commands (such as switching drives) when accessing the directory. For example:

```
HOMESTEAD_ACCESS_DIRECTORY_COMMAND="cd /d D: && cd /Homestead"
```
