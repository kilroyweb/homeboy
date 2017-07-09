# Homeboy

Tool for automating sites using Laravel Homestead. With one command, Homeboy will:

- Install Laravel into a new directory via composer
- Update your host file to point your dev domain to your homestead IP
- Update your Homestead.yaml file mapping to include your new project
- Update your Homestead.yaml file to create a database for your project
- Re-provision Vagrant

![](https://j.gifs.com/y8KL0n.gif)

## Installation + First Time Setup

*Requires Git, PHP, and Composer installed on your local (non-homestead machine)*

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

- Copy one of the .env.example files to a new file called `.env` and update it with your information

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

### Use Homeboy to host a new homestead site

```
homeboy
```

Running this command automates the task of creating a new composer project, updating your hosts file and vagrant files when provisioning a new website

When the command runs, it prompts for the sites directory name, database name, and dev url to update the needed files and then provisions vagrant

### Options

A few options have been added to speed up the command. However because the command "homeboy" is simply a shortcut for "homeboy host" (allowing us to add additional commands in the future), options only work when calling "homeboy host" rather than just "homeboy".

Some options available:

- ```--use-defaults``` : Automatically accept defaults before provisioning
- ```--skip-confirmation``` : Automatically confirm before running task
- ```--name``` : Directory name to create project in
- ```--database``` : Database to add to homestead
- ```--domain``` : Development domain

The following example will run all tasks automatically without any question prompts:

```$xslt
homeboy host --name=my-project --use-defaults --skip-confirmation
```