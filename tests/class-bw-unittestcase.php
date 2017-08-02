<?php // (C) Copyright Bobbing Wide 2016,2017

/**
 * BW_UnitTestCase is for In situ testing of WordPress plugins and themes
 * 
 * It extends WP_UnitTestCase and prevents you from doing something silly like deleting your content.
 * This is belt and braces since we've overridden WP_UnitTestCase to do this anyway.
 * 
 * For 'In situ' testing we do not reset the state of the WordPress installation before and after every test.
 * We just make sure nothing gets added or taken away.
 *
 * The WP_UnitTestCase class includes utility functions and assertions useful for testing WordPress.
 *
 * 'In situ' unit tests for WordPress plugins and themes should either inherit from this class or the overridden WP_UnitTestCase
 * delivered by oik-batch.
 * 
 * Any test that might cause a database change and that which implements the setUp() method
 * must also call call parent::setUp() to ensure that the transactions are rolled back at the end.
 * 
 * 
 * {@see https://dev.mysql.com/doc/refman/5.7/en/commit.html}
 */

class BW_UnitTestCase extends WP_UnitTestCase {

	public static function setUpBeforeClass() {
		bw_trace2();
		self::rollback_transaction();
		// It's not the setUpBeforeClass that needs to do this but the setUp(), which does
		//self::my_start_transaction();
		//self::set_autocommit_0();
	}
	
	public static function tearDownAfterClass() {
		bw_trace2();
		self::rollback_transaction();
	} 
	
	public static function rollback_transaction() {
		global $wpdb;
		$wpdb->query( 'ROLLBACK' );
		bw_trace2( $wpdb, "wpdb" );
	}
	
	/**
	 * Commits the transaction
	 * 
	 * We don't expect any calls to this method so we trace it and then go bang.
	 * @TODO One day this will be implemented properly.
	 */
	public static function commit_transaction() {
		bw_trace2();
		gob();
	}
	
	public static function set_autocommit_0() {
		global $wpdb;
		$wpdb->query( 'SET AUTOCOMMIT=0' );
		bw_trace2( $wpdb, "wpdb" );
	}
	
	public static function my_start_transaction() {
		global $wpdb;
		$wpdb->query( 'START TRANSACTION' );
		bw_trace2( $wpdb, "wpdb" );
	}
	
	/**
	 * Replaces the admin_url in $expected
	 * 
	 * Note: assumes https protocol
	 * @param string $expected
	 * @return string updated string
	 */
	function replace_admin_url( $expected ) {
		$expected = str_replace( admin_url(), "https://qw/src/wp-admin/", $expected );
		return $expected;
	}
	
	/**
	 * Replaces the generated oik_url in $html
	 *
	 * Note: assumes https protocol
	 *
	 * @param string $html
	 * @return string modified $html
	 */
	function replace_oik_url( $html ) {
		$html = str_replace( oik_url(), "https://qw/src/wp-content/plugins/oik/", $html );
		return $html;
	}
	
	/**
	 * Returns array of HTML tags
	 * 
	 * Breaks the HTML into lines at tag interfaces and converts into an array.
	 *
	 * New line characters are inserted between adjacent '>' and '<' characters.
	 *
	 * @param string $html
	 * @return array $html_array
	 */
	function tag_break( $html ) {
		$new_lined = str_replace( "><", ">\n<", $html);
		$html_array = explode( "\n", $new_lined );
		return $html_array;
	}
	
	/**
	 * Helps to generate the expected array from actual test output
	 *
	 * This function should not be invoked in a completed test case.
	 * Echoing this output ensures we get a message from PHPUnit; so does the assertion.
	 * 
	 * @param array $html_array
	 * 
	 */
	function generate_expected( $html_array ) {
		echo PHP_EOL;
		echo '$expected = array();';
		foreach ( $html_array as $line ) {
			echo PHP_EOL;
			$line = str_replace( "'", "\'", $line );
			echo '$expected[] = \'' . $line . "';";
		}
		echo PHP_EOL;
		$this->assertFalse( true );
	}
	
	/**
	 * Replaces generated nonce value with a generic value
	 * 
	 * Expects there to be at least one nonce in the input
	 * Note: If there is no matching nonce in the output this method will cause the test to fail.
	 * 
	 * @param array $expected_array generated HTML
	 * @param string $id nonce ID to search for
	 * @param string $name nonce name to search for 
	 * @return array modified HTML where the generated value is now 'nonsense'
	 */
	function replace_nonce_with_nonsense( $expected_array, $id="_wpnonce", $name="_wpnonce" ) {
		$found = false;
		$needle = '<input type="hidden" id="'. $id . '" name="' . $name . '" value="';
		foreach ( $expected_array as $index => $line ) {
			$pos = strpos( $line, $needle );
			if ( false !== $pos ) {
				$expected_array[ $index ] = $needle. 'nonsense" />';
				$found = true;
			}
		}
		$this->assertTrue( $found, "No nonce id=$id name=$name found in expected array" );
		return $expected_array;
	}
	
