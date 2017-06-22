<?php // (C) Copyright Bobbing Wide 2016

/**
 * Runs git status against each plugin and theme to check if the git repository has changes that need to be committed
 *
 * Syntax: batch oik-git.php
 * 
 * Run against the parent folder of the git repositories. e.g. wp-content/plugins or wp-content/themes
 */
oik_git_loaded();


/**
 * Function to invoke when oik-git is loaded
 */
 
function oik_git_loaded() {
	oik_require( "libs/oik-git.php", "oik-batch" );
	oik_require( "oik-login.inc", "oik-batch" );
	$git = git();
	$cwd = getcwd();
	$git->check_status( $cwd );
}




