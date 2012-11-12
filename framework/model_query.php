<?php
/*
 * Tikapot Model Query System
 *
 */

require_once(home_dir . "framework/database.php");
require_once(home_dir . "framework/model.php");
require_once(home_dir . "framework/model_cache.php");
require_once(home_dir . "framework/utils.php");

class ModelQueryException extends Exception { }

class ModelQuery implements Iterator, Countable
{
	private $_model, $_query, $_objects, $_count, $_has_run, $_built_queries, $_position, $_sanitized, $_using, $_table, $_can_cache;
	
	/**
	 * $query should conform to the following structure (each line optional):
	 *  (
	 *    WHERE => (COL => Val, "op"=>"OR", COL => (Val, OPER), etc),   Default OPER: = Default OP: AND
	 *    ORDER BY => (COL, (COL, DESC/ASC), etc),          Default: ASC
	 *    GROUP BY => (COL, COL, etc)
	 *    ONLY => (COL, COL, etc),
	 *    LIMIT => num,
	 *    OFFSET => num,
	 *  )
	 */
	public function __construct($model, $query = array(), $using = "default", $table = "", $can_cache = true) {
		if (!$model)
			throw new ModelQueryException($GLOBALS['i18n']['framework']["mqerr1"]);
		$this->_count = false;
		$this->_position = 0;
		$this->_has_run = False;
		$this->_sanitized = False;
		$this->_model = $model;
		$this->_built_queries = array();
		$this->_query = $query;
		$this->_using = $using;
		$this->_table = $table;
		$this->_can_cache = $can_cache;
	}
	
	private function sanitize_column($obj, $col) {
		if ($col == "pk")
			$col = $obj->get_pk_name();
		return $col;
	}
	
	protected function sanitize_query($query) {
		if (!is_array($query))
			throw new ModelQueryException($GLOBALS['i18n']['framework']["mqerr2"]);
		$parsed_query = array();
		if (array_key_exists("WHERE", $query)) {
			$parsed_query["WHERE"] = array(); // Ensure WHERE is always first
		}
		$db = Database::create($this->_using);
		if (!$db)
			return false;
		$class = get_class($this->_model);
		$this->_model->create_table($this->_table);
		foreach ($query as $clause => $criterion) {
			$arr = array();
			if (is_array($criterion)) {
				foreach ($criterion as $col => $val) {
					switch ($clause) {
						case "WHERE":
							if ($col == "op") {
								$arr["op"] = $val;
							} else {
								$col = $this->sanitize_column($this->_model, $col);
								$field = $this->_model->get_field($col);
								if (is_array($val)) {
									if ($val[1] == "IN") {
										$str = "";
										if (is_array($val[0])) {
											$str = "(";
											foreach ($val[0] as $item) {
												if (strlen($str) > 1)
													$str .= ",";
												$str .= $field->sql_value($db, $item);
											}
											$str .= ")";
										} else {
											$str = $db->escape_string($val[0]);
										}
										$arr[$col] = array($str, $val[1]);
									} else {
										$arr[$col] = array($field->sql_value($db, $val[0]), $db->escape_string($val[1]));
									}
								} else {
									$arr[$col] = $field->sql_value($db, $val);
								}
							}
							break;
						case "ORDER BY":
							if (is_array($val))
								$arr[] = array($db->escape_string($this->sanitize_column($this->_model, $val[0])), $db->escape_string($val[1]));
							else
								$arr[] = $this->sanitize_column($this->_model, $db->escape_string($val));
							break;
						case "GROUP BY":
							$arr[] = $this->sanitize_column($this->_model, $db->escape_string($val));
							break;
						case "ONLY":
							$arr[] = $this->sanitize_column($this->_model, $db->escape_string($val));
							break;
					}
				}
			} else {
				if ($clause == "LIMIT" || $clause == "OFFSET") {
					$arr = intVal($criterion);
				}
			}
			$parsed_query[$clause] = $arr;
		}
		return $parsed_query;
	}
	
	/* Allows for lazy evaluation */
	private function _ensure_run() {
		if (!$this->_has_run)
			$this->_run();
	}
	
