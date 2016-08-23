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
 * Locate the wp-config.php they expected to use
 *
 * __FILE__ may be a symlinked directory
 * but we need to work based on the current directory
 * so we work our way up the directory path until we find a wp-config.php
 * and treat that directory as abspath
 * 
 * The ABSPATH constant refers to the directory in which WP is installed.
 * as we see in the comment in wp-config.php 
 * `Absolute path to the WordPress directory.`
 * 
 * 
 * What if we move wp-config.php to the directory above?
 * then we have to set ABSPATH differently?
 * 
 * @return string the normalized path to the wp-config.php file
 */
function oik_batch_locate_wp_config() {
	$owd = getcwd();
	$owd = oik_normalize_path( $owd );
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
	//echo "wp-config in: $abspath" . PHP_EOL;
	echo "ABSPATH: $abspath" . PHP_EOL;
	return( $abspath );
}

/**
 * Normalize a path to UNIX style
 * 
 * Similar to wp_normalize_path except this doesn't deal with double slashes...
 * which might be a good thing if we try to use it for URLs! 
 * 
 * @param string $path - path or filename
 * @return string path with backslashes converted to forward and drive letter capitalized
 */
function oik_normalize_path( $path ) {
	$path = str_replace( "\\", "/", $path );
	if ( ':' === substr( $path, 1, 1 ) ) {
		$path = ucfirst( $path );
	}
	return( $path );
}

/**
 * Report the version of WordPress
 * 
 * We may not be able to do this in oik-batch
 */
function oik_batch_report_wordpress_version() {
	global $wp_version;
	printf( __( "oik-wp running WordPress %s", "oik-batch" ), $wp_version );
	echo PHP_EOL;
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

/**
 * Implement "oik_admin_menu" action for oik-batch
 *
 * Register the plugin as being supported from an oik-plugins server
 * Does this work for oik-wp as well?  
 */
function oik_batch_admin_menu() {
	
  oik_register_plugin_server( __FILE__ );
}

/**
 * Implement "admin_notices" hook for oik-wp and oik-batch to check plugin dependency
 *
 * Note: createapi2 and listapis2 are dependent upon oik-shortcodes, BUT oik-batch itself is not.
 * Not yet... Anyway createapi2 and listapis2 should be moved to oik-shortcodes.
 * BUT oik-batch/oik-wp.php may have a user interface for defining / redefining the initial status for PHPUnit tests
 * so it's probably dependent upon oik v3.0 
 * 
 */
function oik_batch_activation() {
  static $plugin_basename = null;
  if ( !$plugin_basename ) {
    $plugin_basename = plugin_basename(__FILE__);
    add_action( "after_plugin_row_oik-batch/oik-batch.php", "oik_batch_activation" );
		add_action( "after_plugin_row_oik-batch/oik-wp.php", "oik_batch_activation" );
    if ( !function_exists( "oik_plugin_lazy_activation" ) ) { 
      oik_require( "admin/oik-activation.php", "oik-batch" );
    }  
  }  
  $depends = "oik:3.0";
	// We have to tell the lazy activation routine the correct plugin; oik-batch or oik-wp ? 
	// So don't do it for admin_notices only the after_plugin_row ones
	$current_filter = current_filter();
	if ( "admin_notices" != $current_filter ) {
		$plugin = basename(  $current_filter );
		oik_plugin_lazy_activation( $plugin, $depends, "oik_plugin_plugin_inactive" );
	}
}

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
      $dirname = "oik-batch"; // @TODO - make it choose the current directory
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
		// Should this call do_action( "run_$??? " ). If so, what's $???
		// How does WP-cli work?  
  }
}






