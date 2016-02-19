<?php // (C) Copyright Bobbing Wide 2014


/**
 * List the previous version file list
 *
 * Return an array of files containing the previous version's file names and CRC values
 * so that we can check for changes.
 *
 * @param string $component - the component to load - may be a plugin, theme or WordPress itself
 * @param string $previous - the required version. If null then we 
 */
function oikb_list_previous_files( $plugin, $previous=null ) {
  $files = array();
  if ( $previous ) {
    $source = oikb_locate_source( $plugin, $previous );
    if ( $source ) {
      $files = oikb_list_files( $source ); 
    }
  } 
  return( $files );
}

/**
 * Locate the source of the previous version's files
 *
 * Eventually, we will look in SVN folders, Zip files and other places.
 * The source for these is currently hardcoded. Let's get it working first. 
 *  
 * @param string $component
 * @param string $previous
 * @return string - folder containing the files
 *
 */
function oikb_locate_source( $component, $previous ) {
  $sources = array( "svn" => oikb_get_svn_source()
                  , "svn_plugins" => oikb_get_svn_plugins_source()
                  //, "zip" => oikb_get_zip_source()
                  //, "git" => oikb_get_git_source()
                  );
  $source = null;
  $test = current( $sources );
  while ( !$source && $test ) {
    if ( $test ) {
      $folder = $test . "/" . $component . "/tags/" . $previous;
      echo "Folder: $folder" . PHP_EOL;
      if ( file_exists( $folder ) ) {
        $source = $folder;
      }   
    }
    if ( !$source ) {
      $test = next( $sources );
    }  
  }
  echo "Source: $source" . PHP_EOL;     
  return( $source );
} 

/**
 * List the files from the source
 *
 *
 * @TODO Extract or list files in the .zip folder
 *
 * @param string $source - the source folder or (zip) file of the files
 * @return array $files - array of files with CRC or other info
 */
function oikb_list_files( $source ) {
  $files = oikb_list_files_in_directory( $source );
  $files = oikb_list_files_crc( $files );
  $files = oikb_relative_files( $files, $source );
  return( $files );
}

/**
 * Get SVN source folder
 *
 * We need to be able to find out where the files have been extracted from SVN
 * @TODO - Specify as a constant in wp-config.php or wp-batch-config.php or wp-cli's config file?
 *
 * 
 * - svn.wordpress.org
 * - plugins.svn.wordpress.org
 * - themes.svn.wordpress.org
 * 
 * @return string - the source folder for SVN extracts
 */
function oikb_get_svn_source() {
  $source = "c:/svn";
  return( $source );
}

/**
 * Get SVN plugins source
 * 
 */
 
function oikb_get_svn_plugins_source() {
  $source = "c:/apache/htdocs/svn_plugins";
  return( $source );
}

/**
 * Get ZIP file source
 *
 * Currently...  
 *
 * zip files created by "oik-zip" are stored in the plugins folder
 * zip files created by "oik-tip" are stored in the themes folder
 *
 * @return string - the source folder for zip files
 */
function oikb_get_zip_source() {
  $source = ABSPATH . "wp-content/plugins" ;
  return( $source );
}

/**
 * Get the source folder for the git repository
 * 
 * @return string - the source folder for Git repositories
 */
function oikb_get_git_source( $repository, $owner="bobbingwide" ) {
	$source_dir = null;
	$repos = array( "C:/github/$owner/$repository"
								, "C:/github/$repository"
								);
	foreach ( $repos as $repo ) {
		$source_dir = oikb_is_git( $repo );
		if ( $source_dir ) {
			break;
		}
	}
  return( $source_dir );
}

/**
 * Return the root directory of the git repository
 *
 * 
 */
function oikb_is_git( $source_dir ) {
	$git = git();
	$git_dir = $git->is_git( $source_dir );
	
	return( $git_dir );
}

/**
 * Return the list of files in the directory
 * 
 * This returns an array of all files, except directory pointers.
 * Hopefully you don't have a recursive directory structure.
 *
 * If there are zillions of non-PHP files then subsequent processing might take a little longer than we'd like. 
 *
 * @param string $directory - not expected to have trailing slash
 * @return array - array of files
 */
function oikb_list_files_in_directory( $directory ) {
  $iterableFiles = new \RecursiveIteratorIterator( new \RecursiveDirectoryIterator( $directory ) );
  $files = array();
  try {
    foreach ( $iterableFiles as $file ) {
      $fil =  $file->getFilename();
      if ( $fil !== "." && $fil !== ".." ) {
        $filename = $file->getPathname();
        //echo $filename . PHP_EOL;
        $files[] = $filename; 
      }  
    }
  } catch ( \UnexpectedValueException $e ) {
    bw_trace2( 'Directory [%s] contained a directory we can not recurse into', $directory );
  }
  return $files;
}

/**
 * Get CRC for a file
 *
 * Convert Windows line endings to Unix when determining the CRC
 * since SVN extracted files will not have their line endings changed automatically.
 *  
 * @param string $filename - fully qualified file name
 * @return string - CRC as a hexadecimal code
 */