	protected function _get_object_from_result($result) {
		$class = get_class($this->_model);

		// Try to get from Cache
		if (isset($result['id'])) {
			$obj = ModelCache::get($class, $result['id']);
			if ($obj) {
				return $obj;
			}
		}


		$obj = new $class();
		$obj->load_values($result, true);

		ModelCache::set($obj);

		return $obj;
	}
	
	/* Returns the built query */
	protected function _build_query($selection = "*", $limit = 0, $offset = 0) {
		$query = "";
		$count = 0;
		if (!$this->_sanitized) {
			$this->_query = $this->sanitize_query($this->_query);
			$this->_sanitized = true;
		}
		$op = "AND";
		$counting = starts_with($selection, "COUNT");
		foreach ($this->_query as $clause => $criterion) {
			if ($counting && ($clause === "ORDER BY" || $clause === "GROUP BY"))
				continue;
			if (is_array($criterion)) {
				$count = 0;
				foreach ($criterion as $name => $val) {
					if ($clause === "ONLY") {
						if ($count == 0)
							$selection = "($val";
						else
							$selection .= ", $val";
					} else {
						if ($count == 0)
							$query .= " $clause ";
					}
				
					if ($clause === "WHERE") {
						if ($name == "op") {
							$op = $val;
						} else {
							if ($count > 0) {
								$query .= " $op ";
								$op = "AND";
							}
							$query .= "\"" . $name . "\"";
							if (is_array($val))
								$query .= $val[1] . " " . $val[0];
							else
								$query .= "=" . $val;
						}
					}
				
					if ($clause === "ORDER BY") {
						if ($count > 0)
							$query .= ',';
						if (is_array($val))
							$query .= '"' . $val[0] . '" ' . $val[1];
						else
							$query .= '"' . $val . '" ASC';
					}
					
					if ($clause === "GROUP BY") {
						if ($count == 0)
							$query .= $val;
						else
							$query .= ", " . $val;
					}
				
					$count++;
				}
			}
			if ($clause === "LIMIT")
				$limit = $criterion;
			if ($clause === "OFFSET")
				$offset = $criterion;
			if ($clause === "ONLY")
				$selection .= ")";
		}
		
		$table_name = $this->_table === "" ? $this->_model->get_table_name() : $this->_table;
		$this->_built_queries[$selection] = "SELECT $selection FROM \"" . $table_name . "\"$query".(!$counting && $limit > 0 ? " LIMIT $limit" : "").(!$counting && $offset > 0 ? " OFFSET $offset" : "").";";
		return $this->_built_queries[$selection];
	}
	
	private function _get_query($selection = "*") {
		if (!isset($this->_built_queries[$selection]))
			$this->_built_queries[$selection] = $this->_build_query($selection);
		return $this->_built_queries[$selection];
	}
	
	/* Run this query */
	private function _run() {
		// Reset
		$this->_objects = array();
		$this->_count = 0;
		
		// Get objects
		$db = Database::create($this->_using);
		if (!$db)
			return false;
		$this->_model->create_table();
		$query = $db->query($this->_get_query());
		while($result = $db->fetch($query)) {
			array_push($this->_objects, $this->_get_object_from_result($result));
			$this->_count++;
		}
		
		$this->_has_run = True;
	}
	
	/* Change to a different database */
	public function using($db) {
		$this->_using = $db;
		return $this;
	}
	
	/* Change to a different database */
	public function using_table($table) {
		$this->_table = $table;
		return $this;
	}
	
	/* Does this query set have any elements? */
	public function exists() {
		return $this->count() > 0;
	}

	/**
	 * Disallow cache for this query
	 */
	public function no_cache() {
		$this->_can_cache = false;
		return $this;
	}
	
	/* Find the model matching the query */
	public function find($query) {
		return new static($this->_model, array_merge_recursive($this->_query, array("WHERE" => $query)), $this->_using, $this->_table, $this->_can_cache);
	}
	
	/* Alias for find */
	public function filter($query) {
		return $this->find($query);
	}
	
