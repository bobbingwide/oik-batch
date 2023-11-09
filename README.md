# oik-batch 
![banner](assets/oik-batch-banner-772x250.jpg)
* Contributors: bobbingwide,vsgloik
* Donate link: https://www.oik-plugins.com/oik/oik-donate/
* Tags: batch, WordPress, CLI, PHPUnit
* Requires at least: 5.2
* Tested up to: 6.4.1
* Stable tag: 1.1.2
* License: GPLv2 or later
* License URI: http://www.gnu.org/licenses/gpl-2.0.html
* Text Domain: oik-batch
* Domain Path: /languages/

## Description 
Batch interface to WordPress

* Allows development and testing of plugin and theme functions in a native environment, such as Windows.
* Allows you to run WordPress in batch mode, from the command line.
* Provides some basic APIs to assist in Command Line Interface (CLI) processing.

Components

* oik-wp - Batch WordPress - standalone processing using a complete WordPress installation but not using WP-CLI
* oik-batch - Batch interface to WordPress servers
* Sub-component: oik-git - Checks the status of Git repositories
* Sub-component: oik-innodb - Detects MyISAM tables and converts them to InnoDB
* Sub-component: oik-sqldump - Runs mysqldump ( early version )
* Super-component: oik-phpunit - in situ PHPUnit test invocation for WordPress MultiSite

oik-wp

* Allows you to run WordPress and WordPress Multisite from the command line.
* Connects to your chosen local WordPress environment.
* Supports direct invocation of batch processes.
* Supports running PHPUnit tests for WordPress plugins or themes in situ.
* Supports installations with Git based plugins and themes


oik-batch

* Allows you to run parts of WordPress in batch mode
* No connection to the database
* Performs bootstrap logic preparing a [wp] client environment.
* Supports direct invocation of batch processes.

* Sub component: oik-git

* Run under oik-batch, checks the status of Git repositories for changes that need to be committed
* Syntax: batch oik-git.php from the plugin or theme directory

* Sub component: oik-innodb

* Converts MyISAM tables to InnoDB to enable in situ PHPUnit tests
* Syntax: oikwp oik-innodb.php

* Sub component: oik-sqldump

* Syntax: oikwp oik-sqldump.php
* Creates a backup of your MySQL database
* Target directory hardcoded as C:/backups-qw/qw/sqldumps

* Super component: oik-phpunit

* Syntax: php oik-phpunit.php PHPUnit parms url=domain path=site-path
* Supports invocation of in situ PHP testing for WordPress MultiSite
* Calls PHPUnit having previously saved the parameters required to start WordPress MultiSite in a batch environment


## Installation 
1. Upload the contents of the oik-batch plugin to the `/wp-content/plugins/oik-batch' directory
1. Do not activate the oik-batch plugin through the 'Plugins' menu in WordPress
1. Invoke from the command line using a batch file

## Frequently Asked Questions 
# What other components are in oik-batch? 

* createapi2.php -
* listapis2.php
* list_oik_plugins.php
* oik-load.php
* oik-locurl.php
* oik-site.php


# What are the dependencies? 

oik-batch.php and oik-wp.php

As of v0.8.7 oik-batch is no longer dependent upon the oik base plugin's files for batch processing.

If you activate either oik-batch or oik-wp then it will report a dependency on the oik base plugin.

For other routines, such as createapi2.php, which is used for listing and defining the APIs implemented by a WordPress plugin, theme or WordPress core itself,
you will need the following:

* oik-shortcodes plugin
* oik-plugins plugin
* oik-fields plugin
* oik base plugin
* the WordPress plugin
* a local WordPress installation


# How do I invoke the routines? 

Create a batch file for each of the main routines

oikwp.bat for invoking oik-wp.php

```
rem Run oik-wp in batch mode so that you can test some code outwith the browser
php c:\apache\htdocs\wordpress\wp-content\plugins\oik-batch\oik-wp.php %*
```

Run the routine from the required installation's folder where the php file exists.
This allows oik-wp to determine the correct wp-config file to use. eg

```
cd \apache\htdocs\oikcom\wp-content\plugins\oik-shortcodes
oikwp
```



batch.bat for invoking oik-batch.php

```
php c:\apache\htdocs\wordpress\wp-content\plugins\oik-batch\oik-batch.php %*
```
pass the name of the PHP file to invoke as the first parameter

e.g. To invoke listapis2 use:
```
batch listapis2 <i>plugin</i>
```


Alternatively oik-batch.php can be included in the main routine.
```
php c:\apache\htdocs\wordpress\wp-content\plugins\oik-batch\createapi2.php --plugin=%1 --site=https://oik-plugins.co.uk --apikey=apikey
```

wu.bat for invoking oik-phpunit.php

```
set PRE_PHPUNIT_CD=%CD%
set PHPUNIT=c:\apache\htdocs\phpLibraries\phpunit\phpunit-8.4.1.phar
php C:\apache\htdocs\wordpress\wp-content\plugins\oik-batch\oik-phpunit.php "--verbose" "--disallow-test-output" "--stop-on-error" "--stop-on-failure" "--log-junit=phpunit.json" %*

```


## Screenshots 
1. oik-batch in action performing createapi2.php

## Upgrade Notice 
# 1.1.2 
Update for support for PHP 8.1 and PHP 8.2.

## Changelog 
# 1.1.2 
* Changed: Don't str_replace( $string, null, $to ) #45
* Changed: PHP 8.2: Don't strlen( null ) #45
* Changed: PHP 8.2: Don't is_dir( null ) #45
* Tested: With PHPUnit 9.6
* Tested: With PHP 8.0, PHP 8.1 and PHP 8.2
* Tested: With WordPress 6.4.1 and WordPress Multisite
