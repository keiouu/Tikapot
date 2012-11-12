<?php
/**
 * Tikapot Model System
 * 
 * @author James Thompson
 * @package Tikapot\Framework
 */

require_once(home_dir . "framework/database.php");
require_once(home_dir . "framework/model_query.php");
require_once(home_dir . "framework/model_cache.php");
require_once(home_dir . "framework/model_fields/init.php");
require_once(home_dir . "framework/models.php");
require_once(home_dir . "framework/forms.php");
require_once(home_dir . "framework/utils.php");

/**
 * A Validation Exception
 * 
 * @package Tikapot\Framework
 * @subpackage Exceptions
 */
class ValidationException extends Exception { }

/**
 * A Table Validation Exception
 * 
 * @package Tikapot\Framework
 * @subpackage Exceptions
 */
class TableValidationException extends ValidationException { }

/**
 * Thrown when a Model Exists
 * 
 * @package Tikapot\Framework
 * @subpackage Exceptions
 */
class ModelExistsException extends Exception {}

/**
 * Thrown when there is an error related to a field
 * 
 * @package Tikapot\Framework
 * @subpackage Exceptions
 */
class FieldException extends Exception {}

/**
 * This should be used by any object attempting to mimic a Model
 * 
 * @package Tikapot\Framework
 * @subpackage Interfaces
 */
interface ModelInterface
{
	/**
	 * Returns true if this object has a field of the given name
	 *
	 * @param string $name The name of the field to test
	 */
	public function has_field($name);
	
	/**
	 * Returns the value of the field
	 *
	 * @param string $name The name of the field to get
	 */
	public function get_field($name);
	
	/**
	 * Sets the field
	 *
	 * @param string $name The name of the field to set
	 * @param string $value The field's new value
	 */
	public function set_field($name, $value);
	
	/**
	 * Reset the field to default
	 *
	 * @param string $name The name of the field to reset
	 */
	public function reset_field($name);
	
	/** Saves the model to the database */
	public function model_save();
}

/**
 * Model's are a Database abstraction layer, designed to give you an easy way to
 * store information without having to write SQL.
 * 
 * @package Tikapot\Framework
 */
abstract class Model
{
	/** Is this object from a database? @internal */
	private $from_db = False;
	/** Is this object valid? @internal */
	private $_valid_model = False;
	/** List of fields. @internal */
	protected $fields = array();
	/** List of safe fields. @internal */
	protected $safe_fields = array();
	/** Error bucket. @internal */
	protected $errors = array();
	/** The database this object uses. @internal */
	protected $_using = "default";
	/** This model's version. @internal */
	protected $_version = "1.0";
	/** This object's table name override. @internal */
	protected $_table_override = null;
	
	/**
	 * A simple constructor
	 * 
	 */
	public function __construct() {
		$this->_valid_model = True;
		$this->add_field("id", new PKField(22, 0, True));
	}
	
	/**
	 * Returns a string representation of this object
	 */
	public function __toString() {
		return "" . $this->pk;
	}
	
	/**
	 * Get a nice display name for this class
	 *
	 * @param string $class_override Override the default class name for this class
	 * @return string A user-friendly name for this class
	 */
	public static function _display_name($class_override = "") {
		$name = $class_override;
		if ($name === "")
			$name = get_class(static::_get_temp_object());
		$lower_name = strtolower($name);
		return isset($GLOBALS['i18n']['framework']["model_" . $lower_name]) ? $GLOBALS['i18n']['framework']["model_" . $lower_name] : $name;
	}
	
	/**
	 * See _display_name(...)
	 * 
	 * @param string $override Override the default class name for this class
	 * @deprecated 1.2 This method will be replaced by "_display_name($class_override)"
	 */
	public static function model_display_name($override = "") {
		console_deprecation("model_display_name", "_display_name");
		return static::_display_name($override);
	}
	
	/**
	 * Returns the ContentType object for this model
	 *
	 * @return ContentType The ContentType object of this model
	 */
	public static function _content_type() {
		return ContentType::of(static::_get_temp_object());
	}
	
	/**
	 * See _content_type()
	 * 
	 * @deprecated 1.2 This method will be replaced by "_content_type()"
	 */
	public static function get_content_type() {
		console_deprecation("get_content_type", "_content_type");
		return static::_content_type();
	}
	
