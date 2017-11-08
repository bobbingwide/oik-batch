<?php // (C) Copyright Bobbing Wide 2016

/**
 * PHPUnit bootstrap for oik-batch 
 
 * For running WordPress plugin and/or theme unit tests under PHPUnit.
 
 * Normally, the bootstrap file is invoked by phpunit, 
 * loading phpunit.xml in preference to phpunit.xml.dist,
 * and finding the bootstrap parameter in the main tag
 * `<phpunit bootstrap="tests/bootstrap.php" backupGlobals="false" colors="true">`
 *
 * With oik-batch the bootstrap is different. 
 * Instead of loading tests/bootstrap.php we load ../oik-batch/oik-wp.php 
 * using `phpunit --bootstrap=../oik-batch/oik-wp.php`
 * or `<phpunit bootstrap="../oik-batch/oik-wp.php" >`
 * 
 * oik-wp will instantiate WordPress, with all the active plugins and themes,
 * and without resetting the database.
 *
 *
 * The bootstrap file for wordpress-develop-tests does a lot of things including resetting the database.
 * But most importantly it loads the PHP classes that we need to develop our tests ( WP_UnitTestCase )
 * 
 * We would like to try running tests on any database without destroying the original contents.
 * In this code we're trying to answer the questions...
 * 
 * - Is this possible?
 * - How easy is this to do?
 * - What problems does this introduce with regards to using existing test data?
 * - i.e. Is this a silly thing to do?
 * - Can we run phpunit under WordPress or must phpunit be in charge?
 * 
 
 * @TODO Rather than resetting the database to a defined checkpoint ( original idea August 2016 )
 * we need to ensure that WP_UnitTestCase does not do anything silly with the database.
 * We do this by implementing our own instance of WP_UnitTestCase.
 * 
 * 
 */
bootstrap_loaded(); 


 
 
/** 
 * Function to invoke when loaded 
 *
 
 Having looked at a number of unit test routines for WordPress plugins there appear to be a couple of approaches
 of getting their code loaded into the test suite.
 
 # Using mu_plugins loaded
 
 
 1. Load the wordpress-develop-tests functions.php
 2. This provides tests_add_filter() which enables them to register code to run in response to "muplugins_loaded"
 3. Load the wordpress-develop-tests boostrap.php
 
 # Using global $wp_tests_options	
 
 1. Adds your plugin to the array of 'active_plugins' in $GLOBALS['wp_tests_options']
 2. Load the wordpress-develop-tests bootstrap.php
 
 This second method appears to have been deprecated, but I don't know when.
 
 
 # Summary of methods used by different plugins
  
 plugin           | method used						 | env var 						| plugin's tests dir		  | multisite?
 ---------        | ------------------		 | -------						| --------		  | --------- 
 shortcake        | muplugins_loaded			 | WP_TESTS_DIR				| php-tests		  | ?
 jetpack          | muplugins_loaded			 | WP_DEVELOP_DIR     | tests/php		  | php.multisite.xml
 WP-cli generated | muplugins_loaded			 | WP_TESTS_DIR       | tests         | ?
 CMB2             | muplugins_loaded      | WP_TESTS_DIR       | tests         | ?
 BuddyPress				| muplugins_loaded       | WP_TESTS_DIR       | tests/phpunit | ?
 blobaugh/wordpress-plugin-tests | wp_tests_options | 
 bbPress          | both?                  | WP_TESTS_DIR     	| tests/phpunit | multisite.xml
 
 Note: WP-cli's PHPUnit testing is different
 
 # Preliminary thoughts on generalizing the solution
 
 - Most unit test routines hard code the name of the plugin file to load
 - But we can probably do better than that. 
 - If we know the name of our bootstrap file then we can determine the plugin name from that; assumption is that the bootstrap file is in wp-content/plugins/$plugin/tests/bootstrap.php
 - Another way of invoking the tests would be to respond to an action hook invoked by wordpress-develop-tests
 
 # Requirements for in situ testing
 
 All of the above discusses how plugins cater for running the tests in a vanilla environment.
 But we want to run our tests in situ, which means our plugin is expected to be loaded already.
 
 - Therefore we don't need to manually load the plugin.
 - And we certainly don't want to actually run the bootstrap.
 - You might wonder why we looked for it in the first place.
 - So all we need to do is locate and load the files we need
 
 
*/
function bootstrap_loaded() {
	$wordpress_develop_tests = locate_wordpress_develop_tests();
	if ( $wordpress_develop_tests ) {
		load_bootstrap_functions( $wordpress_develop_tests );
		//tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );
		//continue_loading_bootstrap( $wordpress_develop_tests );
	} else {
		echo "What shall we do now then?" . PHP_EOL;
		die( "Tests cannot be run without the WordPress develop test suite." );
	}
}

