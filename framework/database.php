<?php
/**
 * Tikapot Database Class
 *
 * @author James Thompson
 * @package Tikapot\Framework
 */

require_once(home_dir . "framework/signal_manager.php");
include_once(home_dir . "framework/databases/mysql.php");
include_once(home_dir . "framework/databases/postgres.php");

/**
 * NotConnectedException
 *
 */
class NotConnectedException extends Exception { }
/**
 * QueryException
 *
 */
class QueryException extends Exception { }

SignalManager::register("on_db_query");

/**
 * Database template and primary layer
 *
 * @abstract
 * @package Tikapot\Framework
 */
abstract class Database
{
	private static /** A list of databases */ $dbs = array();
	protected /** This object's link       */ $_link, 
	          /** Are we connected? 	   */ $_connected, 
	          /** My tables 			   */ $_tables, 
	          /** My type 				   */ $_type;
	
	/**
	 * Create a new database connection using the configuration
	 * specified in config.php for the given database name
	 *
	 * @static
	 * @param string $database The name of the database to use
	 * @return Database The database object
	 */
	public static function create($database = "default") {
		if (isset(Database::$dbs[$database]))
			return Database::$dbs[$database];

		global $databases;
		$database_type = $databases[$database]['type'];
		switch ($database_type) {
			case "mysql":
				Database::$dbs[$database] = new MySQL();
				break;
			case "psql":
				Database::$dbs[$database] = new PostgreSQL();
				break;
		}
		if (Database::$dbs[$database]) {
			if (!Database::$dbs[$database]->connect($database))
				return false;
			Database::$dbs[$database]->populate_tables();
			Database::$dbs[$database]->_type = $database_type;
		}
		return Database::$dbs[$database];
	}
	
	/**
	 * Are we connected?
	 * 
	 * @return boolean True if we are connected, false if not
	 */
	public function is_connected() {
		return $this->_connected;
	}
	
	/**
	 * Get the database link
	 *
	 * @return resource The link
	 */
	public function get_link() {
		return $this->_link;
	}
	
	/**
	 * Get a list of tables for this database
	 *
	 * @return array The tables in this database
	 */
	public function get_tables() {
		return $this->_tables;
	}
	
	/**
	 * Get the type of this database
	 *
	 * @return "mysql"|"psql" Returns the type of database this is
	 */
	public function get_type() {
		return $this->_type;
	}
	
	/**
	 * Connects to the database using data from the config file
	 *
	 * @param string $database The name of the database to connect too
	 * @return boolean True if we connected, false if not
	 */
	protected abstract function connect($database);
	
	/**
	 * Run a query on this database
	 *
	 * @param string $query The query to execute
	 * @param array $args Any arguments to be fed into the query
	 * @return resource The result of the mysql_query command
	 */
	public abstract function query($query, $args=array());
	
	/**
	 * Returns the result of the previous query
	 *
	 * @param resource $result The query result to gather data for
	 * @return array The data for the given result
	 */
	public abstract function fetch($result);
	
	/**
	 * Disconnect this database
	 *
	 */
	public abstract function disconnect();
	
	/**
	 * Populates the tables array
	 *
	 * @internal
	 */
	public abstract function populate_tables();
	
	/**
	 * Escapes a given value
	 *
	 * @param string $value The value to escape
	 * @return string The escaped string
	 */
	public abstract function escape_string($value);
	
	/**
	 * Drop a table from the database
	 *
	 * @param string $table The name of the table to drop
	 */
	public abstract function drop_table($table);
	
	/**
	 * Returns the columns of a table
	 *
	 * @param string $table The name of the table
	 * @return array An array of columns
	 */
	public abstract function get_columns($table);
	
	/**
	 * Returns the prefix for tables in this database
	 * 
	 * @return string The prefix for table names
	 */
	 public abstract function get_prefix();
	
	/**
	 * __destruct disconnects the database
	 *
	 * @internal
	 */
	function __destruct() {
		$this->disconnect();
	}
}

?>

