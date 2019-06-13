<?php
/**
 * Test the behaviour of home_url and site_url in batch
 *
 * @copyright C) Copyright Bobbing Wide 2019
 *
 *  */

class test_home_url_site_url extends BW_UnitTestCase {

	function setUp() {

	}

	/**
	 * Prior to the addition of oik_batch_maybe_set_scheme_from_siteurl()
	 * the calls to get_home_url() and get_site_url() produced different results under oikwp.
	 * get_site_url would return http even when the siteurl option used https.
	 * get_home_url was not altered.
	 * This tests that they now produce the same results.
	 * In other tests we assume that siteurl and home use the https scheme so URLs will be prefixed https.
	 */
	function test_home_url_equals_site_url() {
		$home_url = get_home_url();
		$site_url = get_site_url();
		$this->assertEquals( $home_url, $site_url );
	}





}

