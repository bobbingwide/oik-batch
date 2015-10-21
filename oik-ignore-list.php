<?php // (C) Copyright Bobbing Wide 2013, 2014

/**
 * Add files to ignore when the plugin is NOT "wordpress"
 *
 * These are mostly being ignored since they contained OO code or 
 * LOTS of functions we really didn't care much about.
 *
 *
 * 
 */
function _ca_not_wordpress_ignore_list() { 
  $ignore_files = "geshi,cron-svn-pots,";
  $ignore_files .= "extract,ExtractTest,l10n,makepot,mo,po,streams,pot-ext-meta,";
  $ignore_files .= "extension.cache.dbm,extension.cache.mysql,getid3.lib,module.archive.gzip,module.archive.rar";
  return( $ignore_files );
}
 
 
 
/**
 * createapi ignore list - copy of listapis ignore list 
 *
 * Includes wp-config.php since otherwise this can reveal DB connection information
 * It's a bit silly to have this information as a constant! 
 * 
 * @TODO Change so that po, mo and other files are NOT ignored for the "wordpress" plugin
 * 
 *
 */
function _ca_checkignorelist( $file ) {
  gobang();
  $dir = pathinfo( $file, PATHINFO_DIRNAME );
  $ignore_dirs = bw_assoc( bw_as_array( "oik-tunes/getid3," ) );
  $ignore = bw_array_get( $ignore_dirs, $dir, false );
  if ( $ignore ) {
    // echo "Ignoring folder: $dir ". PHP_EOL;
  } else {  
    $filename = pathinfo( $file, PATHINFO_FILENAME );
    // $ignores = bw_assoc( bw_as_array( "oik-activation,oik-docblock,bobbnotwp" ) );
    $ignore_files = "wp-config,oik-activation,oik-docblock,bobbnotwp";
    //if ( $plugin !== "wordpress" ) {
      $ignore_files .= _ca_not_wordpress_ignore_list(); 
    //}
    $ignores = bw_assoc( bw_as_array( $ignore_files ) );
    $ignore = bw_array_get( $ignores, $filename, false );
    if ( $ignore ) {
      echo "Ignoring file: $filename " . PHP_EOL;
    } else { 
      echo "Passing file: $filename " . PHP_EOL ;
      //goban();
    }
  }
  return( $ignore );
}



/**
 * See if the file is in the ignore list
 *
 * @param string $file - relative file name e.g. oik/oik.php 
 * @return bool - true if the file is to be ignored 
 * 
 */  
function _la_checkignorelist( $file ) {
  $dir = pathinfo( $file, PATHINFO_DIRNAME );
  $ignore_dirs = bw_assoc( bw_as_array( "oik-tunes/getid3," ) );
  $ignore = bw_array_get( $ignore_dirs, $dir, false );
  static $ignored = 0;
  if ( $ignore ) {
    // echo "Ignoring folder: $dir ". PHP_EOL;
  } else {  
    $filename = pathinfo( $file, PATHINFO_FILENAME );
    // $ignores = bw_assoc( bw_as_array( "oik-activation,oik-docblock,bobbnotwp" ) );
    $ignore_files = "wp-config,oik-activation,oik-docblock,bobbnotwp,geshi,cron-svn-pots,extract,ExtractTest,l10n,makepot,mo,po,streams,pot-ext-meta,";
    $ignore_files .= ",extension.cache.dbm,extension.cache.mysql,getid3.lib,module.archive.gzip,module.archive.rar";
    $ignores = bw_assoc( bw_as_array( $ignore_files ) );
    $ignore = bw_array_get( $ignores, $filename, false );
    if ( $ignore ) {
      $ignored++;
      echo "Ignoring $ignored file: $filename " . PHP_EOL;
    } else { 
      echo "Passing file: $filename " . PHP_EOL ;
      //goban();
    }
  }
  return( $ignore );
}
