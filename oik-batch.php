<?php // (C) Copyright Bobbing Wide 2013-2016
/*
Plugin Name: oik-batch
Plugin URI: http://www.oik-plugins.com/oik-plugins/oik-batch
Description: standalone processing using a subset of WordPress 
Version: 0.8.6
Author: bobbingwide
Author URI: http://www.oik-plugins.com/author/bobbingwide
License: GPL2

    Copyright 2013 - 2016 Bobbing Wide (email : herb@bobbingwide.com )

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
			oik_batch_load_lib( "oik-cli" );
			oik_batch_debug();
			oik_batch_trace( true );
			//oik_batch_locate_wp_config();
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
				// }
			echo "End cli:" . __FUNCTION__ . PHP_EOL; 
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
 *
 *
 * If the file name given is in the form of a plugin file name e.g. plugin/plugin.php
 * then we can invoke it using oik_path() 
 * If it's just a simple name then we assume it's in the ??? folder and we need to append .php 
 * and invoke it using oik_path()
 * If it's a fully specified file name that exists then we call it directly
 */
 
if ( !function_exists( "oik_batch_run_script" ) ) { 
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
    $extension = bw_array_get( $script_parts, "extension", "php" );
    
       
    $required_file = WP_PLUGIN_DIR . "/$dirname/$filename.$extension";
    echo $required_file . PHP_EOL;
    if ( file_exists( $required_file ) ) {
      require_once( $required_file );
    } else {
      echo "Cannot find script to run: $required_file" . PHP_EOL;
    }  
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
if ( !function_exists( "oik_batch_run" ) ) { 
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
 * 
 * @return string the normalized path to the wp-config.php file
 */
if ( !function_exists( "oik_batch_locate_wp_config" ) ) {
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
	echo "ABSPATH: $abspath" . PHP_EOL;
	return( $abspath );
}
}

oik_batch_loaded();

