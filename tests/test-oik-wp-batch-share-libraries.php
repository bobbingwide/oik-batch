<?php // (C) Copyright Bobbing Wide 2016
/**
 * @group oik-batch
 */
class Tests_oik_wp_batch_share_libraries extends BW_UnitTestCase {

	/**
	 * Test that oik-batch and oik-wp can peacefully coexist,
	 *
	 * They share functionality from the oik-cli shared library.
	 *
	 * Note: When oik-batch is run under oik-wp it echos some strings.
	 * So we need to tell PHPUnit what we know.
	 * 
	 * If oik-batch and oik-wp are both activated then the messages are displayed earlier
	 * we need to cater for this by detecting if oik-batch.php has already been loaded.
	 * We check if oik_batch_loaded() exists
	 */
	function test_loading_oik_batch() {
		if ( function_exists( "oik_batch_loaded" ) ) {
			$this->assertTrue( true );
		} else {
			oik_require( "oik-batch.php", "oik-batch" );
			$this->expectOutputString( "cli" . PHP_EOL . "End cli:oik_batch_loaded" . PHP_EOL );
		}	
	}

}