	/**
	 * Replaces an unknown string between two strings with a known value
	 *
	 * @param string $string - input string which may contain $before and $after
	 * @param string $before - substring of the before part
	 * @param string $after - substring of the after part
	 * @param string $between - replacement section
	 * @return null|string updated string or null
	 */
	function replace_between( $string, $before, $after, $between ) {
		$replace = null;
		$spos = strpos( $string, $before );
		if ( $spos  ) {
			$spos += strlen( $before );
			$epos = strpos( $string, $after );
			if ( $epos > $spos ) { 
				$left = substr( $string, 0, $spos );
				$right = substr( $string, $epos );
				$replace = $left . $between . $right ;
			}
		}
		return $replace;
	}

	/**
	 * Replaces antispambot emails with a known value
	 * 
	 * Note: This function could fail if there is no mailto: in the output
	 */
	function replace_antispambot( $expected_array ) {
		$found = false;
		foreach ( $expected_array as $index => $line ) {
			$replace = $this->replace_between( $expected_array[ $index ], 'href="mailto:', '" title="', 'email@example.com' );
			if ( $replace ) {
				$expected_array[ $index ] = $replace;
				$found = true;
			}
		}
		$this->assertTrue( $found, "No mailto: found in expected array" );
		return $expected_array;
	}
	
	
	/**
	 * Asserts that the HTML array equals the file
	 */
	function assertArrayEqualsFile( $string, $file=null ) {
		$html_array = $this->prepareExpectedArray( $string );
		$expected_file = $this->prepareFile( $file );
		$expected = file( $expected_file, FILE_IGNORE_NEW_LINES );
		$this->assertEquals( $expected, $html_array );	
	}
	
	/**
	 * Converts to an array if required
	 */
	function prepareExpectedArray( $string ) {
		if ( is_scalar( $string ) ) {
			$html_array = $this->tag_break( $string );
		} else { 
			$html_array = $string;
		}
		return $html_array;
	}
	
	/**
	 * Returns the expected file name
	 * 
	 * Expected output files are stored in a directory tree
	 * 
	 * `tests/data/la_CY/test_name.html
	 * ` 
	 * where 
	 * - la_CY is the locale; default is `en_US`
	 * - test_name is the name of the test method
	 * 
	 * 
	 * @param string|null $file - 
	 * 
	 */
	function prepareFile( $file=null ) {
		if ( !$file ) {
			$file = $this->find_test_name();
		}
		$path_info = pathinfo( $file );
		if ( '.' == $path_info['dirname'] ) {
			$dirname = 'tests/data/';
			$dirname .= $this->query_la_CY();
			$path_info['dirname'] = $dirname;
		}
		if ( !isset( $path_info['extension'] ) ) {
			$path_info['extension'] = "html";
		}
		$expected_file = $path_info['dirname'];
		$expected_file .= "/";
		$expected_file .= $path_info['filename'];
		$expected_file .= ".";
		$expected_file .= $path_info['extension'];
		$this->assertFileExists( $expected_file );
		return $expected_file;
	}
	
	/**
	 * Finds the test name from the call stack
	 * 
	 * Assumes the test name starts with 'test_'
	 * 
	 * @param string $prefix
	 */
	function find_test_name( $prefix='test_') {
		$trace = debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS );
		$test_name = null;
		foreach ( $trace as $frame ) {
			if ( 0 === strpos( $frame['function'], $prefix ) ) {
				$test_name = $frame['function'];
				break;
			} 
		}
		$this->assertNotNull( $test_name );
		return $test_name;
	}
		
	
	/**
	 * When we're working in a different language e.g. bb_BB then
	 * we append the la_CY to the file name
	 */
	function assertArrayEqualsLanguageFile( $string, $file ) {
		
		
	}
	
	/**
	 * Queries the currently set locale
	 */
	function query_la_CY() {
		$locale = get_locale();
		$this->assertNotNull( $locale );
		return $locale;
	} 
	
	/**
	 * Helps to generate the expected file from actual test output
	 */
	function generate_expected_file( $html_array ) {
		$html_array = $this->prepareExpectedArray( $html_array );
		echo PHP_EOL;
		foreach ( $html_array as $line ) {
			echo $line;
			echo PHP_EOL;
		}
		$this->prepareFile();
		//$this->assertFalse( true );
	}
	

}
