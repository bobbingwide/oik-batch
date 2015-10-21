<?php // (C) Copyright Bobbing Wide 2013, 2014


if ( !function_exists( "get_bloginfo" ) ) {  
function get_bloginfo( $arg) {
  return ( "http://localhost" ); 
}
}

/**
 * Get a response from stdin
 *
 * @param string $text - prompt for the response
 * @param bool $pause - force a pause even when the response is "go"
 * @return string the trimmed response
 */
function oikb_get_response( $text="Continue?", $pause=false ) {
  static $response = false; 
  echo "$text ( y=yes/n=no/g=go/q=quit )";
  if ( $pause || ( $response !== "g" ) && ( $response !== "s" ) ) {
    $stdin = fopen( "php://stdin", "r" );
    $response = fgets( $stdin );
    fclose( $stdin );
  }  
  $response = strtolower( substr( trim( $response ), 0, 1 ) );
  switch ( $response ) {
    case "n":
    case "s":
      $response = false;
      break;
    case "q":
      echo "Ending";
      die();
      break;
    case "":
      $response = true;
      break;
    default:
      // Let the caller determine
      break;    
  }
  return( $response );
}

/**
 * Create an associative array of the options from $argv
 * 
 * Note: we don't yet make use of the -s, -u or -p values
 * BUT we do use --site --user --pass --apikey and --name
 * 
 * @param string $opt - the parameter we're looking for
 * @return string $options - the options value
 */
function oikb_getopt( $opt=null ) {
  if ( $opt ) { 
    $options = getopt( substr( $opt, 1 ).":", array( $opt .":" ) );
  } else { 
    $options = getopt( "s:u:p:", array( "site:", "user:", "pass:", "plugin:", "apikey:", "name:" ) );
  }  
  //echo PHP_EOL;
  //print_r( $options );
  echo PHP_EOL;
  return( $options ); 
} 

/**
 * Return the URL of the remote site to access
 * 
 * @param string $site - optional value for the site in form http://example.com
 * @return string the remote site URL ( which may be a subfolder )
 */
function oikb_get_site( $site=null ) {
  static $oikb_site; 
  if ( !$site ) { 
    $oikb_site = bw_array_get( oikb_getopt(), "site", null ); 
    if ( !$oikb_site ) {
      $site="http://qw/wpit";
      $oikb_site = oikb_get_response( "Enter site ( default $site ):" );
      if ( !$oikb_site ) {
        $oikb_site = $site; 
      }
    }
  } else {
    $oikb_site = $site; 
  } 
  return( $oikb_site );  
}

/**
 * Obtain the username for oik-batch
 *
 * Note: Use the apikey in preference to username and password
 * 
 * @param string $user - the 'default' user name
 * @return string - the required user name
 */
function oikb_get_user( $user=null ) {
  if ( !$user ) {
    $user = bw_array_get( oikb_getopt(), "user", null );
  }  
  if ( !$user ) {
    $user = oikb_get_response( "Enter user:" );
  }
  return( $user );
}

/**
 * Obtain the password for oik-batch
 *
 *
 * Note: Use the apiref in preference to username and password
 *
 * @param string $pass - the 'default' password
 * @return string - the required password 
 */
function oikb_get_pass( $pass=null ) {
  if ( !$pass ) {
    $pass = bw_array_get( oikb_getopt(), "pass", null );
  }  
  if ( !$pass ) {
    $pass = oikb_get_response( "Enter password:" );
  }
  return( $pass );
}

/**
 * Obtain the apikey for oik-batch
 *
 * Until oAuth2 is implemented we support a simple API key to restrict access to the servers
 * 
 */
function oikb_get_apikey( $apikey=null ) {
  if ( !$apikey ) {
    $apikey = bw_array_get( oikb_getopt(), "apikey", null );
  }  
  if ( !$apikey ) {
    $apikey = oikb_get_response( "Enter API key:" );
  }
  return( $apikey );
}

/**
 * Set cookies required for logging on
 *
 * Added a special cookie for getting past 5quidhosts "Are you human?" check. 2013/06/22
 * 2013/11/06 - they've changed their code so that the cookie is not the same
 * so either we have to use wp-logim.php again OR change to using a different method of logging in. e.g. oauth2
 *
 * @return array of Wp_Http_cookie objects
 */
function oikb_cookies() {
  $cookies = array();
  $cookies[] = new Wp_Http_cookie( "wordpress_test_cookie=WP+Cookie+check;path=/wordpress/" );
  //$cookies[] = new Wp_Http_cookie( "humancheck=YouShallNotPass2013;path=/" );
  // 7eb589989b00014e61b445ec100c9bc5
  // 7eb589989b00014e61b445ec100c9bc5
  
  $cookies[] = new Wp_Http_cookie( "humancheck=7eb589989b00014e61b445ec100c9bc5;path=/" );
  bw_trace2( $cookies, "cookies", false );
  return( $cookies );
}  

/**
 * Perform remote WordPress login
 *
 * The Log In request from wp-login.php looks like this
 *
 *   [log] => username
 *   [pwd] => password - Note that this is in plain text
 *   [wp-submit] => Log In
 *   [redirect_to] => http://www.example.com/wp-admin/
 *   [testcookie] => 1
 *
 *
 * This request should only be performed in the absence of another method
 * It may fail if the hosting company attempts to restrict access to the wp-login.php file

 */