	/**
	 * Set the database to use with this model.
	 *
	 * @param string $db The name of the database, relates to the config.php $databases array entry
	 */
	public function set_db($db) {
		$this->_using = $db;
	}
	
	/**
	 * Get the name of the database this model uses
	 *
	 * @return string The name of the database this model uses
	 */
	public function get_db() {
		return $this->_using;
	}
	
	/**
	 * See get_db()
	 * 
	 * @deprecated 1.2 - This method will be removed and replaced by get_db()
	 */
	public function getDB() {
		console_deprecation("getDB", "get_db");
		return $this->get_db();
	}
	
	/**
	 * Is this model from a database?
	 *
	 * @return boolean Returns true if this object has been loaded from a database, or false if it has not been saved yet
	 */
	public function from_db() {
		return $this->from_db;
	}
	
	/**
	 * See from_db()
	 * 
	 * @deprecated 1.2 - This method will be removed and replaced by from_db()
	 */
	public function fromDB() {
		return $this->from_db();
	}
	
	/**
	 * This method is provided to create temporary models that cannot be saved to a database.
	 *
	 * @internal
	 * @return self Returns a new, "invalid", object of this model.
	 */
	public static function _get_temp_object() {
		$obj = new static();
		$obj->_valid_model = False;
		return $obj;
	}
	
	/**
	 * See _get_temp_object()
	 * 
	 * @deprecated 1.2 This method will be replaced by "_get_temp_object()"
	 */
	public static function get_temp_instance() {
		console_deprecation("get_temp_instance", "_get_temp_object");
		return static::_get_temp_object();
	}
	
	/**
	 * Example:
	 * <code>
	 * $objects = Model::objects();
	 * foreach ($objects as $object) { ... }
	 * </code>
	 * 
	 * @return ModelQuery A ModelQuery object, with every object available.
	 */
	public static function objects() {
		return new ModelQuery(static::_get_temp_object());
	}
	
	/**
	 * Allows custom primary keys.
	 *
	 * @return string The name of this model's primary key field
	 */
	public function get_pk_name() {
		foreach ($this->fields as $name => $field)
			if ($field->is_pk_field())
				return $name;
	}
	
	/**
	 * See get_pk_name()
	 * 
	 * @deprecated 1.2 This method will be replaced by "get_pk_name()"
	 */
	public function _pk() {
		console_deprecation("_pk", "get_pk_name");
		return $this->get_pk_name();
	}
	
	/**
	 * Load a list of values into this object's fields.
	 * This is usually internal but may be useful in some cases
	 *
	 * @param array $array Array of values to load, should follow the format: array("field_name" => "value", ...)
	 * @param boolean $fromDB Are these values from a database?
	 */
	public function load_values($array, $fromDB = false) {
		foreach ($this->fields as $name => $field) {
			if (array_key_exists($name, $array)) {
				$val = $array[$name];
				if (is_array($val))
					$val = $val[0];
				$field->set_value($val);
			}
		}
		
		if ($fromDB) {
			$this->from_db = True;
		}
	}

	/**
	 * Convert this model to JSON
	 * 
	 * @return string JSON representation of this model
	 */
	public function toJSON() {
		$array = array();
		foreach ($this->fields as $name => $field) {
			$value = $field->get_value();
			if ($value instanceof Model || $value instanceof ModelQuery) {
				$value = json_decode($value->toJSON());
			}
			$array[$name] = $value;
		}
		return json_encode($array);
	}
	
	/**
	 * See load_values(...)
	 * 
	 * @param array $result Values to load
	 * @deprecated 1.2 This method will be replaced by "load_values($array, true)"
	 */
	public function load_query_values($result) {
		console_deprecation("load_query_values", "load_values");
		$this->load_values($result, true);
	}
	
	/**
	 * Shortcut for ::objects()->find(...)
	 *
	 * @see ModelQuery::find
	 * @param array $query Array of values to load, should follow the format: array("field_name" => "value", "field_name" => array("value", "=,>,<,etc"), ...)
	 * @return ModelQuery A ModelQuery object containing the found objects
	 */
	public static function find($query) {
    	if (is_array($query))
			return static::objects()->find($query);
		else
			return static::objects()->find(array("pk" => $query));
	}
	
