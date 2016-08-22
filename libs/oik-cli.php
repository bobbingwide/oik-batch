<?php // (C) Copyright Bobbing Wide 2015,2016

/**
 * Command Line Interface (CLI) functions
 *
 * This file should eventually be a shared library file
 * containing some of the common routines used in the oik-zip, oik-tip and other routines
 * including those that deal with directory changes in symlinked environments
 * and others that return responses to the calling routines and make decisions based on them
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
 * Load the oik_boot.php shared library file
 * 
 * If the oik_init() function is not already defined then load the shared library file from our own libs directory.
 *
 * Originally we loaded oik_boot.inc from the oik base plugin.
 * This is now a shared library file that we deliver in the libs folder, along with bwtrace.php
 * We need to run oik_init() in order to ensure trace functions are available.
 */
function oik_batch_load_oik_boot() {
	if ( !function_exists( "oik_init" ) ) {
		oik_batch_load_lib( "oik_boot" );
	}  
	if ( function_exists( "oik_init" ) ) {
		oik_init();
	}
}


/**
 * Prompt to check if the process should be continued
 *
 * This routine does not make any decisions.
 * If you want to stop you just press Ctrl-Break.
 *
 */
if ( !function_exists( 'docontinue' ) ) { 
function docontinue( $plugin="Press Ctrl-Break to halt" ) {
	echo PHP_EOL;
	echo "Continue? $plugin ";
	$stdin = fopen( "php://stdin", "r" );
	$response = fgets( $stdin );
	$response = trim( $response );
	fclose( $stdin );
	return( $response );
}
}

if ( !function_exists( "oik_batch_run_me" ) ) {
function oik_batch_run_me( $me ) {
	$run_me = false;
	echo PHP_SAPI;
	echo PHP_EOL;
	$included_files = get_included_files();
	// print_r( $included_files[0] );
	if ( $included_files[0] == __FILE__) {
		$run_me = true;
	} else {
		//  has been loaded by another PHP routine so that routine is in charge. e.g. boot-fs.php for WP-CLI
		$basename = basename( $included_files[0] );
		if ( $basename == "oik-wp.php" ) {
			print_r( $_SERVER );
			$fetched = bw_array_get( $_SERVER['argv'], 1, null );
			if ( $fetched ) {
				$fetched_basename = basename( $fetched );
				$me_basename = basename( $me );
				$run_me = ( $fetched_basename == $me_basename );
			}	
		}
		if ( $basename == "oik-batch.php" ) {
		
			print_r( $_SERVER );
			$fetched = $_SERVER['argv'][0];
			$fetched_basename = basename( $fetched );
			$me_basename = basename( $me );
			$run_me = ( $fetched_basename == $me_basename );
		}
	}
	return( $run_me );
}
}

/**
 * Batch WordPress without database
 *
 * Load the required WordPress include files for the task in hand.
 * These files are a subset of the full set of WordPress includes.
 * We may also need the oik-bwtrace plugin if there are any bw_trace2() calls temporarily inserted into the WordPress files for debugging purposes.
 *
 * This is not needed in oik-wp.php
 */
function oik_batch_load_wordpress_files() {
	// Load the L10n library.
	require_once( ABSPATH . WPINC . '/l10n.php' ); // for get_translations_for_domain()
	require_once( ABSPATH . WPINC . "/formatting.php" );
	require_once( ABSPATH . WPINC . "/plugin.php" );
	//require_once( ABSPATH . WPINC . "/option.php" );
	require_once( ABSPATH . WPINC . "/functions.php" );
	require_once( ABSPATH . WPINC . '/class-wp-error.php' );
  
	require_once( ABSPATH . WPINC . "/load.php" );
	// Not sure if we need to load cache.php ourselves
	// require_once( ABSPATH . WPINC . "/cache.php" );
	require_once( ABSPATH . WPINC . "/version.php" );
	require_once( ABSPATH . WPINC . "/post.php" ); // for add_post_type_support()
	wp_load_translations_early();
}
   
/**
 * Simulate those parts of wp-settings.php that are required
 * 
 */