/**
 * Self load the plugin's main .php file
 * 
 * @TODO Load the plugin's main .php file
 * 
 * At this point Must Use plugins have been loaded.
 *
 * We don't expect other plugins to be loaded, since there are none defined in active_plugins array
 * so we do it ourselves. 
 * 
 */
function _manually_load_plugin() {
	$active_plugins = (array) get_option( 'active_plugins', array() );
	bw_trace2( $active_plugins, "active_plugins", true );
	bw_backtrace();
	
	$included_files = get_included_files();
	bw_trace2( $included_files, "included_files", false );
	oik_require( "oik.php" );
	echo "oik loaded" . PHP_EOL;
	
	// Logic from WP-cli generated bootstrap
	//  require dirname( dirname( __FILE__ ) ) . '/my-plugin.php';
	
	//require_once( "../../
}

/** 
 * Locate the wordpress-develop-tests code
 * 
 * We're trying to find the path to the wordpress-develop-tests plugin
 * or the "tests\phpunit" directory within an extract from SVN or git.
 *
 * Use these methods to locate the code:
 * 
 * 1. wordpress-develop-tests installed as a plugin.
 * 2. `WP_DEVELOP_DIR` environment variable - used by Jetpack
 * 3. `WP_TESTS_DIR` environment variable - used by CMB2
 * 4. Plugin installed inside of WordPress.org developer checkout
 * 5. Tests checked out to /tmp
 * 
 * If you have a normal WordPress install then the easiest way
 * is to use the plugin approach. 
	 `
   C:\svn
			\wordpress-develop
				\src
					\wp-content
						\plugins
							\oik-batch - oik-wp.php
								\tests - bootstrap.php	
							\$plugin
								\tests - test-*.php
								
							\wordpress-develop-tests
								same directory structure as \tests below
				\tests
					\phpunit
						\build
						\data
						\includes - functions.php and other required files
						\tests
	`
 *	
 * 
 * BTW: Apologies for using Windows backslashes
 * 
 * @return string|null the directory root for the tests files
 */
function locate_wordpress_develop_tests() {
	$test_root = locate_wordpress_develop_tests_plugin();
	if ( $test_root ) {
		echo "Using wordpress-develop-tests plugin" . PHP_EOL;
	} elseif ( false !== getenv( 'WP_DEVELOP_DIR' ) ) {
		$test_root = getenv( 'WP_DEVELOP_DIR' );
	} elseif ( false !== getenv( 'WP_TESTS_DIR' ) ) {
		$test_root = getenv( 'WP_TESTS_DIR' );
	} elseif ( file_exists( '../../../../tests/phpunit/includes/bootstrap.php' ) ) {
		$test_root = '../../../../tests/phpunit';
	} elseif ( file_exists( '/tmp/wordpress-tests-lib/includes/bootstrap.php' ) ) {
		$test_root = '/tmp/wordpress-tests-lib'; 
	} else {
		echo "WordPress develop tests bootstrap.php not found" . PHP_EOL;
	}
	return( $test_root );
}

/**
 * Locate oik-batch 
 *
 * @TODO - remove when determined unnecessary
 
 * 
 * If we're running phpunit from a symlinked directory then there's a good chance that neither the current directory nor the __FILE__ 
 * will tell us where to look for oik-batch.
 * 
 * In this case the phpunit plugin is actually in C:\apache\htdocs\phpLibraries
 * but we think it's symlinked in C:\apache\htdocs\wordpress\wp-content\plugins\phpunit
 * and we think we're running from C:\apache\htdocs\src\wp-content\plugins\phpunit
 * so we may need a constant such as OIK_BATCH_DIR in the phpunit.xml file
 
 * We need to separate the phpunit executable from the phpunit plugin!
 * which leads me to think that oik-batch is the right place for the code.
 * What does WP-cli do?
 */
function locate_oik_batch() {
	echo getcwd() . PHP_EOL;
	echo __FILE__ . PHP_EOL; 
	$oik_batch = '../oik-batch/oik-wp.php';
	if ( !file_exists( $oik_batch ) ) { 
		$oik_batch = null; 
		
		$included_files = get_included_files();
		print_r( $included_files );
	} 
	return( $oik_batch );
}

/**
 * Load the WordPress develop tests bootstrap.php file
 *
 * Loading bootstrap.php from wordpress-develop-tests might not be what we wanted to do.
 * It reloads the database from scratch. 
 * 
 * If however we load oik-wp.php then we can continue to process WordPress normally.
 * Our tests might make changes to the database but that's expected to be less of a problem
 * in the short term.
 * 
 * @param string $wordpress_develop_dir
 * @param string $oik_batch_dir
 */
