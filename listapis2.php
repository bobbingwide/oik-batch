<?php // (C) Copyright Bobbing Wide 2013-2015
/** 
 * Function to invoke when listapis2.php is loaded
 * @param int $argc - count of parameters
 * @param array $argv - array of parameters 
 */
function listapis_loaded( $argc, $argv ) {
  define( 'WP_SETUP_CONFIG', true );
  require_once( ABSPATH . "wp-admin/includes/plugin.php" );
  require_once( ABSPATH . WPINC . "/shortcodes.php" );
  require_once( ABSPATH . WPINC . "/theme.php" );
	//oik_require_lib( "bobbcomp" );
	oik_require( "bobbcomp.inc" );
  oik_require_lib( "bobbfunc" );
  if ( $argc > 1 ) {
    $component = $argv[1];
    if ( $argc > 2 ) {
      $previous = $argv[2];
    } else {
      $previous = null;
    }
  } else {
    oik_require( "list_oik_plugins.php", "oik-batch" );
    $component = list_oik_plugins();
  }    
  $components = bw_as_array( $component );
  oik_require( "oik-ignore-list.php", "oik-batch" );
  foreach ( $components as $component ) {
    //$previous = oikb_list_previous_files( $component, $previous );
    _la_doit( $component, $previous );
  }  
  //echo "done" .  PHP_EOL;
}

/**
 * List the APIs for a specific file
 * 
 * We process .php and .inc files which are not in 'ignore list'
 *
 *
 * @param string $file
 * @param string $component_type - "plugin" or "theme"
 */
function _la_dofile( $file, $plugin=null, $component_type="plugin" ) {
  echo "Processing: $file". PHP_EOL;
  $ext = pathinfo( $file, PATHINFO_EXTENSION );
  $ext = strtolower( $ext );
  $exts = bw_assoc( array( "php", "inc" ));
  $validext = bw_array_get( $exts, $ext, false );
  if ( $validext ) {
    $inignorelist = _la_checkignorelist( $file );
    if ( !$inignorelist ) {
      //_lf_dofile_ajax( $file ); - not required in listapis2 - see createapi2
      _la_doapis( $file, $plugin, $component_type ); 
    }
  }
}

/**
 * List the apis for a particular file in a plugin
 *
 * @param string $file - file name to load
 * 
 */
function _la_doapis( $file, $plugin, $component_type ) {
  global $plugin;
  static $count = 0;
  static $count_classes = 0;
  static $count_functions = 0;
  static $count_files = 0;
  $count_files++;
  $apis = _oiksc_get_apis2( $file, true, $component_type );
  $file = strip_directory_path( ABSPATH, $file );
  // plugin,file,class,parent class,method,startline,endline,size
  echo "$count,$count_classes,$count_files,$plugin,$file,,,,,,," . PHP_EOL;
  foreach ( $apis as $api ) {
    $line = $api->as_csv( $plugin, $file );
    if ( $api->classname && !$api->methodname ) {
      $count_classes++;
    } else {
      $count++;
      _la_setMaxSize( $api->getSize() ) ;
    }
    
    echo "$count,$count_classes,$count_files,$line" . PHP_EOL;
  }
}

/**
 * Set the Maximum file size detected so far
 * 
 * @param $size - this file's size
 */
function _la_setMaxSize( $size ) {
  global $max_size;
  if ( $size > $max_size ) {
    $max_size = $size;
  }
}
  
/**
 * Process the selected component
 *
 * Load all the files for the selected component
 * and process them one by one
 *
 * Note: This is a bit like makepot and probably a bit like the wp-parser
 * except that it's designed primarily for WordPress plugins.
 * 
 * @param string $component - expected to be a locally installed plugin
 * @param string $previous - the previous version to compare against
 *
 */
function _la_doit( $component, $previous=null ) {
  //echo __FUNCTION__ . PHP_EOL;
  global $plugin;
  $plugin = $component;
  oik_require( "admin/oik-apis.php", "oik-shortcodes" );
  oik_require( "oik-list-wordpress-files.php", "oik-batch" );
  oik_require( "oik-list-previous-files.php", "oik-batch" );
  echo "Plugin: $plugin" . PHP_EOL;
  $component_type = oiksc_query_component_type( $plugin ); 
	
	$files = oikb_list_changed_files( $previous, $plugin, $component_type );
	
	if ( null === $files ) {
		$files = oiksc_load_files( $plugin, $component_type );
		$files = oikb_maybe_do_files( $files, $previous, $plugin, $component_type );
	}
  oiksc_do_files( $files, $plugin, $component_type, "_la_dofile" );
  global $max_size;
  echo "Max size: $max_size" . PHP_EOL;
}
  
listapis_loaded( $_SERVER['argc'], $_SERVER['argv'] );
