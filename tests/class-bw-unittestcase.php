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
 * must also call parent::setUp() to ensure that the transactions are rolled back at the end.
 * 
 * 
 * {@see https://dev.mysql.com/doc/refman/5.7/en/commit.html}
 */

class BW_UnitTestCase extends WP_UnitTestCase {

	public static function setUpBeforeClass() : void {
		bw_trace2();
		self::rollback_transaction();
		// It's not the setUpBeforeClass that needs to do this but the setUp(), which does
		//self::my_start_transaction();
		//self::set_autocommit_0();
	}
	
	public static function tearDownAfterClass() : void {
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
     * For those rare occasions where we need to commit some changes in order to be able to test something else.
     * This is required if we're going to do a wp_remote_get() to fetch something we've created within the test.
     *
     * In this case we should run cleanup routines before and after each test.
	 */
	public static function commit_transaction() {
		global $wpdb;
		$wpdb->query( 'COMMIT' );
		bw_trace2( $wpdb, "wpdb" );
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
	 * For WPMS we need to support network_admin_url(). This has to be done in a separate method.
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
	 * Replaces the created nonce with nonsense
	 *
	 * @param string $html the HTML string
	 * @param string $action the action that was passed to wp_create_nonce()
	 * @param string $id the ID that was passed to wp_create_nonce()
	 * @return string updated HTML
	 */ 
	function replace_created_nonce( $html, $action, $id='_wpnonce' ) {
		$created_nonce = $id . '=' . wp_create_nonce( $action );
		$pos = strpos( $html, $created_nonce );
		$this->assertNotFalse( $pos );
		$html = str_replace( $created_nonce, $id . "=nonsense", $html );
		return $html;
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
	
	
	/**
	 * Switch to the required target language
	 * 
	 * - WordPress core's switch_to_locale() function leaves much to be desired when the default language is en_US
	 * - and/or when the translations are loaded from the plugin's language folders rather than WP_LANG_DIR
	 * - We have to (re)load the language files ourselves.
	 * 
	 * @TODO We also need to remember to pass the slug/domain to translate() :-)
	 *
	 * Note: For switch_to_locale() see https://core.trac.wordpress.org/ticket/26511 and https://core.trac.wordpress.org/ticket/39210 
	 */
	function switch_to_locale( $locale='bb_BB' ) {
		//$tdl = is_textdomain_loaded( "oik" );
		//$this->assertTrue( $tdl );
		$switched = switch_to_locale( $locale );
		if ( $switched ) {
			$this->assertTrue( $switched );
		}
		$new_locale = $this->query_la_CY();
		$this->assertEquals( $locale, $new_locale );
		$this->reload_domains();
		$tdl = is_textdomain_loaded( "oik" );
		$this->assertTrue( $tdl );
		//$this->test_domains_loaded();
		if ( $locale === 'bb_BB' ) {
			$bw = translate( "bobbingwide", "oik" );
			$this->assertEquals( "bboibgniwde", $bw );
			$bw = translate( "bobbingwide", null );
			$this->assertEquals( "bboibgniwde", $bw );
		}	
			
	}
	
	/**
	 * Reloads the text domains
	 * 
	 * - Loading the 'oik-libs' text domain from the oik-libs plugin invalidates tests where the plugin is delivered from WordPress.org so oik-libs won't exist.
	 * - but we do need to reload oik's text domain 
	 * - and cause the null domain to be rebuilt.
	 */
	function reload_domains() {
		$domains = array( "oik" );
		foreach ( $domains as $domain ) {
			$loaded = bw_load_plugin_textdomain( $domain );
			$this->assertTrue( $loaded, "$domain not loaded" );
		}
		oik_require_lib( "oik-l10n" );
		oik_l10n_enable_jti();
	}
	
	/**
	 * Reduce a print_r'ed string
	 *
	 * print_r's an array then removes unwanted white space
	 *
	 * @TODO - remove blank lines
	 * 
	 * @param array $array
	 * @return string a reduced string
	 */
	function arraytohtml( $array ) {
		$string = print_r( $array, true );
		$again = explode( "\n", $string );
		$again = array_map( "trim", $again );
		$string = implode( "\n", $again );
		return $string;
	}
	
	/**
	 * Replaces home_url and site_url
	 *
	 * Should we consider using https://example.com ?
	 * @param string $expected
	 * @return string with site_url and home_url replaced by hard coded values
	 */
	function replace_home_url( $expected ) {
		$upload_dir = wp_upload_dir();
		$expected = str_replace( $upload_dir['baseurl'] , "https://qw/src/wp-content/uploads", $expected );
		$expected = str_replace( home_url(), "https://qw/src", $expected );
		$expected = str_replace( site_url(), "https://qw/src", $expected );
		return $expected;
	}
	
	/**
	 * Replaces parameter $post->ID with 42 in selected parameter
	 * 
	 * @param string $expected
	 * @param object $post a post object
	 * @param string $prefix the prefix to find
	 * @return string updated HTML
	 */
	function replace_post_id( $expected, $post, $prefix="post=" ) {
		$expected = str_replace( $prefix . $post->ID, $prefix . "42", $expected );
		return $expected;
	}


	

}