	/**
	 * Shortcut for ::objects()->find(...)->get(0)
	 * 
	 * @throws ModelExistsException If there are no objects matching the query
	 * @throws ModelQueryException If there are multiple objects matching the query
	 * @see ModelQuery::find
	 * @see ModelQuery::get
	 * @param mixed $arg Either an Array of values to load, or a primary key value
	 * @return mixed The found object
	 */
	public static function get($arg = 0) {
		$results = static::find($arg);
		if ($results->count() == 0)
			throw new ModelExistsException($GLOBALS['i18n']['framework']["noobjexist"]);
		if ($results->count() > 1)
			throw new ModelQueryException($GLOBALS['i18n']['framework']["multiobjexist"]);
		return $results->get(0);
	}
	
	/**
	 * Creates a new object of this type, loads the given values and saves it to the database
	 * 
	 * @throws ModelQueryException If there is an error creating the object
	 * @see Model::load_values
	 * @param array $args An array of values to set for the new object
	 * @return mixed|null The created object
	 */
	public static function create($args = array()) {
		if (count($args) <= 0)
			return Null;
		try {
			$obj = new static();
			$obj->load_values($args);
			if ($obj->save())
				return $obj;
			return Null;
		} catch (Exception $e) {
			throw new ModelQueryException($GLOBALS['i18n']['framework']["error1"] . $e->getMessage());
		}
		return Null;
	}

	/**
	 * Searches for an object matching the giving arguments, creating one if it cant find one
	 * 
	 * @param mixed $args An Array of values to look for, or load into a new object
	 * @return array Array(object, created (true|false))
	 */
	public static function get_or_create($args = 0) {
		$obj = NULL;
		$created = False;
		try {
			$obj = static::get($args);
		}
		catch (ModelExistsException $e) {
			$obj = static::create($args);
			$created = True;
		}
		return array($obj, $created);
	}

	/**
	 * Searches for an object matching the giving arguments, similar to ::get but
	 * this method doesnt throw any Exceptions
	 * 
	 * @see Model::get
	 * @param mixed $args An Array of values to look for
	 * @return mixed|null The found object, or null
	 */
	public static function get_or_ignore($args) {
		try {
			$obj = static::get($args);
			return $obj;
		}
		catch (Exception $e) {
			return null;
		}
	}
	
	/**
	 * Searches for an object matching the giving arguments, then deletes them.
	 * 
	 * @see Model::get
	 * @param mixed $args An Array of values to look for
	 * @return boolean True if the object was deleted, false if not
	 */
	public static function delete_or_ignore($args) {
		try {
			$obj = static::get($args);
			return $obj->delete();
		}
		catch (Exception $e) {
			return false;
		}
	}
	
	/**
	 * Add a new field to this model.
	 *
	 * @param string $name The name of the field
	 * @param ModelField $type The field object
	 * @param boolean $hidden Should this field be hidden from forms?
	 */
	protected function add_field($name, $type, $hidden = False) {
		$type->set_hidden($hidden);
		if ($type->is_pk_field()) {
			$new_fields = array();
			$new_fields[$name] = $type;
			foreach ($this->fields as $name => $field)
				if (!$field->is_pk_field())
					$new_fields[$name] = $field;
			$this->fields = $new_fields;
		} else {
			$this->fields[$name] = $type;
		}
		$type->setup($this, $name);
	}
	
	/**
	 * Add a new safe field, where a "safe" field ignores any
	 * validation warnings.
	 * Warning! This is a significant security risk if the field is user-modifiable.
	 * 
	 * @param string $name The name of the field
	 * @param ModelField $type The field object
	 * @param boolean $hidden Should this field be hidden from forms?
	 */
	protected function add_safe_field($name, $type, $hidden = False) {
		$this->add_field($name, $type, $hidden);
		$this->safe_fields[] = $name;
	}
	
	/**
	 * Sets the value of _valid_model
	 * 
	 * @internal
	 * @param boolean $val The new value of Model::_valid_model
	 */
	public function set_valid($val) {
		$this->_valid_model = $val;
	}
	
	/**
	 * See set_valid(...)
	 *
	 * @deprecated 1.2 This method will be replaced by "set_valid($val)"
	 * @param boolean $val The new value of Model::_valid_model
	 */
	public function setValid($val) {
		console_deprecation("setValid", "set_valid");
		$this->set_valid($val);
	}
	
	/**
	 * Get the table name for this object, allows individual objects to "route" themselves
	 * to other tables.
	 *
	 * @param Database $db The new value of Model::_valid_model
	 * @return string The name of the database table this object uses
	 */
	public function get_table_name($db = NULL) {
		$db = $db === NULL ? Database::create($this->_using) : $db;
		return $db->get_prefix() . ($this->_table_override === null ? strtolower(get_class($this)) : $this->_table_override);
	}
	
