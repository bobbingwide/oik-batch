<?php

/**
 * @copyright (C) Copyright Bobbing Wide 2019
 * @author @bobbingwide
 *
 */

function oik_wp_lazy_oik_menu_box() {
	BW_::oik_box( null, null, __( "oik-wp options", "oik-batch" ), "oik_wp_options" );

}



function oik_wp_options() {
	BW_::p( __( "Checking GIT ", "oik-batch "));
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
	oik_require( "libs/oik-git.php", "oik-batch" );
	if ( function_exists( "git")) {
		$git = git();
		BW_::p( "Git class loaded");
		$result = $git->command();
		BW_::p( $result );
	}

	//print_r( $lib );
}