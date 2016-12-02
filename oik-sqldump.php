<?php // (C) Copyright Bobbing Wide 2016

if ( PHP_SAPI !== "cli" ) { 
	die();
}

/**
 * Run mysqldump against the installation's database
 * 
 */
function oik_sqldump_loaded() {
	oik_require( "class-oik-sqldump.php", "oik-batch" );
	$oik_sqldump = new OIK_sqldump();
}

oik_sqldump_loaded();

