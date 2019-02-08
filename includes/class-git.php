<?php // (C) Copyright Bobbing Wide 2015,2016

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

	 public $message = null;
	
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
	 * Constructor for Git
	 */
	public function __construct() {
		$this->commands();
		$this->cwd = null;
	}
	
	/**
	 * Return some Git command aliases
	 *
	 * {@link http://stackoverflow.com/questions/8533202/list-files-in-local-git-repo}
	 * suggests using `git ls-files` or the more detailed `git ls-tree --full-tree -r HEAD`
	 *
	 * {@link http://stackoverflow.com/questions/957928/is-there-a-way-to-get-the-git-root-directory-in-one-command}
	 */
	public function commands() {
		$this->commands = array( "status" => "status -s" 
													 , "changed" => "diff --name-only"
													 , "list" => "ls-files"
													 , "current" => "log --oneline -1"
													 , "gitdir" => "rev-parse --show-toplevel"
													 , "cdup" => "rev-parse --show-cdup"
			, "remote" => "remote -v"
			, "pull" => "pull"
			, "fetch" => "fetch"
													 ); 
	}


	/**
	 * @param $source_dir
	 */
	public function has_dot_git( $source_dir ) {
		$has_dot_git = null;
		$dot_git = $source_dir . "/.git";
		$dot_git = str_replace( "/", DIRECTORY_SEPARATOR, $dot_git );
		//echo $dot_git;
		if ( file_exists( $dot_git ) && is_dir( $dot_git ) ) {
			$has_dot_git = $source_dir;
		} else {
			//echo "nope <br />";
		}
		return $has_dot_git;
	}
	
	/**
	 * Check directory is a git repository
	 *
	 * We want to make sure we're in the right place.
	 * `git status -s` will work if the directory is not a repo itself but is in a folder which is part of a repo
	 * We need to use "gitdir" to find the root directory of the Git repo
	 * 
	 * @param string $source_dir
	 * @return string same as $source_dir if this is a Git repo
	 *
	 */
	public function is_git( $source_dir ) {
		$this->echo( "is_git: " . $source_dir . PHP_EOL );
		$changed = $this->chdir( $source_dir );
		if ( $changed ) {
			$this->source_dir = $source_dir;	
		
			$cdup = $this->command( "cdup" );
			//echo "CDUP: $cdup" . PHP_EOL;
			
			//$gitdir = $this->command( "gitdir" );
			//echo "gitdir: $gitdir" . PHP_EOL;
			$changed = $this->reset_dir( $changed );
			if ( $this->is_repo() ) {
				if ( $cdup ) {
					$this->echo( "Git repo is not in the root" . PHP_EOL );
					$source_dir = null;
				}else {
					$this->source_dir = $source_dir;
				}
			} else {
				$source_dir = null;
			}	
		} else {
			$source_dir = null;
		}
		
		$this->echo( "returning: " .  $source_dir	. PHP_EOL );
		return( $source_dir );
	}
	
	/**
	 * Determine if this is a repo.
	 * 
	 * 
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
	 *
	 * @param string $source_dir
	 * @return string previous directory for reset
	 */
	public function chdir( $source_dir ) {
		$changed = is_dir( $source_dir );
		if ( $changed ) {
			$this->cwd = getcwd();
			$changed = chdir( $source_dir );
		} else {
			$this->cwd = null;
		}
		return( $this->cwd );
	}
	
	/**
	 * Change back to the previous directory
	 *
	 * @TODO: Confirm this doesn't do strange things with symlinked dirs
	 *
	 */
	public function reset_dir( $cwd=null ) {
		if ( $cwd ) {
			$this->cwd = $cwd;
		}
		if ( $this->cwd ) {
			chdir( $this->cwd );
			$this->echo( "Reset dir to: " . $this->cwd . PHP_EOL );
		}
		$this->cwd = null;
	}
	
	/**
	 * Perform a git command against the repository
	 */
	public function command( $command="status -s", $parms=null ) {
		$cmd = $this->actual_command( $command, $parms );
		$prevdir = $this->chdir( $this->source_dir );
		//echo "Is source_dir correctly set: {$this->source_dir}?" . PHP_EOL;
		$this->execute( $cmd );
		
		//echo "Result: " . $this->result . PHP_EOL;
		$this->reset_dir( $prevdir );
		$this->command = $command;
		$this->parms = $parms;
		//echo "Result: " . $this->result . PHP_EOL;
		return( $this->result ); 
	}
	
	/**
	 * Return the actual git command to use
	 *
	 * No longer defaulting to "status -s" but the command passed
	 *
	 */
	public function actual_command( $command=null, $parms=null ) {
		if ( null === $command ) {
			$command = "status -s";
		}
		$actual = bw_array_get( $this->commands, $command, $command );
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
		$this->echo( $cmd . PHP_EOL );
		$result = shell_exec( "$cmd 2>&1" );
		bw_trace2( $result, "Result:");
		bw_trace2( $this->hexdump( $result), "Hexdump:" );
		$result = str_replace( ["\t", "\n", "\r" ], " ", $result );
		$this->result = trim( $result );

		//echo "execute result:" . $result . ":" . PHP_EOL;
		return( $this->result );
	}
	
	/**
	 * Return the file list as an array
	 *
	 * @return array of results
	 */
	public function result_as_array() {
		$result_array = explode( " ", $this->result );
		//print_r( $this->result );
		//print_r( $result_array );
		return( $result_array );
	}
	
	 
	/**
	 * Check the status of all the sub-folders in the directory
	 *
	 * List each sub-directory
	 * If it's a git directory
	 * check the status
	 * 
	 *
	 * @param string $directory 
	 */
	function check_status( $directory ) {
		$this->echo( "Looking for Git repos in $directory" . PHP_EOL );
		$files = scandir( $directory );
		foreach ( $files as $file ) {
			
			if ( $file != "." && $file !== ".." && $file !== ".git" ) {
			
				if ( is_dir( $file ) ) {
					$this->echo( "$file is a directory" . PHP_EOL );
					$source = $this->is_git( $directory . "/" . $file );
					if ( $source ) {
						// Can't quite remember what this next line's purpose was.
						//echo "git clone https://github.com/bobbingwide/$file" . PHP_EOL;
						chdir( $source );
						$result = $this->command( "status", null );
						$this->echo( $result );
						$this->echo(  PHP_EOL );
						if ( $result ) {	
							oikb_get_response( "Continue?", true );
						}
					} else {
						$this->echo( "$source is not a git folder" . PHP_EOL );
					}
					chdir( $directory );
					
					
					
				} else {
					//echo "$file is not a directory";
				}
			}
		}
	}
	/**
	 * Safely echo in batch environment
	 *
	 * Stores echoed output for JSON requests
	 * @param $string
	 */
	function echo( $string=PHP_EOL ) {
		if ( "cli" === php_sapi_name() ) {
			echo $string;
			if ( $string != PHP_EOL ) {
				echo PHP_EOL;
			}
		} else {
			$this->message .= $string;
		}
	}

	/**
	 * Finds out if the repository needs pulling
	 *
	 * - Assumes that $source_dir is set.
	 *
	 * @return string
	 */
	function maybe_needs_pulling() {
		$result = $this->command();
		if ( strlen( $result ) ) {
			$result .= "<br />Not safe to pull";
		}
		return $result;
	}

	function check_remote() {
		$remote = null;
		$remote_result = $this->command( "remote" );
		$remotes = explode( " ", $remote_result );
		//echo implode( "!", $remotes );
		if ( count( $remotes) >= 3 ) {
			if ( $remotes[2] === "(fetch)" ) {
				$remote = $remotes[ 1 ];
			}
		}
		return $remote;

	}

	function hexdump( $string ) {
		if ( !function_exists( "oik_hexdump") ) {
			oik_require( "libs/hexdump.php", "oik-batch" );
		}
		if ( function_exists( "oik_hexdump" ) ) {
			$string = oik_hexdump( $string );
		}
		return $string;


	}

}
