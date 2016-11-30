<?php // (C) Copyright Bobbing Wide 2016
/**
 * This file is a subset of the code from /phpunit/includes/functions.php
 * stripped down to what we actually need for in situ tests
 */

//require_once dirname( __FILE__ ) . '/class-basic-object.php';
//require_once dirname( __FILE__ ) . '/class-basic-subclass.php';

/**
 * Resets various `$_SERVER` variables that can get altered during tests.
 */
function tests_reset__SERVER() {
	$_SERVER['HTTP_HOST']       = WP_TESTS_DOMAIN;
	$_SERVER['REMOTE_ADDR']     = '127.0.0.1';
	$_SERVER['REQUEST_METHOD']  = 'GET';
	$_SERVER['REQUEST_URI']     = '';
	$_SERVER['SERVER_NAME']     = WP_TESTS_DOMAIN;
	$_SERVER['SERVER_PORT']     = '80';
	$_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';

	unset( $_SERVER['HTTP_REFERER'] );
	unset( $_SERVER['HTTPS'] );
}

// For adding hooks before loading WP
function tests_add_filter($tag, $function_to_add, $priority = 10, $accepted_args = 1) {
	global $wp_filter;

	if ( function_exists( 'add_filter' ) ) {
		add_filter( $tag, $function_to_add, $priority, $accepted_args );
	} else {
		$idx = _test_filter_build_unique_id($tag, $function_to_add, $priority);
		$wp_filter[$tag][$priority][$idx] = array('function' => $function_to_add, 'accepted_args' => $accepted_args);
	}
	return true;
}

function _test_filter_build_unique_id($tag, $function, $priority) {
	if ( is_string($function) )
		return $function;

	if ( is_object($function) ) {
		// Closures are currently implemented as objects
		$function = array( $function, '' );
	} else {
		$function = (array) $function;
	}

	if (is_object($function[0]) ) {
		return spl_object_hash($function[0]) . $function[1];
	} else if ( is_string($function[0]) ) {
		// Static Calling
		return $function[0].$function[1];
	}
}

/**  
 * Disabled
 *
 * Oh no you bloody well don't
 */
function _delete_all_data() {
	bw_backtrace();
	gob(); 
}


/**  
 * Disabled
 *
 * Oh no you bloody well don't
 */
function _delete_all_posts() {
	bw_backtrace();
	gob();
}

function _wp_die_handler( $message, $title = '', $args = array() ) {
	if ( !$GLOBALS['_wp_die_disabled'] ) {
		_wp_die_handler_txt( $message, $title, $args);
	} else {
		//Ignore at our peril
	}
}

function _disable_wp_die() {
	$GLOBALS['_wp_die_disabled'] = true;
}

function _enable_wp_die() {
	$GLOBALS['_wp_die_disabled'] = false;
}

function _wp_die_handler_filter() {
	return '_wp_die_handler';
}

function _wp_die_handler_txt( $message, $title, $args ) {
	echo "\nwp_die called\n";
	echo "Message : $message\n";
	echo "Title : $title\n";
	if ( ! empty( $args ) ) {
		echo "Args: \n";
		foreach( $args as $k => $v ){
			echo "\t $k : $v\n";
		}
	}
}

/**
 * Set a permalink structure.
 *
 * Hooked as a callback to the 'populate_options' action, we use this function to set a permalink structure during
 * `wp_install()`, so that WP doesn't attempt to do a time-consuming remote request.
 *
 * @since 4.2.0
 */
function _set_default_permalink_structure_for_tests() {
	bw_backtrace();
	gob();
	//update_option( 'permalink_structure', '/%year%/%monthnum%/%day%/%postname%/' );
}

/**
 * Helper used with the `upload_dir` filter to remove the /year/month sub directories from the uploads path and URL.
 */
function _upload_dir_no_subdir( $uploads ) {
	$subdir = $uploads['subdir'];

	$uploads['subdir'] = '';
	$uploads['path'] = str_replace( $subdir, '', $uploads['path'] );
	$uploads['url'] = str_replace( $subdir, '', $uploads['url'] );

	return $uploads;
}

/**
 * Helper used with the `upload_dir` filter to set https upload URL.
 */
function _upload_dir_https( $uploads ) {
	$uploads['url'] = str_replace( 'http://', 'https://', $uploads['url'] );
	$uploads['baseurl'] = str_replace( 'http://', 'https://', $uploads['baseurl'] );

	return $uploads;
}

/** 
 * These are pluggable functions that should have been loaded earlier 
 * We may not need them for in situ so wrapping in function_exists tests.
 */

// Skip `setcookie` calls in auth_cookie functions due to warning:
// Cannot modify header information - headers already sent by ...

if ( !function_exists( 'wp_set_auth_cookie' ) ) {

function wp_set_auth_cookie( $user_id, $remember = false, $secure = '', $token = '' ) {
	$auth_cookie = null;
	$expire = null;
	$expiration = null;
	$user_id = null;
	$scheme = null;
	/** This action is documented in wp-inclues/pluggable.php */
	do_action( 'set_auth_cookie', $auth_cookie, $expire, $expiration, $user_id, $scheme );
	$logged_in_cookie = null;
	/** This action is documented in wp-inclues/pluggable.php */
	do_action( 'set_logged_in_cookie', $logged_in_cookie, $expire, $expiration, $user_id, 'logged_in' );
}
}

if ( !function_exists( 'wp_clear_auth_cookie' ) ) {

function wp_clear_auth_cookie() {
	/** This action is documented in wp-inclues/pluggable.php */
	do_action( 'clear_auth_cookie' );
}
}
