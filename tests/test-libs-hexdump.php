<?php
/**
 * Test the logic in the hexdump function
 *
 * @copyright C) Copyright Bobbing Wide 2019
 *
 * @TODO PHP_EOL may be different on Linux ( not both CR and LF ) so the hex dump output will be different.
 * Both these tests are expected to fail if not run on Windows.
 */

class test_class_libs_hexdump extends BW_UnitTestCase {

	function setUp() : void {
		oik_require( "libs/hexdump.php", "oik-batch" );
	}

	function test_oik_hexdump_php_eol() {
		$output = oik_hexdump( "PHP_EOL:" . PHP_EOL . "!" );
		$expected = '11' . PHP_EOL;
		$expected .= 'PHP_EOL:..!......... 50 48 50 5f 45 4f 4c 3a 0d 0a 21 ' . PHP_EOL;
		$this->assertEquals( $expected, $output );
	}

	function test_oik_hexdump() {
		$string = "blah di blah\n\rdi blah di blah\ndi blah di blah\r etcetera!";
		$output = oik_hexdump( $string );
		$expected = '56' . PHP_EOL;
		$expected .= 'blah di blah..di bla 62 6c 61 68 20 64 69 20 62 6c 61 68 0a 0d 64 69 20 62 6c 61 ' . PHP_EOL;
		$expected .= 'h di blah.di blah di 68 20 64 69 20 62 6c 61 68 0a 64 69 20 62 6c 61 68 20 64 69 ' . PHP_EOL;
		$expected .= ' blah. etcetera!.... 20 62 6c 61 68 0d 20 65 74 63 65 74 65 72 61 21 ' . PHP_EOL;
		$this->assertEquals( $expected, $output );
	}

	/**
	 * What should oik_hexdump do when passed an integer such as __LINE__ ?
	 */

	function test_oik_hexdump_line() {
		$string = __LINE__;
		echo $string;
		$output = oik_hexdump( $string );
		echo $output;
		$expected = '35' . PHP_EOL;
		$this->assertEquals( $expected, $output );

	}



}

