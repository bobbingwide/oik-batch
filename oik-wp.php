<?php // (C) Copyright Bobbing Wide 2013-2016
/*
Plugin Name: oik-wp
Plugin URI: http://www.oik-plugins.com/oik-plugins/oik-wp
Description: standalone processing using a complete WordPress installation but not using WP-CLI 
Version: 0.0.2
Author: bobbingwide
Author URI: http://www.oik-plugins.com/author/bobbingwide
Text Domain: oik-wp
Domain Path: /languages/
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

    Copyright 2015,2016 Bobbing Wide (email : herb@bobbingwide.com )

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
 * We can't just run index.php rather than wp-config.php since we don't want WordPress to do normal WordPress stuff
 * Since we don't run wp-load.php, (why not?)  there are some things that we need to set up, particularly for WPMS, that would have been done
 * in wp() 
 * 
 * See 
 * wp-blog-header.php
 *   wp-load.php
 *
 */
function oik_batch_start_wordpress() {
  //global $wp_did_header;
  //$wp_did_header = true;
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
	//require( $abspath . "wp-load.php" );
	//require( $abspath .  "index.php" );
	oik_batch_report_wordpress_version();
}

/**
 * Report the version of WordPress
 */
function oik_batch_report_wordpress_version() {
	global $wp_version;
	printf( __( "oik-wp running WordPress %s", "oik-batch" ), $wp_version );
	echo PHP_EOL;
}

/**
 * Locate the wp-config.php they expected to use
 *
 * __FILE__ may be a symlinked directory
 * but we need to work based on the current directory
 * so we work our way up the directory path until we find a wp-config.php
 * and treat that directory as abspath
 */

function oik_batch_locate_wp_config() {
	$owd = getcwd();
	$abspath = null;
	while ( $owd ) {
		if ( file_exists( $owd . "/wp-config.php" ) ) { 
			$abspath = $owd . '/';
			$owd = null;
		} else {
			$next = dirname( $owd );
			//echo "Checking $next after $owd" . PHP_EOL;
			if ( $next == $owd ) {
				$owd = null;
			}	else {
				$owd = $next;
			}
			
		}
	}
	echo "wp-config in: $abspath" . PHP_EOL;
	return( $abspath );
}



/**
 * Implement "admin_notices" hook for oik-wp to check plugin dependency
 * 
 */
function oik_wp_activation() {
  static $plugin_basename = null;
  if ( !$plugin_basename ) {
    $plugin_basename = plugin_basename(__FILE__);
    add_action( "after_plugin_row_oik-wp/oik-wp.php", "oik_wp_activation" );
    if ( !function_exists( "oik_plugin_lazy_activation" ) ) {   
      require_once( "admin/oik-activation.php" );
    }  
  }  
  $depends = "oik:2.4";
  oik_plugin_lazy_activation( __FILE__, $depends, "oik_plugin_plugin_inactive" );
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
		if ( $_SERVER['argv'][0] == "boot-fs.php" )   {
			// This is WP-CLI, so we don't do anything.
		} else { 
			oik_batch_load_lib( "oik-cli" );
			oik_batch_start_wordpress();
			
			/*
			 * The plugin may decide to run itself automagically
			 * This code is probably all rubbish now!
			 */
			//echo "I got here" . PHP_EOL;
			//echo "oik-wp running WordPress 
			echo getcwd() . PHP_EOL;
			
			oik_batch_debug();
			oik_batch_trace( true );
      
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
			add_action( "admin_notices", "oik_batch_activation" );
			add_action( "oik_admin_menu", "oik_batch_admin_menu" );
			//add_action( "@TODO load shared libraries?
		} 
	}
}

/**
 * Run a script in batch
 *
 * @TODO Check these comments
 * If the file name given is in the form of a plugin file name e.g. plugin/plugin.php
 * then we can invoke it using oik_path() 
 * If it's just a simple name then we assume it's in the ??? folder and we need to append .php 
 * and invoke it using oik_path()
 * If it's a fully specified file name that exists then we call it directly.
 *
 * The script can be run by simply loading the file
 * and/or it can implement an action hook for "run_$script"
 *
 * @param string $script the file to load and run
 *
 */
