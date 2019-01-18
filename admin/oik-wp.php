<?php

/**
 * @copyright (C) Copyright Bobbing Wide 2019
 * @author @bobbingwide
 *
 */

function oik_wp_lazy_oik_menu_box() {

	BW_::oik_box( null, null, __( "oik-wp admin", "oik-batch" ), "oik_wp_options" );

}


/**
 *
 */



function oik_wp_options() {

	oik_wp_check_git();
	//$option = "bw_css_options";
	//$options = bw_form_start( $option, "oik_css_options" );
	//bw_checkbox_arr( $option, __( "Disable automatic paragraph creation", "oik-css" ), $options, "bw_autop" );
	//etag( "table" );
	//e( isubmit( "ok", __("Save changes", "oik-css" ), null, "button-secondary" ) );
	//etag( "form" );
	//BW_::p( __( "To enable automatic paragraph creation use the [bw_autop] shortcode.", "oik-css" ) );
	//BW_::p( __( "To disable automatic paragraph creation use [bw_autop off].", "oik-css" ) );
	bw_flush();
}

function oik_wp_check_git() {
	//$lib = oik_require_lib( "git" );
	BW_::p( __( "Checking GIT ", "oik-batch " ) );
	$path = oik_path( "libs/oik-git.php", "oik-batch" );
	if ( file_exists( $path ) ) {
		oik_require( "libs/oik-git.php", "oik-batch" );

		if ( function_exists( "git" ) ) {
			$git = git();
			oik_wp_check_gitability( $git );
			oik_wp_process_selected_plugin( $git );
			oik_wp_check_plugins( $git );
		} else {
			BW_::p( "Error with oik-git", "oik-batch" );
		}
	} else {
		BW_::p( "File not available: $path", "oik-batch" );
	}

	//print_r( $lib );
}

function oik_wp_check_gitability( $git ) {
	BW_::p( "Git class loaded");
	$result = $git->execute( "uname");
	BW_::p( "Operating system: " . $result );
	$repo = $git->is_git( ABSPATH );
	if ( $repo ) {
		BW_::p( "Git repo!" );
		$result = $git->command();
		BW_::p( $result );
	}
}

function oik_wp_check_plugins( $git ) {
	$plugins = [];
	$glob = glob( WP_PLUGIN_DIR . '/*', GLOB_ONLYDIR );
	foreach ( $glob as $dir ) {

		$result = oik_wp_get_plugin_info( $git, $dir );
		$result[ 'link' ] = oik_wp_check_plugin_link( $git, $result );
		$plugins[] = $result;
	}

	oik_wp_report_plugins( $plugins );

}

function oik_wp_check_plugin_link( $git, $git_plugin_info ) {
	$link = null;
	if ( $git_plugin_info[ 'plugin'] && $git_plugin_info[ 'remote'] ) {
		$plugin = $git_plugin_info[ 'plugin'];
		$link = retlink( null, admin_url("admin.php?page=oik_batch&amp;check_plugin=$plugin"), __( "Check", null ) );
	}
	return $link;

}

function oik_wp_get_plugin_info( $git, $dir ) {
	$git_plugin_info = array();
	$git_plugin_info[ "plugin" ] = basename( $dir );
	$is_git = $git->is_git( $dir );

	if ( $is_git === $dir  ) {
		//e( " tada");
		$remote = $git->check_remote();
		$git_plugin_info[ "remote" ] = $remote;



	} else {
		$git_plugin_info["remote"] = null;
		$git_plugin_info[ "status"] = null;
	}
	//print_r( $git_plugin_info );
	return $git_plugin_info;


}

function oik_wp_check_plugin( $git, $dir ) {
	$git_plugin_info = oik_wp_get_plugin_info( $git, $dir );
	if ( $git_plugin_info[ 'remote'] ) {

		$status = $git->maybe_needs_pulling();
		$git_plugin_info[ "status" ] = $status;
	} else {
		gob();
	}

	return $git_plugin_info;

}

function oik_wp_process_selected_plugin( $git ) {
	$plugin = bw_array_get( $_REQUEST, "check_plugin", null );
	if ( $plugin ) {
		$dir = WP_PLUGIN_DIR . '/' . $plugin;
		p( "Checking $plugin" );
		$git_plugin_info = oik_wp_check_plugin( $git, $dir );
		print_r( $git_plugin_info );
	}

}

function oik_wp_report_plugins( $plugins ) {
	stag( "table", "widefat");
	stag( "tbody" );
	foreach ( $plugins as $plugin ) {

		bw_tablerow( $plugin );
	}
	etag( "tbody" );

	etag( "table");


}