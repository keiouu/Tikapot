<?php
/**
 * Tikapot PostgreSQL Database Extension Class
 *
 * @author James Thompson
 * @package Tikapot\Framework\Databases
 */

require_once(home_dir . "framework/database.php");

/**
 * PostgreSQL Database Layer
 *
 * @package Tikapot\Framework\Databases
 */
class PostgreSQL extends Database
{
	private /** The name of this database */ $_dbname, /** Prefix for tables */ $_prefix;
	
	/**
	 * Connects to the database using data from the config file
	 *
	 * @param string $database The name of the database to connect too
	 * @return boolean True if we connected, false if not
	 */
	protected function connect($database) {
		global $databases;
		$settings = $databases[$database];
		$portStr = $settings['port'];
		
		if (strlen($portStr) > 0) {
			$portStr = " port=" . $portStr;
		}
		
		$this->_dbname = $settings['name'];
		$connect_str = "host=" . $settings['host'].$portStr;
		$connect_str .= " user=" . $settings['username'];
		$connect_str .= " password=" . $settings['password'];
		$connect_str .= " dbname=" . $settings['name'];
		$connect_str .= " connect_timeout=" . $settings['timeout'];
		$this->_link = pg_connect($connect_str);
		$this->_connected = $this->_link ? true : false;
		
		if (!$this->_connected) {
			throw new NotConnectedException($GLOBALS['i18n']['framework']["dberr1"]);
		}
		
		$this->_prefix = isset($settings["prefix"]) ? $settings["prefix"] : "";
		
		return $this->_connected;
	}
	
	/**
	 * Run a query on this database
	 *
	 * @param string $query The query to execute
	 * @param array $args Any arguments to be fed into the query
	 * @return resource The result of the pg_query_params command
	 */	
	public function query($query, $args=array()) {
		$id = Profiler::start("psql_query");
		SignalManager::fire("on_db_query", array($query, $args));
		if (debug_show_queries)
			console_log($query);
		if (!$this->_connected) {
			throw new NotConnectedException($GLOBALS['i18n']['framework']["dberr2"]);
		}
		if (count($args) > 0)
			$res = pg_query_params($this->_link, $query, $args);
		else
			$res = pg_query($this->_link, $query);
		if (strpos($query, "ATE TABLE") > 0 || strpos($query, "OP TABLE") > 0)
			$this->populate_tables();
		Profiler::end("psql_query", $id);
		return $res;
	}
	
	/**
	 * Returns the result of the previous query
	 *
	 * @param resource $result The query result to gather data for
	 * @return array The data for the given result
	 */
	public function fetch($result) {
		if (!$this->_connected) {
			throw new NotConnectedException($GLOBALS['i18n']['framework']["dberr2"]);
		}
		return pg_fetch_array($result, NULL, PGSQL_BOTH);
	}
	
	/**
	 * Disconnect this database
	 *
	 */
	public function disconnect() {
		if ($this->_connected) {
			$this->_connected = !pg_close($this->_link);
		}
	}
	
	/**
	 * Populates the tables array
	 *
	 * @internal
	 */
	public function populate_tables() {
		$this->_tables = array();
		$query = $this->query("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public';");
		while($result = $this->fetch($query))
			array_push($this->_tables, $result["table_name"]);
	}

	/**
	 * Returns the columns of a table
	 *
	 * @param string $table The name of the table
	 * @return array An array of columns
	 */
	public function get_columns($table) {
		$arr = array();
		$query = $this->query('SELECT * from "' . $this->escape_string($table) . '";');
		$i = pg_num_fields($query);
		for ($j = 0; $j < $i; $j++)
			$arr[pg_field_name($query, $j)] = pg_field_type($query, $j);
		return $arr;
	}
	
	/**
	 * Escapes a given value
	 *
	 * @param string $value The value to escape
	 * @return string The escaped string
	 */
	public function escape_string($value) {
		return pg_escape_string($this->_link, $value);
	}
	
	/**
	 * Drop a table from the database
	 *
	 * @param string $table The name of the table to drop
	 */
	public function drop_table($table) {
		$this->query('DROP TABLE "' . $this->escape_string($table) . '" CASCADE;');
	}
	
	/**
	 * Returns the prefix for tables in this database
	 * 
	 * @return string The prefix for table names
	 */
	 public function get_prefix() {
	 	return $this->_prefix;
	 }
}

?>

