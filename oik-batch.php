<?php // (C) Copyright Bobbing Wide 2013-2015
/*
Plugin Name: oik-batch
Plugin URI: http://www.oik-plugins.com/oik-plugins/oik-batch
Description: standalone processing using a subset of WordPress 
Version: 0.8
Author: bobbingwide
Author URI: http://www.oik-plugins.com/author/bobbingwide
License: GPL2

    Copyright 2013 - 2015 Bobbing Wide (email : herb@bobbingwide.com )

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
 * Simulate oik_boot for when oik is installed
 */      
function oik_batch_simulate_oik_boot() {
  gobang();
/** 
 * Return the array[index] or a default value if not set
 * 
 * @param mixed $array - an array or object or scalar item from which to find $index
 * @param scalar $index - the array index or object property to obtain
 * @param string $default - the default value to return 
 * @return mixed - the value found at the given index
 *
 * Notes: This routine may produce a Warning message if the $index is not scalar
 * I can't change it yet since there are other bits of code that may go wrong if I attempt 
 * to deal with an invalid  $index parameter. 
 */
if ( !function_exists( 'bw_array_get' ) ) {
  function bw_array_get( $array = NULL, $index, $default=NULL ) { 
    //if ( is_array( $index ) ) {
    //  bw_backtrace();
    //  gobang();
    //  sometimes we get passed an empty array as the index to the array - what should we do in this case **?** Herb 2013/10/24
    //}
    if ( isset( $array ) ) {
      if ( is_array( $array ) ) {
        if ( isset( $array[$index] ) || array_key_exists( $index, $array ) ) {
          $value = $array[$index];
        } else {
          $value = $default;
        }  
      } elseif ( is_object( $array ) ) {
        if ( property_exists( $array, $index ) ) {
          $value = $array->$index;
        } else {
          $value = $default;
        } 
      } else {
        $value = $default;
      }  
    } else {
      $value = $default;
    }  
    return( $value );
  }
}
}
 
/**
 * Turn on debugging for oik-batch
 */ 
function oik_batch_debug() {
  define( 'WP_DEBUG', true );
  error_reporting(E_ALL);
  @ini_set('display_errors',1);
}

/**
 * Enable trace and action trace for oik-batch
 *
 * @TODO Make it so we can turn trace on and off Herb 2014/06/09 
 */