	/**
	 * Set the table name for this object, allows individual objects to "route" themselves
	 * to other tables.
	 *
	 * @param string $name The name of the database table this object should use
	 */
	public function set_table_name($name) {
		$this->_table_override = $name;
	}
	
	/**
	 * The model version is useful for database upgrades etc
	 *
	 * @return string The version number of this model
	 */
	public function get_version() {
		return $this->_version;
	}
	
	/**
	 * Returns an array of all fields used by this object
	 *
	 * @return array An array containing the fields of this object
	 */
	public function get_fields() {
		return $this->fields;
	}
	
	/**
	 * A Method for: $object->_$name
	 *
	 * @param string $name The name of the field to return
	 * @return ModelField A ModelField object for the field of the given name
	 */
	public function get_field($name) {
		return $this->__get("_" . $name);
	}
	
	/**
	 * Does this object have a given field?
	 *
	 * @param string $name The name of the field to check
	 * @return boolean True if this model had a field of the specified name, false if not
	 */
	public function has_field($name) {
		return isset($this->fields[$name]) || (starts_with($name, "_") && isset($this->fields[substr($name, 1)]));
	}
	
	/**
	 * A Method for: $object->_$name = $value;
	 *
	 * @param string $name The name of the field to set
	 * @param string $value The new value of the field
	 * @return mixed Returns $value for convenience
	 */
	public function set_field($name, $value) {
		$this->__set($name, $value);
		return $value;
	}
	
	/**
	 * Get a Form object containing appropriate elements for this object,
	 * as well as values for the fields.
	 *
	 * @see Form
	 * @param string $action [Optional] The action for the form
	 * @param string $method [Optional] The method for the form (GET/POST)
	 * @return Form A pre-built form for this object
	 */
	public function get_form($action = "", $method = "POST") {
		$fields = array();
		foreach($this->get_fields() as $name => $field) {
			if (!$field->get_hidden())
				$fields[$name] = $field->get_formfield($name);
		}
		return new Form(array(
			new Fieldset(prettify($this->_display_name()), $fields),
		), $action, $method);
	}
	
	/**
	 * This allows you to directly access fields of this model.
	 * Append an underscore to get the field instead of its value.
	 * 
	 * For example:
	 * <code>
	 * $object = new Model();
	 * $value = $object->some_field_name;
	 * $field = $object->_some_field_name;
	 * </code>
	 *
	 * @param string $name The name of the field
	 */
	public function __get($name) {
		$is_safe = in_array($name, $this->safe_fields);
		if ($name == "pk")
			$name = $this->get_pk_name();
		if ($name == "_pk")
			$name = "_" . $this->get_pk_name();
		if (isset($this->fields[$name])) {
			$val = $this->fields[$name]->get_value();
			if (method_exists($this, "__get_" . $name)) {
				$method = "__get_" . $name;
				$val = $this->$method($val);
			}
			return ($is_safe || !is_string($val)) ? $val : htmlentities($val);
		}
		if (starts_with($name, "_")) {
			$base_name = substr($name, 1);
			if (isset($this->fields[$base_name]))
				return $this->fields[$base_name];
		}
		
		throw new FieldException($GLOBALS['i18n']['framework']["fieldne"] . ' ' . get_class(new static()) . ":$name.");
	}
	
	/**
	 * This allows you to directly set fields of this model.
	 * 
	 * For example:
	 * <code>
	 * $object = new Model();
	 * $object->some_field_name = "hello";
	 * </code>
	 *
	 * @param string $name The name of the field
	 * @param mixed $value The new value for the field
	 */
	public function __set($name, $value) {
		if (method_exists($this, "__set_" . $name)) {
			$method = "__set_" . $name;
			$value = $this->$method($value);
		}
		
		if ($name == "pk")
			$name = $this->get_pk_name();
		
		if (isset($this->fields[$name])) {
			$this->fields[$name]->set_value($value);
		} else {
			throw new FieldException($GLOBALS['i18n']['framework']["fieldne"] . " '$name'.");
		}
	}
	
