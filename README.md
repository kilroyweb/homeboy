# Homeboy

Tool for automating sites using Laravel Homestead. With one command, Homeboy will:

- Update your host file to point your dev domain to your homestead IP
- Update your Homestead.yaml file mapping to include your new project
- Update your Homestead.yaml file to create a database for your project
- Re-provision Vagrant

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

- Copy one of the .env.example files to .env, and update it with your information

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

Running this command automates the task of updating your hosts file and vagrant files when provisioning a new website

When the command runs, it prompts for the sites directory name, database name, and dev url to update the needed files and then provisions vagrant
