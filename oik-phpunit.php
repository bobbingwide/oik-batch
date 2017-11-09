<?php // (C) Copyright Bobbing Wide 2017

/**
 * Description: in situ PHPUnit test invocation for WordPress MultiSite
 * 
 * Runs in situ PHPUnit tests using oik-batch and wordpress-develop-tests
 * 
 * Syntax:
 * 
 * php path-to-file\oik-phpunit.php PHP Unit parameters url=domain path=path
 * 
 * e.g. 
 * `
 * php C:\apache\htdocs\wordpress\wp-content\plugins\oik-batch\oik-phpunit.php "--verbose" "--disallow-test-output" "--stop-on-error" "--stop-on-failure" "--log-junit=phpunit.json" %*
 * `
 */
 
if ( PHP_SAPI !== "cli" ) { 
	die();
}

/**
 * Runs PHPUnit 
 * 
 * - Hides args that PHPUnit doesn't understand
 * - Invokes PHPUnit
 */
function oik_phpunit_loaded() {
	oik_phpunit_save_our_args();
	oik_phpunit_run_phpunit();
}

/**
 * Save our args.
 * 
 * It's not possible to pass parameters through PHPUnit to the bootstrap routine.
 * So we hide them from PHPUnit and re-instate them later on.
 * So far the only two parameters we need are for WordPress MultiSite.
 * 
 * Parameter | Purpose       | Example          | Notes
 * --------  | -------       | --------         | ---------
 * url       | WPMS domain   | url=qw           | 
 * path      | WPMS path     | path=wpms        |	Primary site -  https://qw/wpms
 * path      | WPMS subsite  | path=wpms/site-2 |	Child site - https:/qw/wpms/site-2
 * 
 * Note: Leading and trailing slashes will be added to the path parameter if not null.
 */
function oik_phpunit_save_our_args() {
	foreach ( $_SERVER['argv'] as $index => $arg ) {
		$kvp = explode( "=", $arg );
		if ( in_array( $kvp[0],  array( "url", "path" ) ) ) {
			$_SERVER['argv-saved'][] = $arg;
			unset( $_SERVER['argv'][$index] );
		}
	}
}	

/**
 * Determine the name of the PHPUNIT phar file to load
 * 
 * The calling routine is expected to set this in the environment. e.g.
 * `
 * set PHPUNIT=c:\apache\htdocs\phpLibraries\phpunit\phpunit-6.2.0.phar
 * `
 */
function oik_phpunit_run_phpunit() {
	$phpunit = getenv( "PHPUNIT" );
	echo $phpunit;
	include $phpunit;
	PHPUnit\TextUI\Command::main();
}

oik_phpunit_loaded(); 