function oikb_get_file_crc( $filename ) { 
  if ( file_exists( $filename ) ) {
    $contents = file_get_contents( $filename );
    $contents = str_replace( "\r\n", "\n", $contents );
    //$contents = str_replace( "\n", " ", $contents );
    //$contents = str_replace( "\r", " ", $contents );
    $crc = dechex( crc32( $contents ) );
  } else {
    echo "File missing: $filename" . PHP_EOL;  
  }  
  //echo $crc;
  return( $crc );
} 

/**
 * List the CRC for each file
 *
 * @param array $files - array of fully qualified file names
 * @return array - associative array of file and their CRCs
 */
function oikb_list_files_crc( $files ) {
  foreach ( $files as $key => $filename ) {
    $crc = oikb_get_file_crc( $filename );
    $crcfiles[$filename] = $crc;
  } 
  return( $crcfiles );
} 

/** 
 * Return relative file array
 *
 * Strip the path from each key in the associative array of files
 * 
 * @param array $files - associative array of fully qualified files
 * @param string $source - the source folder of the files - excluding the trailing slash
 * @return array $rfiles - associative array of relative files
 */
function oikb_relative_files( $files, $source ) {

  $start = strlen( $source );
  $start++;
  foreach ( $files as $key => $value ) {
    $filename = substr( $key, $start );
    $filename = str_replace( "\\", "/", $filename );
    $rfiles[$filename] = $value; 
  }
  //print_r( $rfiles );
  return( $rfiles );
}


/**
 * Return the selected components source directory
 *
 * Question... should we sanitize the directory returned?
 
 */
function oikb_source_dir( $plugin, $component_type ) {
 switch ( $component_type ) {
    case "wordpress":
      $sourcedir = trim( ABSPATH, '/' ); 
      break;
    case "plugin":
      $sourcedir = WP_PLUGIN_DIR . "/$plugin";
      break;
    case "theme": 
      $sourcedir = get_theme_root() . "/$plugin";
      break;
    default:
      echo "Unrecognised component: $plugin component_type: $component_type" . PHP_EOL;
      $sourcedir = getcwd();
  }
  echo "Source dir: " . $sourcedir . PHP_EOL;
  return( $sourcedir );

}

/**
 * List the changed files
 * 
 * @param array $files
 * @param string $prev_version
 * @param string $plugin
 * @param string $component_type
 * @return array - the set of changed/new files
 */
function oikb_maybe_do_files( $files, $prev_version, $plugin, $component_type ) {
  $dofiles = array();
  $previous = oikb_list_previous_files( $plugin, $prev_version );
  echo "Previous: " . count( $previous ) . PHP_EOL;
  echo "Current: " . count( $files ) . PHP_EOL;
  $sourcedir = oikb_source_dir( $plugin, $component_type );
  
  foreach ( $files as $file ) {
    if ( substr( $file, -1 ) !== "." ) {
      $source_file = $sourcedir . '/' . $file;
      //echo "maybe: $source_file" . PHP_EOL;
      $previous_crc = bw_array_get( $previous, $file, null );
      if ( $previous_crc ) {
        $this_crc = oikb_get_file_crc( $source_file );
        if ( $previous_crc != $this_crc ) {
          ///echo "$source_file $previous_crc $file $this_crc" . PHP_EOL;
          $dofiles[] = $file;
        }    
      } else {
        $dofiles[] = $file; 
      }  
    } 
     
  } 
  echo "Changes: " . count( $dofiles ) . PHP_EOL;
  return( $dofiles );
}

/**
 * List the changed files 
 *
 * If we're using a git/GitHub repository then we can easily list the changed files
 * by using git commands.
 * 
 * {@link 	
 * `git diff --name_only sha1 sha2`
 * `git diff --name_only
 *
 * oikb_get_git_source() should return the directory of the git repo
 *  
 * use this function in preference to oikb_maybe_do_files()
 *  
 * 
 */
function oikb_list_changed_files( $prev_version, $plugin, $component_type ) {
	$git_loaded = oik_require_lib( "git" );
	echo $git_loaded;
	oik_require( "libs/oik-git.php", "oik-batch" );
	
	$git = Git();
	
	$source_dir = oikb_source_dir( $plugin, $component_type );
	$git_dir = oikb_is_git( $source_dir );
	if ( !$git_dir ) {
		$repository = $plugin;
		$git_dir = oikb_get_git_source( $repository );
	}
	echo "Git dir: $git_dir" . PHP_EOL;
	
	if ( $git_dir ) {
		$files = oikb_git_command( "changed", $prev_version );
		$files = $git->result_as_array();
	} else {
		$files = null;
	}
	
  echo "GIT changes: " . count( $files ) . PHP_EOL;
	return( $files );

}

/**
 * Implement a pseudo git command
 *
 * These are pseudo git commands - not exactly aliases
 * but they perform a subset of what you can do with git
 * 
 * $command | $parms | Processing
 * -------- | ------- | ----------
 * status -s | n/a |  Determine if this is a git repository
 * changed | tag | List changed files since tagged version
 * 
 * 
 */

function oikb_git_command( $command="status -s", $parms=null ) {
	$git = git();
	$result = $git->command( $command, $parms );
	echo $result;
	return( $result );
}


	
	

                   
 