function oik_batch_trace( $trace_on=false ) {
  if ( $trace_on ) {
    define( 'BW_TRACE_CONFIG_STARTUP', true );
    define( 'BW_TRACE_ON', true);
    define( 'BW_ACTIONS_ON', true );
    define( 'BW_TRACE_RESET', false );
    define( 'BW_ACTIONS_RESET', false );
  } else {
    // We don't do the defines so it can be done later.
  } 
   
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
 * @TODO This solution is not yet catered for. See oik_batch_define_oik_batch_dir()
 *
 * If not defined here then these constants will be defined in other source files such as default-constants.php 
 */
function oik_batch_define_constants() {
  if ( !defined('ABSPATH') ) {
    /** Set up WordPress environment */
    global $wp_did_header;
    $abspath = __FILE__;
    echo "Setting ABSPATH: $abspath" . PHP_EOL;
    $abspath = dirname( dirname( dirname( dirname( $abspath ) ) ) );
    $abspath .= "/";
    echo "Setting ABSPATH: $abspath" . PHP_EOL;
    define( "ABSPATH", $abspath );
    define('WP_USE_THEMES', false);
    $wp_did_header = true;
    //require_once('../../..//wp-load.php');
    
    // We can't load bwtrace.inc until we know ABSPATH
    //require_once( ABSPATH . 'wp-content/plugins/oik/bwtrace.inc' );
    
    define( 'WPINC', 'wp-includes' );
    
  	if ( !defined('WP_CONTENT_DIR') )
  		define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' ); // no trailing slash, full paths only - copied from default-constants.php

    if ( !defined('WPMU_PLUGIN_DIR') ) {
      define( 'WPMU_PLUGIN_DIR', WP_CONTENT_DIR . '/mu-plugins' ); // full path, no trailing slash
    }
  }
}

/**
 * Set the OIK_BATCH_DIR constant
 *
 * If you want to run oik-batch against the current directory then
 * it would make sense to assume that the files come from within this directory somewhere
 * However, get_plugin_files() - return a list of files relative to the plugin's root uses WP_PLUGIN_DIR
 * which is set from ABSPATH.
 * If we try to set ABSPATH then we'll have to ensure that ALL of the plugins needed by the oik-batch routine are within the current directory.
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
 * Simulate those parts of wp-settings.php that are required
 * 
 */
function oik_batch_simulate_wp_settings() {
  $GLOBALS['wp_plugin_paths'] = array();
}

/**
 * Batch WordPress without database
 *
 * Load the required WordPress include files for the task in hand.
 * These files are a subset of the full set of WordPress includes.
 * We may also need the oik-bwtrace plugin if there are any bw_trace2() calls temporarily inserted into the WordPress files for debugging purposes.
 *
 */
function oik_batch_load_wordpress_files() {
  // Load the L10n library.
  require_once( ABSPATH . WPINC . '/l10n.php' ); // for get_translations_for_domain()
  require_once( ABSPATH . WPINC . "/formatting.php" );
  require_once( ABSPATH . WPINC . "/plugin.php" );
  //require_once( ABSPATH . WPINC . "/option.php" );
  require_once( ABSPATH . WPINC . "/functions.php" );
  require( ABSPATH . WPINC . '/class-wp-error.php' );
  
  require_once( ABSPATH . WPINC . "/load.php" );
  // Not sure if we need to load cache.php ourselves
  // require_once( ABSPATH . WPINC . "/cache.php" );
  require_once( ABSPATH . WPINC . "/version.php" );
  require_once( ABSPATH . WPINC . "/post.php" ); // for add_post_type_support()
  wp_load_translations_early();
}

/**
 * Load the oik_boot.inc file from the oik base plugin
 *
 * If the oik_init() function is not already defined
 * then load it from the oik directory relative to the current file
 * 
 */
function oik_batch_load_oik_boot() {
  if ( !function_exists( "oik_init" ) ) {
    $dir = dirname( __FILE__ );
    $parent_dir = dirname( $dir );
    echo $parent_dir . PHP_EOL;
    $oik_boot = "$parent_dir/oik/oik_boot.inc";
    echo $oik_boot . PHP_EOL;
    if ( file_exists( $oik_boot ) ) {
      require_once( $oik_boot );
    } else {
      oik_batch_simulate_oik_boot(); 
    }
  }  
  if ( function_exists( "oik_init" ) ) {
    oik_init();
  }
}

/**
 * Implement "admin_notices" hook for oik-batch to check plugin dependency
 *
 * Note: createapi2 and listapis2 are dependent upon oik-shortcodes, BUT oik-batch itself is not.
 * 
 */
function oik_batch_activation() {
  static $plugin_basename = null;
  if ( !$plugin_basename ) {
    $plugin_basename = plugin_basename(__FILE__);
    add_action( "after_plugin_row_oik-batch/oik-batch.php", "oik_batch_activation" );
    if ( !function_exists( "oik_plugin_lazy_activation" ) ) {   
      require_once( "admin/oik-activation.php" );
    }  
  }  
  $depends = "oik:2.2";
  oik_plugin_lazy_activation( __FILE__, $depends, "oik_plugin_plugin_inactive" );
}

/**
 * Bootstrap logic for oik-batch
 * 
 * 
 * The checks if we're running as "CLI" - command line interface
 * - If we are then we prepare the batch environment
 * - then, if this file is the first file then we run the routine specified on the command line.
 * 
 */
function oik_batch_loaded() {
  if ( PHP_SAPI == "cli" ) {
    if ( $_SERVER['argv'][0] == "boot-fs.php" )   {
      // This is WP-CLI
    } else {
      oik_batch_debug();
      oik_batch_trace( true );
      oik_batch_define_constants();
      oik_batch_load_oik_boot();
      oik_batch_simulate_wp_settings();
      oik_batch_load_wordpress_files(); 
      echo PHP_SAPI;
      echo PHP_EOL;
    $included_files = get_included_files();
    if ( $included_files[0] == __FILE__) {
        oik_batch_run();
    }// else {
     // wp-batch has been loaded by another PHP routine so that routine is in charge. e.g. boot-fs.php for WP-CLI
     
    }
  } else {
    //echo PHP_SAPI;
    //echo PHP_EOL;
    if ( function_exists( "bw_trace2" ) ) {
      bw_trace2( PHP_SAPI, "oik-batch loaded in WordPress environment?" );
    }
    if ( function_exists( "add_action" ) ) {
      // if ( bw_is_wordpress() ) {
      add_action( "admin_notices", "oik_batch_activation" );
      add_action( "oik_admin_menu", "oik_batch_admin_menu" );
    } 
  }
}

/**
 * Implement "oik_admin_menu" action for oik-batch
 *
 * Register the plugin as being supported from an oik-plugins server  
 */
function oik_batch_admin_menu() {
  oik_register_plugin_server( __FILE__ );
}

/**
 *
 *
 * If the file name given is in the form of a plugin file name e.g. plugin/plugin.php
 * then we can invoke it using oik_path() 
 * If it's just a simple name then we assume it's in the ??? folder and we need to append .php 
 * and invoke it using oik_path()
 * If it's a fully specified file name that exists then we call it directly
 */
function oik_batch_run_script( $script ) {
  if ( file_exists( $script ) ) {
    require_once( $script ); 
  } else {
    $script_parts = pathinfo( $script );
    print_r( $script_parts );
    $dirname = bw_array_get( $script_parts, "dirname", null );
    if ( $dirname == "." ) {
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
    //print_r( $_SERVER['argv'] );
    $_SERVER['argc']--;
    //print_r( $_SERVER['argc'] );
    oik_batch_run_script( $script );
  }   
}



oik_batch_loaded();

