<?php // (C) Copyright Bobbing Wide 2016
/**
 * @group WordPress
 */
class Tests_WordPress extends WP_UnitTestCase {


	function setUp() : void {
		// parent::setUp();
	}

	function tearDown() : void {
		// parent::tearDown();
	}
	
	/**
	 * Test that WordPress functions are available
	 * 
	 */
	function test_get_post_meta_function_exists() {
		$this->assertTrue( function_exists( "get_post_meta" ) );
	}
	
	/**
	 * Test that factory methods are available
	 * 
	 * Try performing a post create and confirm the $id is not 0. 
	 * Then we need to delete it. 
	 */
	function test_factory_methods_available() {
		$id = self::factory()->post->create();
		$this->assertGreaterThan( 0, $id );
		wp_delete_post( $id, true );
		
		
	}
	


}

