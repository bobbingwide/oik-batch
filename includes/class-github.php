<?php // (C) Copyright Bobbing Wide 2015

/**
 *
 * Class GitHub
 * 
 * Implements an interface to GitHub using the GitHub API
 * {@link https://developer.github.com/v3/}
 *
 * We want to be able to do the following:
 * 
 * - Find out how many downloads there have been for each plugin
 * - Check the plugin versions available
 * - Download and install new plugin version
 * - Download and install a new plugin 
 
 * To achieve this properly we need to create releases in addition to tags
 * And then, when we download the release the folder for the zi file will reflect the 
 * release number. e.g. bbboing-1.8.0 for bbboing release v1.8.0
 * 
 * Ditto for themes.
 
 * See GitHub plugin updater **?**
 */
class GitHub {

	/** 
	 * Store the latest response from the GitHub API
	 */ 
	public $response;
	
	/**
	 * The owner is either a user or an organization
	 */
	public $owner; 
	
	/**
	 * The repository is expected to match the slug of the WordPress plugin or theme
	 */  
	public $repository;

	
	function __construct() {
		$this->owner();
		$this->repository();
		$this->response();
	}	
	
	
	function owner( $owner=null ) {
		$this->owner = $owner;
	}
	
	function repository( $repository=null ) {
		$this->repository = $repository;
	}
	
	function response( $response=null ) {
		print_r( $response );
		$this->response = $response;
	}
	
	/**
	 * Get information for a specific repository
	 *
	 * Store the results in $this->response 
	 
	 * @TODO: Set the header Accept: application/vnd.github.v3+json
	 *
	 * @param string $repository
	 */
	function get_download( $repository="oik" ) {
		$this->repository( $repository );
    $request_url = $this->api();
    $response = bw_remote_get( $request_url ); //, null );
		$this->response( $response );
		//print_r( $response );
	}
	
	/**
	 * Get information about a particular repository
	 * 
	 * GET /repos/:owner/:repository/releases
	 */
	
	function get_download_info( $repository="oik" ) {
	
		$this->repository( $repository );
    $request_url = $this->api( "tags" );
    $response = bw_remote_get( $request_url ); //, null );
		bw_trace2( $response );
		$this->response( $response );
	}
	
	/** 
	 * GET /users/:username/repos
	 */
	function list_repos() {
	
	
	
	}
	
	
	
	/**
	 * Get information for a specific owner
	 */
	function get_owner( $owner ) {
	
	}
	
	/**
	 * Get the API root for a repository ( repos )
	 * 
	 * This needs to work with a different high level thingy: users, repos etc
	 * 
	 *
	 */
	function api( $parms=null ) {
		$api = array();
		$api[] = "https://api.github.com";
		$api[] = "repos";
		$api[] = $this->owner;
		$api[] = $this->repository;
		if ( $parms ) {
			$api[] = $parms;
		}
		$api_url = implode( "/", $api );
		echo $api_url;
		return( $api_url );
	}
	
	function get_download_count() {
		bw_trace2( $this->response );
		
		return( $this->response->assets->download_count );
		
	}
	
}
