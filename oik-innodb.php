<?php // (C) Copyright Bobbing Wide 2016,2017

if ( PHP_SAPI !== "cli" ) { 
	die();
}

oik_innodb_loaded();

/**
 * Ensure all the tables in the database have an engine type of InnoDB rather than MyISAM
 *
 * The conversion is only performed if invoked as 
 * oikwp oik-innodb.php convert=y
 */
function oik_innodb_loaded() {
	oik_require( "class-oik-innodb.php", "oik-batch" );
	$oik_innodb = new OIK_innodb();
	$myisam_count = $oik_innodb->query_count();
	
	echo "MyISAM tables: " . $myisam_count . PHP_EOL;
	if ( $myisam_count ) {
		$convert = oik_batch_query_value_from_argv( "convert", null );
		$convert = bw_validate_torf( $convert );
		if ( $convert ) {
			echo "Converting the MyISAM tables to InnoDB" . PHP_EOL;
			$oik_innodb->maybe_convert();
		}	
	} 
}		 