	/**
	 * This allows you to directly check if a model field exists.
	 * 
	 * For example:
	 * <code>
	 * $object = new Model();
	 * if (isset($object->some_field_name)) {}
	 * </code>
	 *
	 * @param string $name The name of the field
	 */
	public function __isset($name) {
		if ($name == "pk")
			return True;
		return isset($this->fields[$name]) && $this->fields[$name]->is_set();
	}
	
	/**
	 * This allows you to directly unset a model field.
	 * 
	 * For example:
	 * <code>
	 * $object = new Model();
	 * unset($object->some_field_name);
	 * </code>
	 *
	 * @param string $name The name of the field
	 */
	public function __unset($name) {
		if ($name == "pk")
			return;
		if ($this->__isset($name))
			$this->fields[$name]->reset();
	}
	
	/**
	 * Does this model relate to the given object?
	 * A relationship is defined as:
	 * * Any field having a link to the given object (e.g. FK fields)
	 *
	 * @param object $object The object to test
	 * @return boolean True if the models are related, false of not
	 */
	public function relates_to($object) {
		foreach ($this->fields as $name => $field) {
			if ($field->relatesTo(get_class($object)))
				return true;
		}
		return false;
	}
	
	/**
	 * See relates_to(...)
	 *
	 * @deprecated 1.2 This method will be replaced by "relates_to($object)"
	 * @param object $object The object to test
	 */
	public function relatesTo($object) {
		console_deprecation("relatesTo", "relates_to");
		return $this->relates_to($object);
	}
	
	/**
	 * Get any objects this object relates to.
	 *
	 * @see Model::relates_to
	 * @param object $object The object to check against
	 * @return array An array containing all objects that relate to this object.
	 */
	public function get_related_objects($object) {
		foreach ($this->fields as $name => $field) {
			if ($field->relatesTo($object)) {
				return $this->find(array($name => $object));
			}
		}
		return array();		
	}
	
	/**
	 * See get_related_objects(...)
	 *
	 * @deprecated 1.2 This method will be replaced by "get_related_objects($object)"
	 * @param object $object The object to check against
	 */
	public function getRelatedObjects($object) {
		return $this->get_related_objects($object);
	}
	
	/**
	 * Returns an SQL query to create this object
	 *
	 * @internal
	 * @param Database $db The database to use
	 * @param string $override_table Use a different table name from the default one?
	 */
	public function db_create_query($db, $override_table = "") {
		$table_name = $override_table === "" ? $this->get_table_name() : $override_table;
		$post_scripts = "";
		$SQL = "CREATE TABLE \"" . $db->escape_string($table_name) . "\" (";
		$i = 0;
		foreach ($this->get_fields() as $name => $field) {
			if ($i > 0) $SQL .= ", ";
			$SQL .= $field->db_create_query($db, $name, $table_name);
			$i++;
			$post_query = $field->db_post_create_query($db, $name, $table_name);
			if (strlen($post_scripts) > 0 && strlen($post_query) > 0)
				$post_scripts .= ", ";
			if (strlen($post_query) > 0)
				$post_scripts .= $post_query;
		}
		if (strlen($post_scripts) > 0)
			$SQL .= ", " . $post_scripts;
		$SQL .= ");";
		
		return $SQL;
	}
	
	/**
	 * Gather extra SQL statements to execute before the main CREATE query.
	 *
	 * @internal
	 * @param Database $db The database to use
	 */
	public function db_create_extra_queries_pre($db) {
		$table_name = $this->get_table_name();
		$extra_scripts = array();
		foreach ($this->get_fields() as $name => $field) {
			$query = $field->pre_model_create($db, $name, $table_name);
			if (strlen($query) > 0)
				array_push($extra_scripts, $query);
		}
		return $extra_scripts;
	}
	
	/**
	 * Gather extra SQL statements to execute after the main CREATE query.
	 *
	 * @internal
	 * @param Database $db The database to use
	 */
	public function db_create_extra_queries_post($db) {
		$table_name = $this->get_table_name();
		$extra_scripts = array();
		foreach ($this->get_fields() as $name => $field) {
			$query = $field->post_model_create($db, $name, $table_name);
			if (strlen($query) > 0)
				array_push($extra_scripts, $query);
		}
		return $extra_scripts;
	}
	
	/**
	 * Returns true if the table for this object exists in the database
	 *
	 * @internal
	 * @param string $override_name Use a different table name from the default one?
	 */
	public function table_exists($override_name = "") {
		$db = Database::create($this->_using);
		if ($db)
			return in_array($override_name === "" ? $this->get_table_name() : $override_name, $db->get_tables());
	}
	