function oikb_login( $site=null, $user=null, $pass=null ) {
  $site = oikb_get_site( $site );
  $user = oikb_get_user( $user );
  $pass = oikb_get_pass( $pass ); 
  if ( $user && $pass ) {  
    $parms = array( "log" => $user
                  , "pwd" => $pass
                  , "wp-submit" => "Log+In"
                  , "redirect_to" => "$site/wp-admin/"
                  , "testcookie" => "1"
                  );
    bw_trace2();
    $url = "$site/wp-login.php" ; 
    $url = "$site/wp-logim.php" ; // 2013/06/22 - No longer need wp-logim.php on oik-plugins.com or oik-plugins.co.uk 
    echo $url . PHP_EOL;
    /* Disable redirection handling - we want to get a 302! */
    $args = array( "body" => $parms 
                 , "cookies" => oikb_cookies()
                 , "redirection" => 0
                 );
    oik_require( "includes/oik-remote.inc" );
    $result = oikb_remote_post( $url, $args );
    // echo __FUNCTION__ . " result:"; 
    print_r( $result );
    // echo PHP_EOL;
  } else {
    $result = null;  
  }
  bw_trace2( $result );
  return( $result );
}

/**
 * 
 * server calls 
    set_auth_cookie admin|1360148096|1b735b9cba5c59124210f25259be8079
    set_logged_in_cookie admin|1360148096|8d428eb484c51fda475cb17358634851
    
 * we see
 * 
 
                    [0] => wordpress_test_cookie=WP+Cookie+check; path=/wordpress/
                    [1] => wordpress_b52421ac8f23936806405e044a4a9d78=admin%7C1360148099%7Ccab22fc0acb01b73a38caa2a9fe347cd; path=/wordpress/wp-content/plugins; httponly
                    [2] => wordpress_b52421ac8f23936806405e044a4a9d78=admin%7C1360148099%7Ccab22fc0acb01b73a38caa2a9fe347cd; path=/wordpress/wp-admin; httponly
                    [3] => wordpress_logged_in_b52421ac8f23936806405e044a4a9d78=admin%7C1360148099%7Cf2f105b975a46af6582f6657efe11988; path=/wordpress/; httponly
                    
  wordpress_b52421ac8f23936806405e044a4a9d78=admin%7C1360147983%7Cece0d8f2b6f426c06032bd9a69812c8d; 
*/


/**
 * Wrapper to wp_remote_post
 * 
 * @param string $url the request URL
 * @param array $args - array of args including body array 
 * @return unserialized result or null
 */ 
function oikb_remote_post( $url, $args ) {
  $request = wp_remote_post( $url, $args );
  echo "after remote post" . PHP_EOL;
  if ( is_wp_error ($request ) ) {
    bw_trace2( $request, "request is_wp_error" );
    $result = oikb_handle_response( $request );
    //$result = null;
  } else {
    $result = oikb_retrieve_result( $request );
  }
  bw_trace2( $result, "result" );
  return( $result );
}

/**
 * Handle the errors when the result of wp_remote_post() is a WP_Error
 * 
 * Can we determine the response code from the WP_Error object?
 
        [errors] => Array
            (
                [http_request_failed] => Array
                    (
                        [0] => Operation timed out after 5000 milliseconds with 0 bytes received
                    )

            )

        [error_data] => Array
            (
            )
 * 
 */
function oikb_handle_response( $request ) {
  $response_code = wp_remote_retrieve_response_code( $request );
  bw_trace2( $response_code, "response_code", false );
  echo "response_code: $response_code " . PHP_EOL; 
  return( $response_code ); 
}

/**
 * Retrieve the result
 * 
 * If the response code is 200 ( OK ) return the result
 * If the response code is 302 ( redirect ) set the global $oikb_cookies for subsequent calls and return null
 * Else it's an unexpected result - return null
 * 
 * @param $request - returned from wp_remote_post()
 * @return string result or null 
 */
function oikb_retrieve_result( $request ) {
  $response_code = wp_remote_retrieve_response_code( $request );
  echo $response_code . PHP_EOL;
  bw_trace2( $response_code, "response_code", false );
  if ( $response_code == 200 ) {
    $response = wp_remote_retrieve_body( $request );
    bw_trace2( $response, $response_code );
    if ( empty( $response ) ) {
      $result = null;
    } else {
      $result = maybe_unserialize( $response ); //json_decode( $json );
    }
  } elseif ( $response_code == 302 ) {
    oikb_save_cookies( $request );
    $result = null;
  } else {
    bw_trace2( $response_code, "unexpected response_code" );
    $response_message = wp_remote_retrieve_response_message( $request );
    bw_trace2( $response_message, "response_message" );
    $result = null;
  }
  return( $result );      
}

/**
 * Save the cookies for subsequent calls
 * 
 * @global $oikb_cookies
 */
function oikb_save_cookies( $request ) {
  global $oikb_cookies;
  //bw_trace2( "can we see cookies?", "can we?" );
  $oikb_cookies = bw_array_get( $request, "cookies", null );
  bw_trace2( $oikb_cookies, "cookies", false );
}

/**
 * Test function to visit the bw_setup_menu admin page
 *
 * If the login has worked then the admin page is displayed
 * ASSUMING the plugin is enabled
 * 
 */
function oikb_admin() {
  // $url = "http://qw/wordpress/wp-admin/index.php";
  $url = "http://qw/wordpress/wp-admin/admin.php?page=bw_setup_menu";
  global $oikb_cookies; 
  $args = array( "cookies" => $oikb_cookies );
  $result = oikb_remote_post( $url, $args );
  gobang(); // trying to find where array() comes from on wp-login.php
  print_r( $result );
}



