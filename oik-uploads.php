<?php // (C) Copyright Bobbing Wide 2018

/**
 * Syntax: oikwp oik-uploads.php source_dir
 * 
 * when run from the oik-batch folder
 *
 * Compares the files in source_dir with the files in the wp-content/uploads folder 
 * ( using wp_upload_dir()? )
 * Copying missing files
 *
 *
 * 
 * For WPMS
 * - No support yet
 * - subdomain install pass the url= parameter
 * - subdirectory install we need to set url= and path= 

 *
 */
 
oik_uploads_loaded();


/**
 * Copy uploaded files from the given folder
 */ 
function oik_uploads_loaded() {
	$source_dir = oik_batch_query_value_from_argv( 1, null );
	
	if ( !$source_dir ) {
		echo "Syntax: oikwp oik-uploads.php source_dir" . PHP_EOL;
		$source_dir = "/backups-qw/bigram.co.uk/public_html/wp-content/uploads";
		echo "where source_dir is $source_dir" . PHP_EOL;
		die();
	}
	echo "Source: $source_dir" . PHP_EOL;
	
	$target_dir = oik_uploads_target_dir();
	echo "Target: $target_dir" . PHP_EOL;
	
	copy_missing_files( $source_dir, $target_dir, $source_dir ); 
}

/** 
 * Determines the base target directory for uploads
 * 
 * @return string target directory e.g. /apache/htdocs/bigram/wp-content/uploads
 */
function oik_uploads_target_dir() { 
	$uploads = wp_get_upload_dir();
	$target_dir = bw_array_get( $uploads, "basedir", null );
	return $target_dir;
	
}

/**
 * Copies missing files from source dir to target_dir
 *
 * @param string $source_dir fully qualified source directory
 * @param string $target_dir target directory base
 * @param string $base source directory base
 */
function copy_missing_files( $source_dir, $target_dir, $base) {
	$files = glob( $source_dir .'/*' );
	foreach ( $files as $file ) {
		if ( is_dir( $file ) ) {
			copy_missing_files( $file, $target_dir, $base );
		} else {
			copy_missing_file( $file, $target_dir, $base );
		}
	}
}


/** 
 * Copies a missing file
 * 
 * Creates the target directory if required. 
 * 
 * @param string $file fully qualified source file
 * @param string $target_dir target directory base
 * @param string $base source directory base
 */
function copy_missing_file( $file, $target_dir, $base ) {
	//echo "copying missing file?" . $file;
	$target_file = str_replace( $base, $target_dir, $file );
	if ( !file_exists( $target_file ) ) {
		echo "Target: $target_file" . PHP_EOL;
		wp_mkdir_p( dirname( $target_file ) );
		copy( $file, $target_file );
	} else {
		copy_different_files( $file, $target_file );
	}
}

/**
 * Copy different files - maybe?
 * 
 * Files in the master may be different from the local version for a number of reasons
 * e.g. 
 * - Images may have been rotated.
 * - Case sensitivity related issues in a Windows system
 * - Files may not be kosher 
 *
 * @param string $source_file 
 * @param string $target_file
 */
function copy_different_files( $source_file, $target_file ) {
	$source_mtime = filemtime( $source_file );
	$target_mtime = filemtime( $target_file );
	if ( $source_mtime > $target_mtime ) {
		echo "Files different. Need investigation" . PHP_EOL;
		echo "$source_file $source_mtime" . PHP_EOL;
		echo "$target_file $target_mtime" . PHP_EOL;
		//copy( $source_file,$target_file );
		//gob();
	}
}