function oik_batch_simulate_wp_settings() {
  $GLOBALS['wp_plugin_paths'] = array();
}

/**
 * Set the OIK_BATCH_DIR constant
 *
 * If you want to run oik-wp/oik-batch against the current directory then
 * it would make sense to assume that the files come from within this directory somewhere
 * However, get_plugin_files() - return a list of files relative to the plugin's root uses WP_PLUGIN_DIR
 * which is set from ABSPATH.
 * If we try to set ABSPATH then we'll have to ensure that ALL of the plugins needed by the oik-wp routine are within the current directory.
 * This is not going to be the case.
 * SO... get_plugin_files() should not be used when OIK_BATCH_DIR is set differently from WP_PLUGIN_DIR
 * This change will also be necessary when we want to support themes.
 * 
 */  
function oik_batch_define_oik_batch_dir() {
	///if ( !defined( 'OIK_BATCH_DIR' ) ) {
	//  define( 'OIK_BATCH_DIR', getcwd() );
	//}
}


/**
 * Define the mandatory constants that allow WordPress to work
 * 
 * The logic to set ABSPATH was originally defined to allow the oik-batch to be used from one folder
 * while batch processing is working against other WordPress instances.
 * 
 * The current logic is to set the ABSPATH by working upwards from the current file.
 * This therefore requires oik-batch to be "installed" as a plugin with similar requirements to WP-CLI
 * 
 * In order to be able to run batch files against different instances of WordPress you will need to
 * use a "batch" routine that invokes the correct version of the required batch routine
 * while finding the appropriate version of the source.
 *  
 * @TODO This solution is not yet catered for. ISN'T IT? 
 *
 * If not defined here then these constants will be defined in other source files such as default-constants.php 
 */
function oik_batch_define_constants() {
	if ( !defined('ABSPATH') ) {
		/** Set up WordPress environment */
		global $wp_did_header;
		echo "Setting ABSPATH:". PHP_EOL;
		
		$abspath = oik_batch_locate_wp_config();
		
		//$abspath = __FILE__;
		//$abspath = dirname( dirname( dirname( dirname( $abspath ) ) ) );
		//$abspath .= "/";
		//$abspath = str_replace( "\\", "/", $abspath );
		//if ( ':' === substr( $abspath, 1, 1 ) ) {
		//	$abspath = ucfirst( $abspath );
		//}
		echo "Setting ABSPATH: $abspath" . PHP_EOL;
		define( "ABSPATH", $abspath );
		define('WP_USE_THEMES', false);
		$wp_did_header = true;
		//require_once('../../..//wp-load.php');
		
		// We can't load bwtrace.inc until we know ABSPATH
		//require_once( ABSPATH . 'wp-content/plugins/oik/bwtrace.inc' );
			
		define( 'WPINC', 'wp-includes' );
    
		if ( !defined('WP_CONTENT_DIR') )	{
				define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' ); // no trailing slash, full paths only - copied from default-constants.php
		}
		if ( !defined('WPMU_PLUGIN_DIR') ) {
			define( 'WPMU_PLUGIN_DIR', WP_CONTENT_DIR . '/mu-plugins' ); // full path, no trailing slash
		}
	}
}


/**
 * Turn on debugging for oik-batch
 * 
 * We're running in batch mode so we want to see and log all errors.
 */ 
function oik_batch_debug() {
	if ( !defined( "WP_DEBUG" ) ) {
		define( 'WP_DEBUG', true );
	}  
	error_reporting(E_ALL);
	ini_set( 'display_errors', 1);
	ini_set( 'log_errors', 1 );
}

/**
 * Enable trace and action trace for oik-batch routines
 *
 * @TODO Make it so we can turn trace on and off Herb 2014/06/09 
 * 
 * @param bool $trace_on
 */
function oik_batch_trace( $trace_on=false ) {
	if ( $trace_on ) {
		if ( !defined( 'BW_TRACE_ON' )  ) {
			define( 'BW_TRACE_CONFIG_STARTUP', true );
			define( 'BW_TRACE_ON', true);
			define( 'BW_TRACE_RESET', false );
		}  
	} else {
		// We don't do the defines so it can be done later.
	} 
}






