<?php // (C) Copyright Bobbing Wide 2017

/**
 * Test the logic in the Git class
 */
 
class test_class_git extends BW_UnitTestCase {

	function setUp() {
		//$git_loaded = oik_require_lib( "git" );
		oik_require( "libs/oik-git.php", "oik-batch" );
	}
	
	function test_git() {
		$git = git();
		$this->assertInstanceOf( 'Git', $git );
	}
	
	function test_php_sapi_constant_matches_name() {
		$cli = PHP_SAPI;
		$php_sapi_name = php_sapi_name();
		$this->assertEquals( $php_sapi_name, $cli );
	}
	
	/**
	 * Something caused PHP_SAPI to be changed
	 * 
	 * But I don't know what it was
	 * So here we try a few things.
	 */
	
	function test_php_sapi_doesnt_change() {
		$this->test_php_sapi_constant_matches_name();
		$this->test_command();
		$this->test_php_sapi_constant_matches_name();
	}
	
	/**
	 * Expects output: git ls-files
	 *
	 */ 
	function test_command() {
		$git = git();
		$this->expectOutputString( "git ls-files " . PHP_EOL );
		$result = $git->command( "list" );
		$this->assertContains( "tests/test-class-git.php", $result );
		$actual = $this->getActualOutput();
		bw_trace2( $actual, "actual output", false );
		//echo $actual;
	}
	
	/**
	 * How do we check for notices? 
	 * 
	 * This is what we expect to get
	 * 
	 * Notice: Constant PHP_SAPI already defined in 
	 * C:\apache\htdocs\wordpress\wp-content\plugins\oik-batch\tests\test-class-git.php on line 49
	 */
	function test_cant_change_php_sapi_constant() {
		@define( 'PHP_SAPI', "omga" );
		$this->test_php_sapi_constant_matches_name();
	}
	

		
}
