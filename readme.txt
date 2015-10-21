=== oik-batch ===
Contributors: bobbingwide,vsgloik
Donate link: http://www.oik-plugins.com/oik/oik-donate/
Tags: batch, WordPress, subset
Requires at least: 3.9
Tested up to: 4.1
Stable tag: 0.8
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Text Domain: oik-batch
Domain Path: /languages/

== Description ==
Batch interface to remote WordPress servers

* Allows you to run parts of WordPress in batch mode.
* No connection to the database
* Allows development and testing of plugin functions in a native environment, such as Windows


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
* oik-login.inc - common functions for command line
* oik-batch.php - main routine
* oik-site.php - UNDER ACTIVE DEVELOPMENT
* oik-load.php - UNDER ACTIVE DEVELOPMENT


* readme.txt - this file ( also README.md ) 

 
Deprecated code:
* wp-batch._php - deprecated code. use oik-batch.php
* createapi._php - use createapi2.php
* listapis._php - use listapis2.php

= What are the dependencies? =

For listing and defining the APIs implemented by a WordPress plugin, or WordPress itself you will need the following:

* oik-shortcodes plugin
* oik-plugins plugin
* oik-fields plugin
* oik base plugin
* the WordPress plugin 
* a local WordPress installation


= How do I invoke it? =
Create a batch file such as batch.bat
` 
php c:\apache\htdocs\wordpress\wp-content\plugins\oik-batch\oik-batch.php %*
`
pass the name of the PHP file to invoke as the first parameter

To invoke listapis2 use:
`
batch listapis2 <i>plugin</i>  
`


Alternatively oik-batch.php can be included in the main routine. 
`
php c:\apache\htdocs\wordpress\wp-content\plugins\oik-batch\createapi2.php --plugin=%1 --site=http://oik-plugins.co.uk --apikey=apikey
`

== Screenshots ==
1. oik-batch in action performing createapi2.php

== Upgrade Notice ==
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


