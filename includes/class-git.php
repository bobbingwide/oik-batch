<?php // (C) Copyright Bobbing Wide 2015

/**
 *
 * Class Git
 * 
 * Implements a simple interface to Git
 */
class Git {

	/** 
	 * Result of the latest git command executed
	 */
	public $result = null;
	
	/**
	 * Latest command executed
	 */
	public $command = null;
	 
	/** 
	 * Parms to latest command executed
	 */
	public $parms = null;
	 
	/**
	 * Source_dir
	 */
	public $source_dir = null;
	
	/**
	 * Current working directory
	 */
	public $cwd;
	
	/**
	 * $commands
	 */
	 public $commands = array();
	
	/**
	 * @var Git - the true instance
	 */
	private static $instance;

	/**
	 * Return a single instance of this class
	 *
	 * @return object 
	 */
	public static function instance() {
		if ( !isset( self::$instance ) && !( self::$instance instanceof Git ) ) {
			self::$instance = new Git;
		}
		return self::$instance;
	}
	
	/**
	 * 
	 */
	public function __construct() {
		$this->commands();
	}
	
	/**
	 * Return the appropriate git command
	 *
	 * {@link http://stackoverflow.com/questions/8533202/list-files-in-local-git-repo}
	 * suggests using `git ls-files` or the more detailed `git ls-tree --full-tree -r HEAD`
	 *
	 */
	
	public function commands() {
		$this->commands = array( "status" => "status -s" 
													 , "changed" => "diff --name-only"
													 , "list" => "ls-files"
													 ); 
	}
	
	/**
	 * Return the directory for the git repository
	 *
	 */
	public function is_git( $source_dir ) {
		echo "is_git: " . $source_dir . PHP_EOL;
		$changed = $this->chdir( $source_dir );
		if ( $changed ) {	
			$success = $this->command();
			$changed = $this->reset_dir();
			if ( $this->is_repo() ) {
				$this->source_dir = $source_dir;
			} else {
				$source_dir = null;
			}	
		} else {
			$source_dir = null;
		}
		echo "returning: " .  $source_dir	. PHP_EOL;
		return( $source_dir );
	}
	
	/**
	 * Determine if this is a repo
	 * 
	 */
	public function is_repo() {
		$result = $this->result;
		if ( 0 === strpos( $result, "fatal:" ) ) {
			$repo = false;
		} else {
			$repo = true;
		}
		return( $repo );
	}
	
	/**
	 * Change to the directory if it exists
	 */
	public function chdir( $source_dir ) {
		$changed = is_dir( $source_dir );
		if ( $changed ) {
			$this->cwd = getcwd();
			$changed = chdir( $source_dir );
		}
		return( $changed );
	}
	
	/**
	 * Change back to the previous directory
	 *
	 * @TODO: Confirm this doesn't do strange things with symlinked dirs
	 *
	 */
	public function reset_dir() {
		if ( $this->cwd ) {
			chdir( $this->cwd );
		}
		$this->cwd = null;
	}
	
	/**
	 * Perform a git command against the repository
	 */
	public function command( $command="status -s", $parms=null ) {
		$cmd = $this->actual_command( $command, $parms );
		$this->chdir( $this->source_dir );
		$this->execute( $cmd );
		$this->reset_dir();
		$this->command = $command;
		$this->parms = $parms;
		return( $this->result ); 
	}
	
	/**
	 * Return the actual git command to use
	 *
	 */
	public function actual_command( $command, $parms=null ) {
		$actual = bw_array_get( $this->commands, $command, "status -s" );
		$cmd = "git $actual $parms";
		return( $cmd );
	}
	
	/**
	 * Execute a command
	 *
	 * Perform the git command and store the result of the command in this->result
	 * The result can then be interpreted by the calling routine
	 * invoking further methods
	 *
	 *
	 * @param string $cmd the command to execute
	 * @return string the result from the command
	 *
	 */
	public function execute( $cmd ) {
		echo $cmd . PHP_EOL;
		$result = shell_exec( "$cmd 2>&1" );
		$this->result = trim( $result );
		//echo "execute result:" . $result . ":" . PHP_EOL;
		return( $result );
	}
	
	/**
	 * Return the file list as an array
	 *
	 * @return array of results
	 */
	public function result_as_array() {
		$result_array = explode( "\n", $this->result );
		//print_r( $this->result );
		//print_r( $result_array );
		return( $result_array );
	}
}
