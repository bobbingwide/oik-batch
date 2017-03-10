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
	

}
