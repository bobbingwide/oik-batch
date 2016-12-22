<?php // (C) Copyright Bobbing Wide 2016

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
 */

class BW_UnitTestCase extends WP_UnitTestCase {

	public static function setUpBeforeClass() {
		bw_trace2();
		self::rollback_transaction();
	}
	
	public static function tearDownAfterClass() {
		bw_trace2();
		self::rollback_transaction();
	} 
	
	public static function rollback_transaction() {
		global $wpdb;
		$wpdb->query( 'ROLLBACK;' );
		bw_trace2();
	}
	
	public static function commit_transaction() {
		bw_trace2();
		gob();
	}

}
