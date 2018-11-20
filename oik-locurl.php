<?php // (C) Copyright Bobbing Wide 2016-2018

/**
 * Syntax: oikwp oik-locurl.php locurl url=domain path=path
 * 
 * when run from the oik-batch folder
 *
 * Report the current URL or set the local URL to the locurl
 * 
 * For WPMS
 * - subdomain install pass the url= parameter
 * - subdirectory install we need to set url= and path= 

 *
 */
 
oik_locurl_loaded(); 

 
/**
 * Function to invoke when oik-locurl is loaded
 * 
 */
function oik_locurl_loaded() {
	$locurl = oik_batch_query_value_from_argv( 1, null );
	echo $locurl . PHP_EOL;
	
	if ( !$locurl ) {
		//echo "Please specify the local URL for the site" . PHP_EOL;
		//echo "Syntax: oikwp oik-locurl.php locurl" . PHP_EOL;
		oik_locurl_report_options();
	} else {
		oik_locurl_update_options( $locurl );
	}
	
	if ( is_multisite() ) {
		oik_locurl_multisite( $locurl );
	} else {
		echo "Site is not MultiSite" . PHP_EOL;
	}
}

function oik_locurl_report_options() {
	$siteurl = get_option( "siteurl" );
	$home = get_option( "home" );
	echo "Option: siteurl is $siteurl" . PHP_EOL;
	echo "Option: home is $home" . PHP_EOL;
}

/**
 * Update the siteurl and home options
 * 
 * @param string $locurl the fully specified local url e.g. http://wp.a2z or http://www.wp-a2z.org
 */
function oik_locurl_update_options( $locurl ) {
	oik_locurl_update_option( "siteurl", $locurl );
	oik_locurl_update_option( "home", $locurl );
}

/**
 * Update an option 
 * 
 * Here we're ponly working with simple, single value string options
 * 
 * @param string $option
 * @param string $value
 */											 
function oik_locurl_update_option( $option, $value ) {
	$current = get_option( $option );
	if ( $current != $value ) {
		update_option( $option, $value );
		echo "Option: $option set to $value was $current" . PHP_EOL;
	} else {
		echo "Option: $option already set to $value" . PHP_EOL;
	}
}

/**
 * Set the local URLs for all the blogs in the WPMS network
 * 
 * @TODO Complete this code
 * 
 * update_blog_details( $id, $data ) 
 * 
 * We need to obtain the domain and 
 
 * Sample entry in wp_blogs table and the site's wp_options table
 * 
 * site_id | domain | path         | wp_n_options siteurl
 * ------- | ------ | ------       | ---------------------
 * 1       | qw     | /wpms/       | http://qw/wpms
 * 2       | qw     | /wpms/site2/ | http://qw/wpms/site-2
 * 
 * 
 * @param string $locurl
 */
function oik_locurl_multisite( $locurl ) {
	echo "@TODO WPMS update. Meanwhile please use wp-admin/network/site-info.php" . PHP_EOL;
	gob();
}
	
	
