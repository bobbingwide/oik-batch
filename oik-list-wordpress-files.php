<?php // (C) Copyright Bobbing Wide 2014-2016

/**
 * List WordPress files
 * 
 * @TODO - Remove hardcoded directory location
 *
 * @return array of files in WordPress
 * 
 */
function _la_get_wordpress_files() {
  //echo "ABSPATH" . ABSPATH . PHP_EOL;
  $directory = "/github/WordPress";
  $directory = "C:/apache/htdocs/wordpress/";
  $directory = ABSPATH;
  //echo "Directory: $directory" . PHP_EOL;
  $files = get_wp_files( $directory );
  return( $files );
}

/**
 * listapis recursive filter iterator
 *
 */ 
class _la_RFI extends RecursiveFilterIterator {
  /**
   * Check if this folder should be processed  
   *
   * @return bool - true if this (file's) folder is not in the array of excluded folders  
   */
  public function accept() {
    return( !in_array( $this->current()->getFileName(), array( "plugins", "themes", "wp-content", ".hg", ".idea", "cookie-cat", "happy", "images", "us-tides_svn" ), true ));
  }
}

/**
 * Remove the leading directory path from the filename
 *
 */
function strip_directory_path( $directory_path, $filename ) {
  $directory_path = str_replace( "\\", "/", $directory_path ); 
  $filename = str_replace( "\\", "/", $filename );
  $filename = str_replace( $directory_path, null, $filename );
  return( $filename );  
}  

/**
 * List PHP files within this directory taking into account excluded folders
 *
 * Code copied from WP-parser\lib\runner.php and modified to support excluded folders
 *
 * @param string $directory - the root directory for the file list
 * @return array of file names 
 */
function get_wp_files( $directory ) {
  $iterator = new \RecursiveDirectoryIterator( $directory );
  $filter = new _la_RFI( $iterator );
	$iterableFiles = new \RecursiveIteratorIterator( $filter, RecursiveIteratorIterator::SELF_FIRST );
	$files = array();
  $directory = str_replace( "\\", "/", $directory );
  //echo $directory . PHP_EOL;
  
	try {
		foreach ( $iterableFiles as $file ) {
      $filename = $file->getPathname();
      $file_extension = pathinfo( $filename, PATHINFO_EXTENSION);
			if ( $file_extension !== 'php' ) {
				continue;
			}
      $filename = strip_directory_path( $directory, $filename ); 
      //echo $filename; 
      //bw_trace2( $file );
      $files[] = $filename;
			//$files[] = $file->getPathname();
      //$files[] = $file->getFilename();
      
		}
	} catch ( \UnexpectedValueException $e ) {
		printf( 'Directory [%s] contained a directory we can not recurse into', $directory );
	}
	return $files;
}


/**
 * Filter WordPress files listed from Git
 *
 * The WordPress Git repository contains files for plugins, themes and other things that we want to handle separately. 
 * 
 * So filter them out. See _la_RFI
 *
 */
function oikb_filter_wordpress_files( $files ) {
	$filtered = array();
	foreach ( $files as $file ) {
		if ( 0 === strpos( $file, "wp-content/" ) ) {
			if ( strpos( $file, "plugins/index.php" ) ) {
				$filtered[] = $file;
			} elseif ( strpos( $file, "themes/index.php" ) ) {
				$filtered[] = $file;
			} elseif ( $file == "wp-content/index.php" ) {
				$filtered[] = $file;
			}
		}	else {
			$filtered[] = $file;
		}
	}
	return( $filtered );
}
