<?php // (C) Copyright Bobbing Wide 2013-2017
/*
Plugin Name: oik-batch
Plugin URI: http://www.oik-plugins.com/oik-plugins/oik-batch
Description: standalone processing using a subset of WordPress 
Version: 0.9.0
Author: bobbingwide
Author URI: http://www.oik-plugins.com/author/bobbingwide
License: GPL2

    Copyright 2013 - 2017 Bobbing Wide (email : herb@bobbingwide.com )

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
		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			// This is WP-CLI
		} else {
			oik_batch_load_lib( "oik-cli" );
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
				// wp-batch has been loaded by another PHP routine so that routine is in charge.
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
			
			oik_batch_load_lib( "oik-cli" );
			oik_batch_load_oik_boot();
			add_action( "admin_notices", "oik_batch_activation" );
			add_action( "oik_admin_menu", "oik_batch_admin_menu" );
		}
	}
}

oik_batch_loaded();