	/**
	 * Creates this model's database table
	 *
	 * @internal
	 * @param string $override_name Use a different table name from the default one?
	 */
	public function create_table($override_name = "") {
		$table_name = $override_name === "" ? $this->get_table_name() : $override_name;
		if (!$this->table_exists($table_name)) {
			$db = Database::create($this->_using);
			if (!$db)
				return false;
			
			// Run pre-create scripts
			foreach($this->db_create_extra_queries_pre($db) as $query)
				$db->query($query);
			// Create the table
			$res = $db->query($this->db_create_query($db, $table_name));
			// Run post-create scripts
			foreach($this->db_create_extra_queries_post($db) as $query)
				$db->query($query);
					
			$this->_content_type(); // Spawn content type
			return $res;
		}
		return true;
	}
	
	/**
	 * Verifies that the table structure in the database is up-to-date
	 * NOTE: Currently only detects field name changes, not type changes
	 *
	 * @internal
	 */
	public function verify_table() {
		$this->create_table();
		$db = Database::create($this->_using);
		if (!$db)
			return false;
		$table_name = $this->get_table_name();
		$fields = $this->get_fields();
		$columns = $db->get_columns($table_name);
		foreach ($columns as $column => $type) {
			if (!array_key_exists($column, $fields))
				throw new TableValidationException($column . " ".$GLOBALS['i18n']['framework']["nolongerpart"]." " . $table_name);
		}
		foreach ($fields as $field => $type) {
			if (!array_key_exists($field, $columns))
				throw new TableValidationException($field . " ".$GLOBALS['i18n']['framework']["shdin"]." " . $table_name);
		}
		return True;
	}
	
	/**
	 * Validates the current object.
	 * Override this method to provide custom validation mechanisms.
	 *
	 * @return boolean True is the validation succeeded, false if not
	 */
	public function validate() {
		$this->errors = array();
		foreach ($this->get_fields() as $field_name => $field) {
			if (!$field->validate()) {
				$this->errors = array_merge($this->errors, $field->errors);
				return False;
			}
		}
		return True;
	}

	/**
	 * Provides validation errors
	 *
	 * @return array An array containing all errors
	 */
	public function get_errors() {
		return $this->errors;
	}

	/**
	 * Provides validation errors
	 *
	 * @return string A string containing all errors
	 */
	public function get_error_string() {
		$str = "";
		foreach ($this->get_errors() as $error) {
			if (strlen($str) > 0)
				$str .= "\n";
			$str .= $error;
		}
		return $str;
	}
	
	/**
	 * Returns the INSERT query for this object
	 *
	 * @internal
	 * @param Database $db The database to use
	 */
	public function insert_query($db) {
		$keys = "";
		$values = "";
		foreach ($this->get_fields() as $field_name => $field) {
			if ($field->hide_from_query)
				continue;

			$this->on_update_changed($field_name, "", $field->get_value());

			$is_safe = in_array($field_name, $this->safe_fields);
			if (strlen($keys) > 0) {
				$keys .= ", ";
				$values .= ", ";
			}
			$keys .= "\"" . $field_name . "\"";
			$val = $field->sql_value($db);
			$val = $is_safe ? $val : strip_tags($val);
			if (strlen($val) <= 0)
				$val = "''";
			$values .= $val;
		}
		$extra = "";
		if ($db->get_type() == "psql")
			$extra = " RETURNING \"" . $this->get_pk_name() . "\"";
		return "INSERT INTO \"" . $this->get_table_name() . "\" (" . $keys . ") VALUES (" . $values . ")" . $extra . ";";
	}
	
	/**
	 * Hook, called for each changed field when save() causes an update.
	 *
	 * @param string $var The name of the variable that was changed
	 * @param string $old The old value
	 * @param string $new The new value
	 */
	public function on_update_changed($var, $old, $new) {}
	