function continue_loading_bootstrap( $wordpress_develop_dir ) {
	//if ( $oik_batch = locate_oik_batch() ) {
	//	require_once( $oik_batch ); 
	//} else {
	//		echo "Not loading oik-batch" . PHP_EOL;
	//}
	//	require $wordpress_develop_dir . '/includes/bootstrap.php';
	
}

/**
 * Load the WordPress develop tests functions.php file or our oik-batch replacements for in situ 
 *
 * File | Loaded from | Notes
 * ---- | ------------ | -----------------
 * phpunit6-compat.php | WP | Required for PHPUnit 6 class aliases
 * factory.php | WP | this loads a load of factory classes
 * class-basic-object.php | - | 
 * class-basic-subclass.php | - | 
 * functions.php | oik-batch | a limited subset
 * trac.php | - | Not required for in situ testing of plugins & themes
 * testcase.php | oik-batch | WP_UnitTestCase overridden for in situ testing
 * test-bw-unittestcase.php | oik-batch | BW_UnitTestCase extends WP_UnitTestCase for any extra methods we think'll come in handy
 * etc | WP | Not sure about the rest!  
 * 
 * @param string $wordpress_develop_dir - the location of the WordPress PHPUnit test case code
 */
function load_bootstrap_functions( $wordpress_develop_dir ) {
	if ( $wordpress_develop_dir ) {
	
		/**
		 * Compatibility with PHPUnit 6+
		*/
		if ( class_exists( 'PHPUnit\Runner\Version' ) ) {
			require_once $wordpress_develop_dir . '/includes/phpunit6-compat.php';
		}
	

		if ( ! defined( 'WP_TESTS_FORCE_KNOWN_BUGS' ) ) {
			define( 'WP_TESTS_FORCE_KNOWN_BUGS', false );
		}
		
		if ( !defined( 'WP_TESTS_DOMAIN' ) ) {
			define( 'WP_TESTS_DOMAIN', $_SERVER['HTTP_HOST'] );
    }
		//require $wordpress_develop_dir . '/includes/functions.php';
		oik_require( "tests/functions.php", "oik-batch" );
		//require_once dirname( __FILE__ ) . '/trac.php';
    //require $wordpress_develop_dir . '/includes/testcase.php';
		require $wordpress_develop_dir . '/includes/factory.php'; 
		oik_require( "tests/testcase.php", "oik-batch" );
		oik_require( "tests/class-bw-unittestcase.php", "oik-batch" );
		require $wordpress_develop_dir . '/includes/testcase-rest-api.php';
		require $wordpress_develop_dir . '/includes/testcase-xmlrpc.php';
		require $wordpress_develop_dir . '/includes/testcase-ajax.php';
		require $wordpress_develop_dir . '/includes/testcase-canonical.php';
		require $wordpress_develop_dir . '/includes/exceptions.php';
		require $wordpress_develop_dir . '/includes/utils.php';
		require $wordpress_develop_dir . '/includes/spy-rest-server.php';
	} else {
		echo "No WordPress develop test files loaded" . PHP_EOL;
	}
}

/**
 * Locate the wordpress-develop-tests plugin
 * 
 * 
 */
function locate_wordpress_develop_tests_plugin() {
	$tests_dir = null;
	$file = oik_path( "phpunit/includes/functions.php", "wordpress-develop-tests" );
	if ( file_exists( $file ) ) {
		check_wordpress_develop_tests_version();
		$tests_dir = oik_path( "phpunit", "wordpress-develop-tests" );
	}
	return( $tests_dir );
}

/**
 * Check WordPress develop tests are compatible with WordPress installation
 *
 * Assumes that newer versions of the WordPress develop tests could be incompatible with older versions of WordPress core.
 */
function check_wordpress_develop_tests_version() {
	require_once( ABSPATH . "wp-admin/includes/plugin.php" );
	$plugin_file = oik_path( "wordpress-develop-tests.php", "wordpress-develop-tests" );
	$plugin_data = get_plugin_data( $plugin_file, false, false );
	$wordpress_develop_tests_version = bw_array_get( $plugin_data, 'Version', null );
	global $wp_version;
	if ( version_compare( $wp_version, $wordpress_develop_tests_version, "lt" ) ) {
		echo "Warning: potentially incompatible versions: WordPress: $wp_version, wordpress-develop-tests: $wordpress_develop_tests_version" . PHP_EOL;
	}
}