//if ( !function_exists( "oik_batch_run_script" ) ) { 
function oik_batch_run_script( $script ) {
  if ( file_exists( $script ) ) {
		oik_require( "oik-login.inc", "oik-batch" );
    require_once( $script ); 
		echo "Script required once: $script" . PHP_EOL;
		do_action( "run_$script" );
		echo "Did: run_$script" . PHP_EOL;
  } else {
    $script_parts = pathinfo( $script );
    print_r( $script_parts );
    $dirname = bw_array_get( $script_parts, "dirname", null );
    if ( $dirname == "." ) {
      $dirname = "oik-wp"; // @TODO - make it choose the current directory
    } 
    $filename = bw_array_get( $script_parts, "filename", null );
    $extension = bw_array_get( $script_parts, "extension", ".php" );
    
       
    $required_file = WP_PLUGIN_DIR . "/$dirname/$filename$extension";
    echo $required_file . PHP_EOL;
    if ( file_exists( $required_file ) ) {
      require_once( $required_file );
    } else {
      echo "Cannot find script to run: $required_file" . PHP_EOL;
    }  
  }
}
//}

/**
 * Run the script specified having pre-loaded wp-batch code
 *
 * Before loading the script we shift the args so that it thinks it's been invoked directly
 *
 * We will assume that a partial path to the routine to be run ($server) has been specified
 */
function oik_batch_run() {
  if ( $_SERVER['argc'] >=2  ) {
    $script = $_SERVER['argv'][1]; 
    //print_r( $_SERVER['argv'] );
    array_shift( $_SERVER['argv'] );
		echo "Shifting argv" . PHP_EOL;
    //print_r( $_SERVER['argv'] );
    $_SERVER['argc']--;
    //print_r( $_SERVER['argc'] );
    oik_batch_run_script( $script );
  }   
}


/**
 * WordPress MultiSite needs to know which domain we're working on
 * 
 * We extract it from $_SERVER['argv'] array, looking for url=domain/path
 *
 * We need to know the URL e.g. qw/oikcom or wp-a2z in order to be able to set both HTTP_HOST and REQUEST_URI
 *
 * For WPMS with a subdirectory install we need to be able to differentiate between the directory in which WordPress is installed
 * 
 * and the directory for the subdomain
 * So we'll need a value for path too?
 * 
 * 
 * 
 * 
 * @param string $abspath
 */
function oik_batch_set_domain( $abspath ) {
	$domain = oik_batch_query_value_from_argv();
	echo "Domain: $domain" . PHP_EOL;
	
	if ( !isset( $_SERVER['HTTP_HOST']) ) {
		//print_r( $_SERVER );
		$_SERVER['HTTP_HOST'] = $domain;
	}
	
	if ( !isset( $_SERVER['REQUEST_URI'] ) ) {
		$_SERVER['REQUEST_URI'] = "/";
	}	
	
	if ( !isset( $_SERVER['SERVER_NAME'] ) ) {
		$_SERVER['SERVER_NAME'] = $domain;
		$_SERVER['SERVER_PORT'] = "80";
	}

// $_SERVER['REQUEST_URI'] = $f('path') . ( isset( $url_parts['query'] ) ? '?' . $url_parts['query'] : '' );
// $_SERVER['SERVER_PORT'] = \WP_CLI\Utils\get_flag_value( $url_parts, 'port', '80' );
// $_SERVER['QUERY_STRING'] = $f('query');
}

/**
 * Set the path for WPMS
 */
function oik_batch_set_path() {
	$path = oik_batch_query_value_from_argv( "path", null );
	if ( $path ) {
		$_SERVER['REQUEST_URI'] = $path;
	}
}

