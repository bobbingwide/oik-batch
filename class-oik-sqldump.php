<?php // (C) Copyright Bobbing Wide 2016,2017

/** 
 * Perform MYSQLDUMP of the installation's database
 *
 * - Each database needs to be backed up regularly.
 * - You never know when something bad is going to happen.
 * - Backups should be dated.
 * - They should not override each other
 * - It's probably OK to take one backup per day
 * - Backup file names should reflect the database and the date
 * - Backup files should not be in the same location as the code
 * - They could be zipped
 * - Backups which are no different from the previous days are... unlikely?
 * - This code should be invocable from PHPUnit and other places 
 * - You should be able to restore the database from the backup
 * 
 * @TODO The current code is missing a few options that would enable the DB to be restored. 
 * See https://dev.mysql.com/doc/refman/5.7/en/mysqldump.html#mysqldump-option-file-options
 * 
 */
class OIK_sqldump {

	public $db_host;
	public $db_name;
	private $db_user;
	private $db_password;
	public $dumpfile;
	public $target_dir;
	
											 
	function __construct() {
	
		$this->set_db_fields();	
		$this->set_dumpfile();
		if ( $this->dump_required() ) {
			$this->perform_backup();
		}
	}
	
	function set_db_fields() {
		$this->set_db_host();
		$this->set_db_name();
		$this->set_db_user();
		$this->set_db_password();
	}
	
	function set_db_host() {
		if ( defined( "DB_HOST" ) ) {
			$this->db_host = DB_HOST;
		}
	}
	function set_db_name() {
		if ( defined( "DB_NAME" ) ) {
			$this->db_name = DB_NAME;
		}
	}
	function set_db_user() {
		if ( defined( "DB_USER" ) ) {
			$this->db_user = DB_USER;
		}
	}
	function set_db_password() {
		if ( defined( "DB_PASSWORD" ) ) {
			$this->db_password = DB_PASSWORD;
		}
	}
	
	function set_dumpfile() {
		$this->dumpfile = $this->get_target_dir();
		$this->dumpfile .= $this->db_name;
		$this->dumpfile .= "-";
		$this->dumpfile .= date( "Ymd" );
		$this->dumpfile .= ".sql";
		echo "Dumpfile:" . $this->dumpfile . PHP_EOL;
		
	}
	
	function get_target_dir() {
		//if ( is_dir( ))
		$this->target_dir = "C:/backups-qw/qw/sqldumps/";

		return( $this->target_dir );
	}
	
	/**
	 * Perform a mysqldump
	 */
	function sqldump() {
		$cmd = sprintf( "mysqldump -h %s -u %s --password=%s %s --result_file=%s", $this->db_host, $this->db_user, $this->db_password, $this->db_name, $this->dumpfile	);

		echo $cmd ;
		echo PHP_EOL;
		$output = array();
		$return_var = null;
		
		$lastline = exec( $cmd, $output, $return_var );
		echo $return_var;
		print_r( $output );
		return( $return_var );
	}
	
	/**
	 * Check if an SQL dump is required
	 * 
	 * @return bool - true if the dump file does not already exist or it does but it's empty, false if we're happy.
	 */
	function dump_required() {
		$dump_exists = file_exists( $this->dumpfile );
		if ( $dump_exists ) {
			// Check file size
			$size = filesize( $this->dumpfile );
			if ( !$size ) {
				$dump_exists = false;
			}
		}
		return( !$dump_exists );
	}
	
	/**
	 * Perform the backup using mysqldump
	 *
	 * @TODO Add some additional processing in a future version. e.g. ZIP the output file
	 */
	function perform_backup() {
		$this->sqldump();
		$this->checkdumpfile();
	}
	
	/**
	 * Check the dump file
	 * 
	 * - Check the dump file exists 
	 * - Check iit's a reasonable file size
	 * - @TODO Check it contains some SQL statements that can be used to build a database
	 */
	function checkdumpfile() {
		if ( $this->dump_required() ) {
			echo "Well that didn't work!" . PHP_EOL;
		} else {
			$filesize = filesize( $this->dumpfile );
			if ( $filesize < 1000 ) {
				echo "Need to check the dump file: " . $this->dumpfile . PHP_EOL;	
				echo "It's a bit small: " . $filesize . PHP_EOL;
			}	else {
				echo "Still need to check it'll 'CREATE TABLE's" . PHP_EOL;
			}
		}	
	}

}
