<?php // (C) Copyright Bobbing Wide 2013, 2014


/**
 * Report (some of) the result from the server
 */
function oikb_report_result( $result ) {
  $len = strlen( $result );
   
  if ( $len > 200 ) {
    $result = substr( $result, 0, 200 );
  }
  echo $len . " " . $result . PHP_EOL;
}

/**
 * Invoke admin-ajax using POST
 * 
 * Attempting to drive add-new and edit from a batch client was far too complex.
 * 
 * It's much easier to invoke an admin-ajax server function that will
 * - extract the fields from the body
 * - perform the queries to find out what to do
 * - performs inserts or updates as required
 * We just need to give it enough information to know what to do
 * AND hope that it can achieve that
 *
 * @param string $site - the URL of the site. e.g. http://qw/wordpress
 * @param string $plugin - the plugin slug 
 * @param string $file - the source file that contains the API
 * @param string $class = the class name (if applicable)
 * @param string $api - the API name - function or class::method
 * @param string $type - the "type" of the API - e.g. "action", "filter", "private" 
 * @param string $title - the title we've determined for the API
 * @param string $apikey - the apikey to obtain authorisation to continue
 *
 */ 
function oikb_admin_ajax_post_create_api( $site, $plugin, $file, $api, $type, $title, $apikey=null ) {
  $url = "$site/wp-admin/admin-ajax.php" ;
  $body = array( "action" => "oiksc_create_api" 
               , "plugin" => $plugin
               , "file" => $file
               , "type" => $type
               , "api" => $api
               , "oik_apikey" => $apikey
               , "title" => $title
               );
  echo "Adding $file $api $type $title" . PHP_EOL;               
  global $oikb_cookies;
  $args = array( "body" => $body 
               , "cookies" => $oikb_cookies 
               , 'timeout' => 15
               ); 
  $result = oikb_remote_post( $url, $args );
  oikb_report_result( $result );
  
}


/**
 * Invoke admin-ajax using POST to process a file
 * 
 *
 * @param string $site - the URL of the site. e.g. http://qw/wordpress
 * @param string $plugin - the plugin slug 
 * @param string $file - the source file 
 * @param string $apikey - the apikey to obtain authorisation to continue
 *
 */ 
function oikb_admin_ajax_post_create_file( $site, $plugin, $file, $apikey ) {
  $url = "$site/wp-admin/admin-ajax.php" ;
  $body = array( "action" => "oiksc_create_file" 
               , "plugin" => $plugin
               , "file" => $file
               , "oik_apikey" => $apikey
               );
  echo "Adding $file " . PHP_EOL;               
  global $oikb_cookies;
  $args = array( "body" => $body 
               , "cookies" => $oikb_cookies 
               , 'timeout' => 15
               ); 
  $result = oikb_remote_post( $url, $args );
  oikb_report_result( $result ); 
}
  