/**
 * Obtain a value for a command line parameter
 *
 * If the required parameter key is numeric then we take the positional parameter
 * else we take value of an NVP pair.
 *
 * This is a simple hack that's not as advanced as WP-CLI, which allow --no- prefixes to set parameters to false
 * Here we're really only interested in getting url=
 *
 * @param string $key Not expected to be prefixed with --
 * @param string $default Default value if not found
 * @return string value of the parameter
 */
function oik_batch_query_value_from_argv( $key="url", $default="localhost" ) {
	$argv = $_SERVER['argv'];
	$value = $default;
	if ( $_SERVER['argc'] ) {
		if ( is_numeric( $key ) ) {
			$value = oik_batch_query_positional_value_from_argv( $_SERVER['argv'], $key, $default );
		} else {
			$value = oik_batch_query_nvp_value_from_argv( $_SERVER['argv'], $key, $default );
		}
	}	
	return( $value );
}

/**
 * Query a positional parameter
 *
 * We start counting from 0 - which allows us to get the routine name
 * 
 *
 */
function oik_batch_query_positional_value_from_argv( $argv, $index, $default ) {
	$arg_index = 0;
	$value = $default;
	foreach ( $argv as $key => $arg_value ) {
		if ( false === strpos( $arg_value, "=" ) ) {
			if ( $arg_index == $index ) {
				$value = $arg_value;
			}
			$arg_index++;
		}
			
	}
	return( $value );
}

/**
 * 
 */
function oik_batch_query_nvp_value_from_argv( $argv, $key, $default ) {
	$value = $default;
	foreach ( $argv as $arg_value ) {
		if ( false !== strpos( $arg_value, "=" ) ) {
		 $arg_value = strtolower( $arg_value );
			$arg_parts = explode( "=", $arg_value );
			if ( count( $arg_parts ) == 2 && $arg_parts[0] == $key ) {
				$value = $arg_parts[1];
			}
		}
	}
	return( $value );

}

/**
 * Locate and load the parts we need from WordPress develop tests
 *
 * 
 */
function oik_batch_load_wordpress_develop_tests() {
	oik_require( "tests/bootstrap.php", "oik-batch" );
}

/**
 * Reset current working directory under PHPUnit
 *
 * See notes in Issue #9
 */
function oik_batch_locate_wp_config_for_phpunit() {
	$abspath = null;
	if ( false !== strpos( $_SERVER['argv'][0], "phpunit" ) ) {
		$pre_phpunit_cd = getenv( "PRE_PHPUNIT_CD" );
		if ( $pre_phpunit_cd ) {
		 echo "Searching for wp-config.php in directories leading to: $pre_phpunit_cd" . PHP_EOL;
		 $abspath = oik_batch_cd_drill_down( $pre_phpunit_cd );
		}
	}
	return( $abspath );
}

/**
 * Drill down to locate the lowest file
 * 
 * In Windows, when you're using symlinks and PHP's chdir() the resulting directory reported by getcwd()
 * reflects the real directory. This might not be the one you first thought of.
 * It makes finding files a little tricky, hence the need for this function.
 *
 * @param string $path the ending directory
 * @param string $locate_file the file we're looking for
 * @return string|null the lowest directory or null
 */
function oik_batch_cd_drill_down( $path, $locate_file="wp-config.php" ) {
	$abspath = null;
	$path = str_replace( "\\", "/", $path );
  $paths = explode( "/", $path );
	foreach ( $paths as $cd ) {
		$success = chdir( $cd );
		if ( $success ) {
			$now = getcwd();
			//echo "$cd got me here $now" . PHP_EOL;
			if ( file_exists( $locate_file ) ) {
				$abspath = $now;
				$abspath .= '/';
				echo "Found $locate_file in: $abspath" . PHP_EOL;
			}
		} else {
			echo "Error performing chdir to $cd" . PHP_EOL;
		}
	}
	return( $abspath );
}
		


oik_wp_loaded();
