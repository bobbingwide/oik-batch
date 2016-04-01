=== oik-batch ===
Contributors: bobbingwide,vsgloik
Donate link: http://www.oik-plugins.com/oik/oik-donate/
Tags: batch, WordPress, CLI
Requires at least: 4.4
Tested up to: 4.5-RC1
Stable tag: 0.8.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Text Domain: oik-batch
Domain Path: /languages/

== Description ==
Batch interface to WordPress

* oik-wp v0.0.1 - Batch WordPress 
* oik-batch v0.8.3 - Batch interface to WordPress servers
* oik-git v0.8.4 - Simple Git status checker


* Allows development and testing of plugin functions in a native environment, such as Windows
* Provides some basic APIs to assist in CLI processing

oik-wp

* Allows you to run WordPress from the command line
* Connects to your chosen local WordPress environment


oik-batch

* Allows you to run parts of WordPress in batch mode
* No connection to the database

* Run under oik-batch


== Installation ==
1. Upload the contents of the oik-batch plugin to the `/wp-content/plugins/oik-batch' directory
1. DO NOT activate the oik-batch plugin through the 'Plugins' menu in WordPress
1. Invoke from the command line using a batch file

== Frequently Asked Questions ==
= What are the components? =

Currently:

* createapi2.php - used to document oik APIs
* list_oik_plugins.php - returns a list of known oik-plugins to be processed
* listapis2.php - test routine to list all the APIs in a plugin
* oik-admin-ajax.inc - batch interface to oiksc create API
* oik-batch.php - Batch processing main routine
* oik-ignore-list.php - Ignore dir and file logic
* oik-list-previous-files.php - List previous versions files
* oik-list-wordpress-files.php - List WordPress core files
* oik-login.inc - common functions for command line
* oik-wp.php - Standalone WordPress main routine

Under development:

* oik-load.php - Load a single plugin
* oik-site.php - PROTOTYPE web site checker
* oik-git.php - Check the status of a whole host of Git repos

* readme.txt - this file ( also README.md ) 

= What are the dependencies? =

oik-batch is still dependent upon the oik base plugin's files if oik, oik-lib nor oik-bwtrace is not loaded.

For listing and defining the APIs implemented by a WordPress plugin, or WordPress itself you will need the following:

* oik-shortcodes plugin
* oik-plugins plugin
* oik-fields plugin
* oik base plugin
* the WordPress plugin 
* a local WordPress installation


= How do I invoke the routines? =

Create a batch file for each of the main routine

batch.bat for invoking oik-batch.php

` 
php c:\apache\htdocs\wordpress\wp-content\plugins\oik-batch\oik-batch.php %*
`
pass the name of the PHP file to invoke as the first parameter

e.g. To invoke listapis2 use:
`
batch listapis2 <i>plugin</i>  
`

oikwp.bat for invoking oik-wp.php

`
rem Run oik-wp in batch mode so that you can test some code outwith the browser
php c:\apache\htdocs\wordpress\wp-content\plugins\oik-batch\oik-wp.php %*
`

Run the routine from the required installation's folder where the php file exists.
This allows oik-wp to determine the correct wp-config file to use.



Alternatively oik-batch.php can be included in the main routine. 
`
php c:\apache\htdocs\wordpress\wp-content\plugins\oik-batch\createapi2.php --plugin=%1 --site=http://oik-plugins.co.uk --apikey=apikey
`

= What files are deprecated? =

The following files are deprecated and will no longer be released

* wp-batch._php - deprecated code. use oik-batch.php
* createapi._php - use createapi2.php
* listapis._php - use listapis2.php



== Screenshots ==
1. oik-batch in action performing createapi2.php

== Upgrade Notice ==
= 0.8.4 =
Co-requisite changes for oik-shortcode running locally against WordPress MultiSite sites

= 0.8.3 =
Now supports local running of createapi2.php from oik-shortcodes.php and WordPress MultiSite ( sub-domain installs ).

= 0.8.2 = 
oik-batch now only supports WordPress 4.4 or higher

= 0.8.1 =
Added oik-wp.php - Standalone WordPress main routine

= 0.8 = 
Supports previous: parameter

= 0.7 = 
Now supports parsing of themes. Using the oik-themes plugin on the server.

= 0.6 = 
Added oik-load.php to find out the changes when plugins are loaded

= 0.5 =
Reduced the amount of info echoed from the result from the server

= 0.4 = 
Changes to logic to determine which files to exclude

= 0.3 =
Required for oik-shortcodes v1.14

= 0.2 =
You will need to upgrade oik-shortcodes to v1.11 or higher

= 0.1 =
Required for defining oik APIs for oik plugins. Only supports non-OO functions.

== Changelog ==
= 0.8.4 = 
* Added: Various class Git improvements [github bobbingwide oik-batch issue 7]
* Added: oik-git.php to invoke Git::check_status() [github bobbingwide oik-batch issue 7]
* Added: Support WordPress MultiSite installation with url=site parameter [github bobbingwide oik-batch issue 6]
* Added: Co-req changes for oik-shortcodes to store parse status in post meta [github bobbingwide oik-shortcodes issue 21]
* Added: Changes for remote git repositories when running locally [github bobbingwide oik-shortcodes issue 22]

= 0.8.3 =
* Fixed: Add oikb_check_time_limit() [github bobbingwide oik-batch issues 4]
* Fixed: Don't list plugin or theme files for 'wordpress' component [github bobbingwide oik-batch issues 5]
* Added: Support listing ALL files in a Git repository
* Added: Support running against WordPress MultiSite [github bobbingwide oik-batch issues 6]

= 0.8.2 = 
* Fixed: Improve performance parsing git based repositories [github bobbingwide oik-batch issue 1]
* Fixed: Support direct invocation of batch logic in a plugin [github bobbingwide oik-batch issue 2]
* Fixed: createapi2 does not work on WordPress 4.4.x [github bobbingwide oik-batch issue 3]

= 0.8.1 =
* Added: oik-wp.php - Standalone WordPress, not WP-cli
* Added: libs/oik-cli.php - Library file for 'batch' APIs
* Changed: Improve oikb_get_response() to return the complete response, if needed
* Changed: Added start: parameter, primarily for createapi2 processing
* Changed: Updated ignore file logic 
* Changed: oik-batch doesn't set deprecated constants for oik-bwtrace
* Changed: oik_batch_load_oik_boot() loads oik-boot from oik's libs folder
* Changed: listapis2.php and createapi2.php now use shared library file: bobbfunc
* Changed: createapi2.php supports start parameter
* Changed: createapi2.php uses _la_checkignorelist()
* Fixed: createapi2.php requires more include files

= 0.8 = 
* Added: createapi2 now uses the previous= parameter instead of the name= parameter when applying upgrades from a previous version
* Changed: uses _la_checkignorelist() to determine which files to ignore
* Changed: ignores wp-config.php - security implications
* Not fixed: Does not yet support running oik-batch from a symbolicly linked folder
* Changed: Supports invocation from WP-CLI ( boot-fs.php )
* Changed: timeout on createapi2 increased to 30 seconds
* Changed: listapis2 supports comparing with a previous version of the plugin/theme
* Added: oik-list-previous-files.php 
* Added: oik-wp-api.php and oik-wp-api-tests.php - prototype files working with the WordPress REST API 

= 0.7 = 
* Added: Support for parsing theme files
* Changed: Created common functions for listapis2.php and creatapi2.php

= 0.6 = 
* Added: oik-load.php is a draft routine for producing a rough measurement of the effort required to load a plugin
* Added: oik-load.php can also be invoked from the web.
* Changed: oik-batch can now perform database stuff. To use it you need to create a db.php file in wp-content.
* Changed: Make it easier to load listapis2.php output in CSV form. Commented out some echo's 
* Changed: Added function size to CSV output   

= 0.5 =
* Changed: Added logic to reduce the amount of data echoed from the server
* Added: Started developing oik-sites.php

= 0.4 = 
* Changed: createapi2 now only processes the exclusion list if the plugin is not "wordpress"
* Added: Updated oik-ignore-list.php - used by listapis2.php only 

= 0.3 = 
* Added: createapi2 now calls _lf_dofile_ajax() to define each parsable PHP file
* Changed: Ignore lists extracted to oik-ignore-list.php ... but not yet tidied 
* Added: test routine listfile.php
* Added: oikb_admin_ajax_post_create_file() to invoke "oiksc_create_file" 
* Deleted: unnecessary file: fergle

= 0.2 = 
* Changed: listapis2.php replaces listapis.php - now supports OO code
* Changed: createapis2.php replaces creatapis.php - now supports OO code 
* Changed: Tested with WordPress 3.9 and WordPress 4.0-alpha
* Changed: Improved initialisation logic to be less dependent on the local setup
 
= 0.1 =
* Added: New plugin


