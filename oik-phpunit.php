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
 * Runs the required version of PHPUnit.
 *
 * Determine the name of the PHPUNIT phar file to load.
 * 
 * The calling routine is expected to set this in the environment. e.g.
 * `
 * set PHPUNIT=c:\apache\htdocs\phpLibraries\phpunit\phpunit-6.2.0.phar
 * `
 *
 * Note: Without the `echo PHP_EOL;` we get a whole load of unexpected carriage returns appearing in the PHPUnit output.
 * This is especially annoying if you are redirecting the output to stdout because you intend to copy/paste the output into a tests\data file.
 * Fortunately this extra line resolves the problem. 
 */
function oik_phpunit_run_phpunit() {
	$phpunit = getenv( "PHPUNIT" );
	echo $phpunit;
	echo PHP_EOL;
	include $phpunit;
	/**
	 * How we invoke the PHPUnit code depends on the level of PHPUnit being run.
	 */
	if ( str_contains( $phpunit, 'unit-9.' )) {
		PHPUnit\TextUI\Command::main();
	}
	if ( str_contains( $phpunit, 'unit-10.' ) ) {
		$application = new PHPUnit\TextUI\Application;
		print_r( $_SERVER['argv']);
		$application->run( $_SERVER['argv'] );
	}
	if ( str_contains( $phpunit, 'unit-11.') ) {
		$application=new PHPUnit\TextUI\Application;
		print_r( $_SERVER['argv'] );
		$application->run( $_SERVER['argv'] );
	}
	echo "I don't think we'll get here" . PHP_EOL;
	echo "oik-phpunit ended" . PHP_EOL;
	
}

oik_phpunit_loaded(); 
