<?php // (C) Copyright Bobbing Wide 2013-2015

/** 
 * Function to invoke when createapi2.php is loaded
 * 
 * Syntax: $argv[0] --plugin= [--site= --user= --pass= --apikey=] --name=api --previous=previous --start=start
 *
 * Note: createapi2.php is not designed to be called by oik-batch.php
 * as it needs to obtain parameters using oikb_getopt()
 * 
 * @TODO - remove this limitation
 * 
 */
function createapis_loaded( $argc, $argv ) {
  echo "In createapis_loaded" . PHP_EOL ;
  require_once( "oik-batch.php" );
  if ( defined( 'WP_SETUP_CONFIG' ) ) {
    if ( true == 'WP_SETUP_CONFIG' ) {
      bw_trace2( "WP_SETUP_CONFIG is already defined as true" );
    } else {
      bw_trace2( "WP_SETUP_CONFIG is already defined as false" );
    }   
  } else {  
    define( 'WP_SETUP_CONFIG', true );
    // Do we need these now?
  
    require_once( ABSPATH . WPINC . "/http.php" );
    require( ABSPATH . WPINC . '/class-http.php' );
    
    
    require_once( ABSPATH . "wp-admin/includes/plugin.php" );
    require_once( ABSPATH . WPINC . "/shortcodes.php" );
    require_once( ABSPATH . WPINC . "/theme.php" );
   
    oik_require( "/libs/bobbfunc.php" );
		oik_require( "bobbcomp.inc" );
    oik_require( "oik-login.inc", "oik-batch" );
    
    $plugin = bw_array_get( oikb_getopt(), "plugin", null );
    echo "Plugin/theme: $plugin" . PHP_EOL;
    
    global $apikey;
    $apikey = bw_array_get( oikb_getopt(), "apikey", null ); 
    echo "apikey: $apikey" . PHP_EOL;
    
    global $selected_api;
    $selected_api = bw_array_get( oikb_getopt(), "name", null );
    echo "api: $selected_api!" . PHP_EOL;
    
    /**
     * We can only use previous= when we've chosen a specific plugin
     */
    $previous = bw_array_get( oikb_getopt(), "previous", null );
    echo "Previous: $previous" . PHP_EOL;
		/**
		 * Use start to restart the processing from a particular point
		 * Again, only really relevant for a single plugin
		 */
		$start = bw_array_get( oikb_getopt(), "start", 1 );
		echo "Start: $start" . PHP_EOL;
    
    
    if ( $plugin ) {
      $component = $plugin; // $argv[1];
    } else {
      oik_require( "list_oik_plugins.php", "oik-batch" );
      $component = list_oik_plugins();
    } 
		oik_require( "admin/oik-apis.php", "oik-shortcodes" );
		oik_require( "oik-ignore-list.php", "oik-batch" );
    
    $components = bw_as_array( $component );
    foreach ( $components as $component ) {
      _ca_doaplugin( $component, $previous, $start );
    }  
    //echo "done" .  PHP_EOL;
  }
}

/**
 * createapi ignore list - copy of listapis ignore list 
 */
function _ca_checkignorelist( $file ) {
	$ignore = _la_checkignorelist( $file );
	return( $ignore );
}




/**
 * Create the apis for a particular file in a plugin
 * 
 * 
 * Obtain a list of the APIs in the source file. 
 * For each API send a message to the server to create/update the API.
 * Note: We ignore classes - these are created dynamically
 *
 *
 * @param string $file - file name to load
 * @global $plugin - which may be null when processing WordPress core
 */
function _lf_dofile_ajax( $file ) {
  global $plugin;
  if ( oikb_get_site() ) {
    if ( $plugin == "wordpress" ) {
      $file = strip_directory_path( ABSPATH, $file );
    } else {
      echo $file . PHP_EOL;
        
    }
    echo "Processing file: $plugin,$file" . PHP_EOL;
    $response = oikb_get_response( "Continue to process file?" );
    if ( $response ) {
      $apikey = oikb_get_apikey();
      oik_require( "oik-admin-ajax.inc", "oik-batch" );
      oikb_admin_ajax_post_create_file( oikb_get_site(), $plugin, $file, $apikey );
    }
    echo "Processing file: $plugin,$file ended" . PHP_EOL;
  }
}
 
/**
 * Process a file
 *
 * We process .php and .inc files
 * Note: WordPress doesn't have any .inc files
 *
 * @TODO: Expand to cover ALL files.
 *
 * @param string $file - the file name
 *
 */ 
