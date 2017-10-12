<?php // (C) Copyright Bobbing Wide 2016,2017

class OIK_innodb {

	public $results;
	public $counts;

	/**
	 * Constructor for OIK_innodb
	 */
	function __construct() {
		$this->reset();
	}
	
	/**
	 * Load and count the tables by engine
	 */
	function reset() {
		$this->query_table_engines();
		$this->count_tables();
	}
	
	/**
	 * Convert any MyISAM tables to InnoDB
	 */
	function maybe_convert() {
		$myisam_table_count = $this->query_count( "MyISAM" );
		if ( $myisam_table_count ) {
			$this->convert_engine();
			$this->reset();
		}	
	}

	/**
	 * Query the engine for each table in the table_schema
	 *
	 * 
	 * select table_name, engine from information_schema.tables where table_schema = 'herb_wp373';
	 */
	function query_table_engines() {
		global $wpdb;
		$query = $wpdb->prepare( "select table_name, engine from information_schema.tables where table_schema = %s", DB_NAME );
		bw_trace2( $query, "query" );
		$this->results = $wpdb->get_results( $query );
		return( $this->results );
	}

	/**
	 * Count the different engines for the tables in the database
	 *
	 */
	function count_tables() {	
		$this->counts = array();
		foreach ( $this->results as $result ) {
			if ( !isset( $this->counts[ $result->engine] ) ) {
				$this->counts[ $result->engine ] = 0;
			}
			$this->counts[ $result->engine ] += 1;
		}
		bw_trace2( $this->counts, "Counts for: " . DB_NAME );
	}
	
	/**
	 * Return the number of tables for the engine
	 * 
	 * @param string $engine InnoDB, MyISAM, etc
	 * @return integer count
	 */ 
	function query_count( $engine="MyISAM" ) {
		$count = bw_array_get( $this->counts, $engine, 0 );
		return( $count );
	}

	/**
	 * Convert tables from one engine to another
	 *
	 * We assume that the WordPress user has the authority to alter tables
	 *
	 * 
	 * @param string $from_engine 
	 * @param string $to_engine
	 * 
	 */
	function convert_engine( $from_engine="MyISAM", $to_engine="INNODB" ) {
		global $wpdb;
		foreach ( $this->results as $result ) {
			if ( $from_engine == $result->engine ) {
				$query = $wpdb->prepare( "ALTER TABLE %s engine=%s", DB_NAME . "." . $result->table_name, $to_engine );
				$query = sprintf( "ALTER TABLE %s engine=%s", $result->table_name, $to_engine );
				echo $query;
				echo PHP_EOL;
				$rows_affected = $wpdb->query( $query );
			}
		}
	}
	
}	
