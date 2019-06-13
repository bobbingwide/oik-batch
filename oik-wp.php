<?php // (C) Copyright Bobbing Wide 2013-2019
/*
Plugin Name: oik-wp
Plugin URI: https://www.oik-plugins.com/oik-plugins/oik-batch
Description: standalone processing using a complete WordPress installation but not using WP-CLI 
Version: 1.0.0
Author: bobbingwide
Author URI: https://www.oik-plugins.com/author/bobbingwide
Text Domain: oik-wp
Domain Path: /languages/
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

    Copyright 2013-2019 Bobbing Wide (email : herb@bobbingwide.com )

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License version 2,
    as published by the Free Software Foundation.

    You may NOT assume that you can use any other version of the GPL.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    The license for this software can likely be found here:
    http://www.gnu.org/licenses/gpl-2.0.html

*/

/**
 * Load a library file
 * 
 * @param string $lib - the library file name e.g. oik-cli
 */
if ( !function_exists( "oik_batch_load_lib" ) ) {
	function oik_batch_load_lib( $lib ) {
		$dir = dirname( __FILE__ );
		$lib_file = "$dir/libs/$lib.php";
		if ( file_exists( $lib_file ) ) {
			require_once( $lib_file );
		} else {
			echo "Missing shared library file: $lib_file" . PHP_EOL;
		}
	}
}

/**
 * Start WordPress in the current directory
 * 
 * Note: In normal processing, index.php loads wp-blog-header.php, which loads wp-load.php, which loads wp-config.php ( if it can find it ), 
 * then wp-blog-header calls wp() then loads the theme template.
 *
 * We can't just run index.php rather than wp-config.php since we don't want WordPress to do normal WordPress stuff.   
 * And we can't just run wp-load.php because it might do things we don't want it to do.
 * So we have to perform some of the setup ourselves; particularly for WPMS.
 */
function oik_batch_start_wordpress() {
	$abspath = oik_batch_locate_wp_config_for_phpunit();
	
	if ( !$abspath ) {
		$abspath = oik_batch_locate_wp_config();
	}
	if ( !$abspath ) {
		$abspath = __FILE__;
		echo "Loading WordPress wp-config.php" . PHP_EOL;
		$abspath = dirname( dirname( dirname( dirname( $abspath ) ) ) );
		$abspath .= "/";
	}
	oik_batch_set_domain( $abspath );
	oik_batch_set_path();
	global $wpdb, $current_site;
	require( $abspath . "wp-config.php" );
	oik_batch_report_wordpress_version();
	oik_batch_maybe_set_scheme_from_siteurl();
}

/**
 *  It's a maybe since the scheme may already have been set for WPMS.
 */

function oik_batch_maybe_set_scheme_from_siteurl() {
	if ( defined( 'WP_SITEURL')) {
		$domain = WP_SITEURL;
	} else {
		$domain = get_option( "siteurl" );
	}
	oik_batch_set_scheme( $domain );
}

function oik_batch_set_scheme( $domain ) {
	if ( !isset( $_SERVER['HTTPS'])) {
		$scheme = parse_url( $domain, PHP_URL_SCHEME );
		if ( 'https' === strtolower( $scheme ) ) {
			$_SERVER['HTTPS'] = 'on';
		}
	}
}

/**
 * Bootstrap logic for oik-wp
 * 
 * 
 * The checks if we're running as "CLI" -Command Line Interface
 * - If we are then we prepare the batch environment
 * - then, if this file is the first file we run the routine specified on the command line
 * - or, if it's being run under PHPUnit then we load the unit test environment for the tests that will follow
 * 
 */
function oik_wp_loaded() {
	if ( PHP_SAPI == "cli" ) {
		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			// This is WP-CLI, so we don't do anything.
		} else {
			oik_batch_load_lib( "oik-cli" );
			oik_batch_merge_argv();
			oik_batch_start_wordpress();
			
			/*
			 * The plugin may decide to run itself automagically
			 * This code is probably all rubbish now!
			 */
			//echo "I got here" . PHP_EOL;
			//echo "oik-wp running WordPress 
			echo getcwd() . PHP_EOL;
			
			oik_batch_debug();
			//oik_batch_trace( true );
      
			//oik_batch_define_constants();	 // @TODO Not necessary in oik-wp since WordPress has done all of this! 
			oik_batch_load_oik_boot();
			//oik_batch_simulate_wp_settings();
			//oik_batch_load_wordpress_files();
			//oik_batch_load_cli_functions(); 
			echo PHP_SAPI;
			echo PHP_EOL;
			$included_files = get_included_files();
			if ( $included_files[0] == __FILE__) {
				 oik_batch_run();
			} else {
				if ( false !== strpos( $_SERVER['argv'][0], "phpunit" ) ) {
					// This is PHPUnit
					oik_batch_load_wordpress_develop_tests();
				}
			}
			// wp-batch has been loaded by another PHP routine so that routine is in charge. e.g. boot-fs.php for WP-CLI
			//echo "who's in charge?" . PHP_EOL;
		}
	} else {
		//echo PHP_SAPI;
		//echo PHP_EOL;
		if ( function_exists( "bw_trace2" ) ) {
			bw_trace2( PHP_SAPI, "oik-wp loaded in WordPress environment?" );
		}
		if ( function_exists( "add_action" ) ) {
			// if ( bw_is_wordpress() ) {
			
			oik_batch_load_lib( "oik-cli" );
			oik_batch_load_oik_boot();
			add_action( "admin_notices", "oik_batch_activation" );
			add_action( "oik_admin_menu", "oik_batch_admin_menu" );
			//add_action( "@TODO load shared libraries?
		} 
	}
}

/**
 * Locate and load the parts we need from WordPress develop tests
 *
 * Currently we're loading the file that expects us to be running the tests in situ.
 * @TODO We may also want to support running tests using a bootstrap file similar to those delivered
 * by other plugins.
 */
function oik_batch_load_wordpress_develop_tests() {
	oik_require( "tests/bootstrap.php", "oik-batch" );
}

oik_wp_loaded();
