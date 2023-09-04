<?php // (C) Copyright Bobbing Wide 2017

/**
 * Test the logic in the Git class
 */
 
class test_class_git extends BW_UnitTestCase {

	function setUp() : void {
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
	 * No longer expects output: git ls-files
	 * This is returned in $result.
	 *
	 *
	 */ 
	function test_command() {
		$git = git();
		$remote_output = '';
		$remote_output .= 'git remote -v';
		$remote_output .= "\r\n";
		$remote_output .= "\r\n";
		$remote_output .= 'git remote -v';
		$remote_output .= "\r\n";
		$remote_output .= "\r\n";
		$remote_output .= 'origin\t\github\bobbingwide\oik-batch (fetch)';
		$remote_output .= "\n";
		$remote_output .= 'origin\t\github\bobbingwide\oik-batch (push)';
		$remote_output .= "\n";
		$remote_output .= 'Result:origin\t\github\bobbingwide\oik-batch (fetch)';
		$remote_output .= "\n";
		$remote_output .= 'origin\t\github\bobbingwide\oik-batch (push)';
		$remote_output .= "\n";
		$remote_output .= 'Stderr:';

		$remote_output = str_replace( '\t', "\t", $remote_output );

		$this->expectOutputString( $remote_output );
		$result = $git->command( "remote -v" );

		$actual = $this->getActualOutput();
		bw_trace2( $actual, "actual output", false );
		$actual = str_replace( "\t", " ", $actual );
		//echo $actual;
		$this->assertStringContainsString( "origin \github\bobbingwide\oik-batch (fetch)", $result );
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
