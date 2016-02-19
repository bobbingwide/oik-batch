<?php // (C) Copyright Bobbing Wide 2015
/**
 * git functions
 * 
 * Library: git
 * Provides: git
 * Depends: oik_boot, bwtrace
 * Shareable: No - since it expects to be part of the oik-batch plugin.
 * Conflicts: 
 *
 * This file should eventually be a shared library file providing an interface to git, and potentially GitHub, from PHP routines.
 * I'm sure there are many instances about. But I've not looked
 *
 */

/**
 * Return the single instance of the Git class
 *
 */
function git() {
	if ( !class_exists( "Git" ) ) {
		oik_require( "includes/class-git.php", "oik-batch" );
	}
	$git = Git::instance();
	return( $git );
}
