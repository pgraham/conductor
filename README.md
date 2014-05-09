# Conductor
> Drivin that train...

Conductor is a webapp framework for PHP+Javascript providing specific support
for single page Javascript heavy applications.

## Prerequisites:

 -  composer
 -  node,npm
 -  bower
 -  grunt

## Install

 1. Add as a [Composer](http://getcomposer.org) dependency. (in `composer.json`)

        {
            "require": {
                "zeptech/conductor": "dev-master"
            }
        }

 2. Run Composer install: `composer install`
 3. Run Conductor initialization script: `./vendor/bin/cdt-init`

    If you get errors about not being able to change groups run the following
    command: `sudo chgrp -R www-data target`

 4. \*Install bower dependencies:

        $ cd vendor/zeptech/conductor
        $ bower install

 5. \*Run Grunt build:

        $ cd vendor/zeptech/conductor
        $ npm install
        $ grunt

\* These steps will be eliminated either through a composer install/update hook
or in the cdt-init script.

## Configuration

Wherever possible, Conductor will provide/adopt a convention in order to keep configuration to a minimum. However there is still a small amount of configuration necessary to use Conductor to power your site.

Conductor configuration is defined in a YAML file `conductor.yml` in the root directory of your site.

Here is a sample configuration file with comments explaining each configuration value:

```YAML
# You can override the values of any block for different environments using
# an env block.

# This is display name for you site.
# It will appear as a prefix to each HTML page's <title> tag
title: My Awesome Site

# This is the root namespace where all of your sites PHP files are found.
namespace: mysite
 
# Database configuration.  All settings are required.
db:
  # Currently only MySQL is supported.  There are plans to also
  # support PostgreSQL and SQLite
  driver: mysql
  host: localhost
  username: mysite_d
  password: 123abc
  schema: mysite_d

  # When deploying your site to either staging, or production, the
  # values in the following blocks will be used.
  env:
    stage:
      schema: mysite_s
      username: mysite_s
      password: abc123
    prod:
      schema: mysite
      username: mysite
      password: !123@abc#

# This is web path from your server's domain to the root of your site.
# This value is optional and defaults to /
webRoot: /

# You can put an env block at any level
env:
  dev:
    webRoot: /mysite
```