	/* Random Results */
	public function rand($limit = 1) {
		$potential = $this->all();
		shuffle($potential);
		$array = array_slice($potential, 0, $limit);
		if (count($array) <= 0)
			return null;
		if ($limit == 1)
			return $array[0];
		return $array;
	}
	
	/* Shuffle Results */
	public function shuffle() {
		$potential = $this->all();
		shuffle($potential);
		return $potential;
	}
	
	/* Essentially "or" but thats a php keyword :( */
	public function _or($query) {
		return $this->find(array_merge_recursive(array("op"=>"OR"), $query));
	}
	
	/* Returns the number of objects in this query */
	public function count() {
		if ($this->_count !== false)
			return $this->_count;
		$db = Database::create($this->_using);
		if (!$db)
			return false;
		$query = $db->query($this->_get_query("COUNT(*)"));
		$result = $db->fetch($query);
		$this->_count = $result[0];
		return $result[0];
	}

	/**
	 * Convert this query to JSON
	 * 
	 * @return string JSON representation of this modelquery
	 */
	public function toJSON() {
		$array = array_map(function ($obj) {
			return json_decode($obj->toJSON());
		}, $this->all());
		return json_encode($array);
	}
	
	/* Returns the nth object in this query */
	public function get($n = 0) {
		$this->_ensure_run();

		if (isset($this->_objects[$n])) {
			return $this->_objects[$n];
		}
		
		throw new ModelQueryException($GLOBALS['i18n']['framework']["warn1"] . " $n " . $GLOBALS['i18n']['framework']["mqerr3"]);
	}
	
	/**
	 * Returns the first element if it exists
	 */
	public function first() {
		try {
			$obj = $this->get(0);
			return $obj;
		} catch(Exception $e) {
			return null;
		}
	}
	
	/**
	 * Returns the last element if it exists
	 */
	public function last() {
		$this->_ensure_run();
		try {
			$obj = $this->get(count($this->_objects) - 1);
			return $obj;
		} catch(Exception $e) {
			return null;
		}
	}
	
	/* Returns all objects in this query optionally starting at the nth element */
	public function all($n = 0) {
		$this->_ensure_run();
		
		if ($n == 0)
			return $this->_objects;
		$objects = array();
		for ($i = $n; $i < count($this->_objects); ++$i)
			array_push($objects, $this->_objects[$i]);
		return $objects;
	}
	
	/* Orders elements by (COL, (COL, DESC/ASC), etc) */
	public function order_by($query) {
		$new_query = array();
		if (is_array($query) && (count($query) != 2 || (strtolower($query[1]) !== "asc" && strtolower($query[1]) !== "desc")))
			$new_query["ORDER BY"] = $query;
		else
			$new_query["ORDER BY"] = array($query);
		return new static($this->_model, array_merge_recursive($this->_query, $new_query), $this->_using, $this->_table, $this->_can_cache);
	}
	
	/* Groups elements by (COL, etc) */
	public function group_by($query) {
		$new_query = array();
		if (is_array($query))
			$new_query["GROUP BY"] = $query;
		else
			$new_query["GROUP BY"] = array($query);
		return new static($this->_model, array_merge_recursive($this->_query, $new_query), $this->_using, $this->_table, $this->_can_cache);
	}
	
	/* Limit the number of elements */
	public function limit($limit) {
		$new_query = $this->_query;
		$new_query["LIMIT"] = $limit;
		return new static($this->_model, $new_query, $this->_using, $this->_table, $this->_can_cache);
	}
	
	/* Offset the limit of the number of elements */
	public function offset($offset) {
		$new_query = $this->_query;
		$new_query["OFFSET"] = $offset;
		return new static($this->_model, $new_query, $this->_using, $this->_table, $this->_can_cache);
	}

	/* Iterator methods */
	public function rewind() {
		$this->_position = 0;
	}
	
	public function current() {
		$this->_ensure_run();
		return $this->_objects[$this->_position];
	}
	
	public function key() {
		return $this->_position;
	}
	
	public function next() {
		++$this->_position;
	}
	
	public function valid() {
		$this->_ensure_run();
		return isset($this->_objects[$this->_position]);
	}
	/* End of Iterator methods */
}

