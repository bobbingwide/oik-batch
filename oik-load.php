<?php // (C) Copyright Bobbing Wide 2014

/**
 * Return a full file name for the given $plugin
 * 
 * Note: $dirname of '.' is converted to null
 * Missing $extension treated as "php"
 * Doesn't support "wordpress" files or "themes"
 *
 * $dirname  $filename result
 * --------  --------- ----------
 *      set       set  Use the given value 
 *      set       null Use the $dirname for the file name. e.g. oik/oik.php
 *      null      set  use the $filename for the directory. 
 *      null      null
 *
 * @param string $plugin  in format dirname/filename.extension
 * @return string full file name 
 * 
 *  
 */
function bw_full_file( $plugin ) {
  $dirname = pathinfo( $plugin, PATHINFO_DIRNAME );
  $dirname = str_replace( ".", null, $dirname );
  $filename = pathinfo( $plugin, PATHINFO_FILENAME );
  $extension = pathinfo( $plugin, PATHINFO_EXTENSION );
  if ( !$extension ) {
     $extension = "php";
  }
  //echo "Dirname: $dirname:" ;
  //echo "Filename: $filename:" ;
  if ( $dirname ) {
    if ( $filename ) { 
      //$plugin = "$filename/$filename.$extension";
      // whatever they said
    } else {
      $plugin = "$dirname/$dirname.php";
    }
  } else {
    if ( $filename ) {
      $plugin = "$filename/$filename.$extension"; 
    } else {
      $plugin = "oik/oik.php"; // Error really!
    }
  }   
  $plugin = WP_PLUGIN_DIR . "/" . $plugin;
  return( $plugin );
}

/**
 * Write paragraph then flush
 * @param string $text - text to write
 */
function pf( $text ) {
  p( $text );
  bw_flush();
}

/** 
 * Load WordPress pre-requisite files in order to load a plugin
 *
 * This function was created by trial and error while attempting to load "easy-digital-downloads"
 * WooCommerce was an easier plugin to load.
 *
 * Counted things oik-batch oik-load | woocommerce | EDD  | jetpack |
 * -------------- --------- -------- | ----------- | ---- | ------- |
 * User functions 625       775      | 1006        | 1337 | 784     |
 * Classes        151       155      | 182         | 174  | 171     |
 * Included files 21        28       | 69          | 84   | 51      |
 * User constants 13        43       | 50          | 48   | 52      |
 *
 * The initial User functions and Classes count varies depending on the version of PHP
 * With PHP 5.3.5 it's a much lower value than PHP 5.5.7
 
 * PHP version:5.5.7,Internal: 2115,User: 0,Classes: 170
 * PHP version:5.3.5,Internal: 1570,User: 0,Classes: 138
 *                                      
 * @TODO Can we convert backticks to <pre> after esc_html() ?
 * 
 */
function oik_load_wordpress_prerequisites() { 
  //pf( "Loading WordPress prerequisites ");
  require( ABSPATH . WPINC . "/default-constants.php" );
  /*
   * These can't be directly globalized in version.php. When updating,
   * we're including version.php from another install and don't want
   * these values to be overridden if already set.
   */
  global $wp_version, $wp_db_version, $tinymce_version, $required_php_version, $required_mysql_version;
  require( ABSPATH . WPINC . '/version.php' );
  //pf( "Running wp_initial_constants" );
  wp_initial_constants();
  //pf( "Running require_wp_db" );
  
  oikai_api_status_timer( false, "wp_initial_constants" );
  require_wp_db();
  
  oikai_api_status_timer( false, "after require_wp_db" );
  //pf( "Running wp_set_wpdb_vars" );
  wp_set_wpdb_vars();
  
  oikai_api_status_timer( false, "after wp_set_wpdb_vars" );
  //pf( "Running wp_start_object_cache" );
  //require( ABSPATH . WPINC . "/cache.php" );
  wp_start_object_cache();
  //pf( "Running wp_plugin_directory_constants" );
  wp_plugin_directory_constants();
  require( ABSPATH . WPINC . "/link-template.php" );
  oikai_api_status_timer( false, "after link-template.php" );
  require( ABSPATH . WPINC . "/shortcodes.php" );
  require( ABSPATH . WPINC . "/widgets.php" );
  // pf( "Running wp_set_lang_dir" );
  wp_set_lang_dir();
}  

/**
 * Load a plugin's file and report how many files were loaded and how many new functions were defined
 *
 * This function may not be successful if the plugin does not load its own pre-requisite files.
 * See oik_load_wordpress_prerequisites() for the current minimum set.
 * 
 */
function oik_load_loaded( $argc, $argv ) {
  $plugin_file = bw_array_get( $argv, 1, "oik/oik.php" ); 
  
  oik_batch_trace( false );
  timer_start();
   
  oik_require( "bobbfunc.inc" ); 
  //oik_require( "shortcodes/oik-api-status.php", "oik-shortcodes" );
  oikai_api_status( false, "oik-batch" );
  bw_flush();
  
  oik_load_wordpress_prerequisites();
  oikai_api_status( true, "WordPress subset" );
  bw_flush();
  
  $plugin_files = bw_as_array( $plugin_file );
  foreach ( $plugin_files as $plugin_file ) {
    $plugin_file = bw_full_file( $plugin_file );
    //timer_start();
    $rc = include( $plugin_file );
    $elapsed = timer_stop( false, 6 );
    //h3( "$plugin_file:$rc" );
    //p( "Load time (secs): $elapsed " ); 
    oikai_api_status( false, $plugin_file );
    bw_flush();
  }  
    
}

/**
 * Let this routine be invoked by the web or PHP CLI
 * 
 * When invoked in PHP CLI
 *
 * @TODO Check security considerations.
 * 
 */
if ( isset( $_SERVER['argc'] ) ) {
  oik_load_loaded( $_SERVER['argc'], $_SERVER['argv'] );
} else { 
  require( dirname( __FILE__ ) . "/oik-batch.php" );
  
  oik_batch_define_constants();
  oik_batch_load_oik_boot();
  oik_batch_simulate_wp_settings();
  oik_batch_load_wordpress_files(); 
  
  echo "done" ;
  $plugin = bw_array_get( $_REQUEST, "plugin", "oik" );
  oik_load_loaded( 2, array( null, $plugin ) );
}


