<?php
/**
 * Tikapot MySQL Database Extension Class
 *
 * @author James Thompson
 * @package Tikapot\Framework\Databases
 */

require_once(home_dir . "framework/database.php");

/**
 * MySQL Database Layer
 * Please note: MySQL is currently not supported! THis should be considered "Risky"
 *
 * @package Tikapot\Framework\Databases
 */
class MySQL extends Database
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
			$portStr = ":" . $portStr;
		}
		
		$this->_dbname = $settings['name'];
		$this->_link = mysql_connect($settings['host'] . $portStr, $settings['username'], $settings['password'], true);
		
		if ($this->_link) {
			$this->_connected = mysql_select_db($settings['name'], $this->_link);
		} else {
			$this->_connected = false;
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
	 * @return resource The result of the mysql_query command
	 */
	public function query($query, $args=array()) {
		$id = Profiler::start("mysql_query");
		SignalManager::fire("on_db_query", array($query, $args));
		if (debug_show_queries)
			console_log($query);
		if (!$this->_connected) {
			throw new NotConnectedException($GLOBALS['i18n']['framework']["dberr2"]);
		}
		$vars = array();
		foreach ($args as $arg)
			array_push($vars, mysql_real_escape_string($arg));
		$query = sprintf($query, $vars);
		$res = mysql_query($query, $this->_link);
		if (strpos($query, "ATE TABLE") > 0 || strpos($query, "OP TABLE") > 0)
			$this->populate_tables();
		Profiler::end("mysql_query", $id);
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
		return mysql_fetch_array($result, MYSQL_BOTH);
	}
	
	/**
	 * Disconnect this database
	 *
	 */
	public function disconnect() {
		if ($this->_connected) {
			$this->_connected = !mysql_close($this->_link);
		}
	}
	
	/**
	 * Populates the tables array
	 *
	 * @internal
	 */
	public function populate_tables() {
		$this->_tables = array();
		$query = $this->query("SHOW TABLES;");
		while($result = $this->fetch($query))
			array_push($this->_tables, $result[0]);
	}

	/**
	 * Returns the columns of a table
	 *
	 * @param string $table The name of the table
	 * @return array An array of columns
	 */
	public function get_columns($table) {
		$arr = array();
		$query = $this->query("SELECT COLUMN_NAME, DATA_TYPE FROM information_schema.COLUMNS WHERE TABLE_NAME='%s' AND TABLE_SCHEMA='%s';", array($table, $this->_dbname));
		while($col = $this->fetch($query))
			$arr[$col["COLUMN_NAME"]] = $col["DATA_TYPE"];
		return $arr;
	}
	
	/**
	 * Escapes a given value
	 *
	 * @param string $value The value to escape
	 * @return string The escaped string
	 */
	public function escape_string($value) {
		return mysql_real_escape_string($value, $this->_link);
	}
	
	/**
	 * Drop a table from the database
	 *
	 * @param string $table The name of the table to drop
	 */
	public function drop_table($table) {
		$this->query("DROP TABLE %s;", array($table));
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

