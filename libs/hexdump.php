<?php

/**
 * Returns the hex dump of the string
 *
 * Helps you to find those pesky control characters
 *
 * I first wrote this code in PHP in Sep 2013. It echoed the output.
 * Now I need it to be returned as a string, so rewriting as oik_hexdump().
 * Quite a few people look for something similar. https://stackoverflow.com/questions/1057572/how-can-i-get-a-hex-dump-of-a-string-in-php
 */

if ( php_sapi_name() == "cli") {

  if ( $argc < 2 ) {
    $string = "blah di blah\n\rdi blah di blah\ndi blah di blah\r etcetera!";
  } else { 
    $string = $argv[1];  
  }
	hexdump( "PHP_EOL:" . PHP_EOL . "!" );
	hexdump( $string );
	hex26( $string );
}

function hexdump( $string ) {
	echo oik_hexdump( $string );
}
  

function oik_hexdump( $string ) {
	$hexdump = null;
	$count = strlen( $string );
	$hexdump .= $count;
  $hexdump .= PHP_EOL;
  $lineo = "";
  $hexo = "";
  for ( $i = 1; $i <= $count; $i++ ) {
    $ch = $string[$i-1];
    if ( ctype_cntrl ( $ch ) ) {
      $lineo .= ".";
    } else { 
      $lineo .= $ch; 
    }
    $hexo .= bin2hex( $ch );
    $hexo .=  " ";
    if (  0 == $i % 20 ) {
      $hexdump .= $lineo . " " . $hexo . PHP_EOL;
      $lineo = "";
      $hexo = "";
    }
  }
  $hexdump .= substr( $lineo. str_repeat(".", 20 ), 0, 20 );
  $hexdump .= " ";
  $hexdump .= $hexo;
  $hexdump .= PHP_EOL;
  return $hexdump;
}


function hex26( $string ) {
  $string = substr( $string . "             ",0, 13 );

  $count = strlen( $string );
  echo $count;
  echo PHP_EOL;
  $lineo = "";
  $hexo = "";
  for ( $i = 1; $i <= $count; $i++ ) {
    $ch = $string[$i-1];
    if ( ctype_cntrl ( $ch ) ) {
      $lineo .= ".";
    } else { 
      $lineo .= $ch; 
    }
    $hexo .= bin2hex( $ch );
    //$hexo .=  " ";
    if (  0 == $i % 20 ) {
      echo $lineo . " " . $hexo . PHP_EOL;  
      $lineo = "";
      $hexo = "";
    }
  }
  echo substr( $lineo. str_repeat(".", 20 ), 0, 20 );
  echo " "; 
  echo $hexo;
  echo PHP_EOL;
}