	/**
	 * Returns the UPDATE query for this object
	 *
	 * @internal
	 * @param Database $db The database to use
	 */
	public function update_query($db) {
		$old_object = static::find($this->pk)->no_cache()->using($this->get_db())->using_table($this->get_table_name())->all();
		if (count($old_object) == 0) {
			console_error($GLOBALS['i18n']['framework']['modelerr1'] . " " . $this->get_table_name() . " (" . $this->pk . ")");
			return "";
		}
		$old_object = $old_object[0];
		
		$query = "UPDATE \"" . $this->get_table_name() . "\" SET ";
		$go = False;
		foreach ($old_object->get_fields() as $name => $field) {
			if ($field->hide_from_query)
				continue;
			$is_safe = in_array($name, $this->safe_fields);
			$new_val = $this->fields[$name];
			if (strval($field->sql_value($db)) !== strval($new_val->sql_value($db))) {
				if ($go)
					$query .= ", ";
				$this->on_update_changed($name, $field->get_value(), $new_val->get_value());
				$val = $new_val->sql_value($db);
				$val = $is_safe ? $val : strip_tags($val);
				$query .= '"' . $name . '"=' . $val;
				$go = True;
			}
		}
		$query .= " WHERE " . $this->get_pk_name() . "=" . $db->escape_string($this->pk);
		if ($go)
			return $query;
		return ""; // Nothing to do
	}
	
	/**
	 * Hook, called before a save()
	 *
	 * @return boolean True to allow the save to continue, false to kill the save()
	 */
	public function pre_save() { return true; }
	
	/**
	 * Hook, called after a save()
	 *
	 * @param string|int $pk The object's primary key
	 */
	public function post_save($pk) {}
	
	/**
	 * Hook, called before a save() where the object isnt currently in the database
	 */
	public function pre_create() {}
	
	/**
	 * Hook, called after a save() where the object isnt currently in the database
	 */
	public function post_create() {}
	
	/**
	 * Hook, called before a save() where the object is currently in the database
	 */
	public function pre_update() {}
	
	/**
	 * Hook, called after a save() where the object is currently in the database
	 */
	public function post_update() {}
	
	/**
	 * Saves the object to the database
	 *
	 * @return string|int|boolean The object's new PK, or false if things went wrong and we couldnt save
	 */
	public function save() {
		if (!$this->pre_save()) {
			if (!$this->_valid_model) {
				throw new ValidationException($GLOBALS['i18n']['framework']["saveerror2"] . " " . get_class($this));
			}
			return false;
		}
		if (!$this->validate()) {
			throw new ValidationException($GLOBALS['i18n']['framework']["error1"] . get_class($this) . $GLOBALS['i18n']['framework']["saveerror3"] . "<br />" . $this->get_error_string());
		}

		$this->create_table();
		$db = Database::create($this->_using);
		if (!$db) {
			throw new Exception("No Database could be found!");
			return false;
		}
		
		$query = "";
		
		foreach ($this->get_fields() as $name => $field) {
			$field->pre_save($this, $this->from_db);
		}
		
		$query_res = false;
		if (!$this->from_db) {
			$this->pre_create();
			$query_res = $db->query($this->insert_query($db));
			$id = 0;
			if ($db->get_type() == "psql") {
				$row = $db->fetch($query_res);
				$id = $row[0];
			}
			if ($db->get_type() == "mysql")
				$id = mysql_insert_id();
			$this->pk = intval($id);
			$this->from_db = true;
			$this->post_create();
		}
		else {
			$this->pre_update();
			$query = $this->update_query($db);
			if (strlen($query) > 0)
				$query_res = $db->query($query);
			$this->post_update();
		}
		
		$this->post_save($this->pk);

		ModelCache::set($this);

		return $query_res === false ? false : $this->pk;
	}

	/**
	 * Returns the DELETE query for this object
	 *
	 * @internal
	 * @param Database $db The database to use
	 */
	public function delete_query($db) {
		return "DELETE FROM \"" . $this->get_table_name() . "\" WHERE \"". $this->get_pk_name() ."\"='" . $this->pk . "';";
	}

	/**
	 * Delete this object.
	 *
	 * @return boolean True on success, False on failure
	 */
	public function delete() {
		if (!$this->from_db)
			return false;
		$db = Database::create($this->_using);
		if (!$db)
			return false;

		ModelCache::delete($this);

		return $db->query($this->delete_query($db)) == true;
	}
	
	/**
	 * Upgrade this object.
	 * Override this object to provide upgrade mechanisms.
	 *
	 * @param Database $db The database to use
	 * @param string $old_version The old version
	 * @param string $new_version The new version
	 * @return boolean True on success, False on failure
	 */
	public function upgrade($db, $old_version, $new_version) {
		// Update the CT
		$ct = $this->get_content_type();
		$ct->version = $new_version;
		$ct->save();
		return true;
	}
}
