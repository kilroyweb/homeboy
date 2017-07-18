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

After running ```homeboy setup```, you should have a .env file in your homeboy directory with a few options that can be managed:

TODO

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
