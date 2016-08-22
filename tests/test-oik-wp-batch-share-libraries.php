<?php // (C) Copyright Bobbing Wide 2016
/**
 * @group oik-batch
 */
class Tests_oik_wp_batch_share_libraries extends WP_UnitTestCase {

	/**
	 * Test that oik-batch and oik-wp can peacefully coexist,
	 *
	 * They share functionality from the oik-cli shared library.
	 *
	 * Note: When oik-batch is run under oik-wp it echos some strings.
	 * So we need to tell PHPUnit what we know.
	 */
	function test_loading_oik_batch() {
		oik_require( "oik-batch.php", "oik-batch" );
		//$this->assertTrue( true );
		$this->expectOutputString( "cli" . PHP_EOL . "End cli:oik_batch_loaded" . PHP_EOL );
	}

}