function _ca_dofile( $file, $plugin, $component_type, $start=1 ) {
  if ( $plugin ) {
    $inignorelist = _ca_checkignorelist( $file );
  } else {
    $inignorelist = false;
  }
  if ( !$inignorelist ) {    
    echo "Processing: $file". PHP_EOL;
    $ext = pathinfo( $file, PATHINFO_EXTENSION );
    $ext = strtolower( $ext );
    $exts = bw_assoc( array( "php", "inc" ));
    $validext = bw_array_get( $exts, $ext, false );
    if ( $validext ) {
      _lf_dofile_ajax( $file );
      _ca_doapis( $file, $plugin, $component_type, $start ); 
    }
  }  
}

/**
 * Check for the selected API
 */
function _ca_checkforselected_api( $api, $count ) {
  global $selected_api;
  if ( $selected_api ) {
    $selected = ( $api == $selected_api ) || ( $count >= $selected_api );
  } else {
    $selected = true; 
  }
  return( $selected );
}

/**
 * Create the apis for a particular file in a plugin
 * 
 * 
 * Obtain a list of the APIs in the source file. 
 * For each API send a message to the server to create/update the API.
 * Note: We ignore classes - these are created dynamically
 *
 *
 * @param string $file - file name to load
 * @global $plugin - which may be null when processing WordPress core
 */
function _ca_doapis( $file, $plugin_p, $component_type, $start=1 ) {
  global $plugin;
  $plugin = $plugin_p;
  static $count = 0;
  echo "Processing valid: $plugin $file $component_type" . PHP_EOL;
  $apis = _oiksc_get_apis2( $file, true, $component_type );
  foreach ( $apis as $api ) {
    $count++;
		if ( $count < $start ) {
			continue;
		}
    //echo "$count $file $api" ;
    //doapi( $file, $api ); 
    if ( $api->methodname ) {
      // @TODO move this logic to the server
      $apitype = $api->getApiType();
      $apiname = $api->getApiName();
    } else { 
      $apitype = null;
      $apiname = $api->classname . "::";
    }
    $csv = $api->as_csv( $plugin, $file );
    $desc = $api->getShortDescription();
    echo "$count,$csv,$apitype,$desc";
    oik_require( "oik-admin-ajax.inc", "oik-batch" );
    if ( oikb_get_site() ) {
      //if ( !$plugin ) {
      
      if ( $plugin == "wordpress" ) {
        $file = strip_directory_path( ABSPATH, $file );
      }
      echo "Processing api: $plugin,$file,$apiname" . PHP_EOL;
      $response = _ca_checkforselected_api( $apiname, $count );
      if ( $response ) {
        $response = oikb_get_response( "Continue to create API?" );
      }  
      if ( $response ) {
        $apikey = oikb_get_apikey();
        oikb_admin_ajax_post_create_api( oikb_get_site(), $plugin, $file, $apiname, $apitype, $desc, $apikey );
      }  
    }
  }
}

/**
 * Create the APIs for a component
 *
 * List the files for the component
 * Components supported:
 * "wordpress" - all files in the WordPress installation excluding wp-content
 * <i>plugin</i> - all the files in the named plugin 
 * <i>theme</i> - all the files in the named theme
 *
 * Process each file in the component, defining the file, classes, methods and APIs
 * 
 * @param string $component - the name of the plugin or theme, or "wordpress"
 * @param string $previous - the previous version to compare against - for performance
 */
function _ca_doaplugin( $component, $previous=null, $start=1 ) {
  global $plugin, $apikey;
  $plugin = $component;
  if ( $plugin ) {
    $component_type = oiksc_query_component_type( $plugin );
    if ( $component_type ) {
      echo "Doing a $component_type: " . $plugin . PHP_EOL;
  
      if ( $apikey ) {
        echo "Using apikey=$apikey" . PHP_EOL;
      } else { 
        echo "Missing --apikey= parameter" . PHP_EOL;
        gobang();
        oikb_login(); 
      }  
      $response = oikb_get_response( "Continue?", true );
      if ( $response ) {
        oik_require( "admin/oik-apis.php", "oik-shortcodes" );
        //wp_register_plugin_realpath( WP_PLUGIN_DIR . "/$plugin/." );
        oik_require( "oik-list-previous-files.php", "oik-batch" );
        $files = oiksc_load_files( $plugin, $component_type );
        $files = oikb_maybe_do_files( $files, $previous, $component, $component_type );
        oiksc_do_files( $files, $plugin, $component_type, "_ca_dofile", $start );
      }
    } else {
      echo "Invalid plugin/theme: $component" . PHP_EOL;
    }       
  } else {
    echo "Missing --plugin= parameter" . PHP_EOL;
  } 
}
  
if ( !function_exists( "get_bloginfo" ) ) {       
function get_bloginfo( $arg) {
  return ( "http://localhost" ); 
}
}


createapis_loaded(  $_SERVER['argc'], $_SERVER['argv'] );
