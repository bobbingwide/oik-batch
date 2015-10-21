<?php // (C) Copyright Bobbing Wide 2015

/**
 * Command Line Interface functions
 *
 * This file should eventually be a shared library file
 * containing some of the common routines used in the oik-zip, oik-tip and other routines
 * including those that deal with directory changes in symlinked environments
 * and other that return responses to the calling routines and make decisions based on them
 */
 
 

/**
 * Prompt to check if the process should be continued
 *
 * This routine does not make any decisions.
 * If you want to stop you just press Ctrl-Break.
 */
if ( !function_exists( 'docontinue' ) ) { 
function docontinue( $plugin ) {
   echo PHP_EOL;
    echo "Continue? $plugin ";
    $stdin = fopen( "php://stdin", "r" );
    $response = fgets( $stdin );
		$response = trim( $response );
    fclose( $stdin );
    return( $response );
}
}

