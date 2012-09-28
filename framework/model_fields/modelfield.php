<?php
/**
 * Tikapot Model Field
 *
 * @author James Thompson
 * @package Tikapot\Framework\ModelFields
 */
 
/**
 * A simple Field Validation exception, occurs when the field cannot validate
 */
class FieldValidationException extends Exception { }

/**
 * ModelFields are given to Models and relate to a column in the database
 */
abstract class ModelField
{
	protected static /** The Database Type for this field (e.g. VARCHAR) */ $db_type = "unknown";
	protected 	/** Our default value */ $default_value = "",
				/** Our current value */ $value = "",
				/** Our parent model (if any) */ $model = NULL,
				/** The name of this field */ $name = "",
				/** Have we been set? */ $set = False,
				/** Is this field hidden from forms? */ $hidden = False;
	public 	/** An array of errors */ $errors = array(),
			/** Metadata */ $_extra = "",
			/** Should we hide from modelqueries? */ $hide_from_query = False;

	/**
	 * Construct a new field
	 * 
	 * @param string $default Our default value
	 * @param string $_extra  Metadata
	 */
	public function __construct($default = "", $_extra = "") {
		$this->default_value = $default;
		$this->value = $this->default_value;
		$this->_extra = $_extra;
	}
	
	/**
	 * __toString returns this field's value as a string
	 *
	 * @return string
	 */
	public function __toString() {
		return "" . $this->value;
	}
	
	/**
	 * Get Formfield returns a formfield object for this field
	 *
	 * @abstract
	 * @param string $name The name of the field
	 * @return FormField A FormField subclass object complete with names and values
	 */
	public abstract function get_formfield($name);
	
	/**
	 * Have we been set?
	 * 
	 * @return boolean True if we have been set
	 */
	public function is_set() {
		return $this->set;
	}
	
	/**
	 * Setup the modelfield
	 *
	 * @internal
	 * @param  Model $model The model object we are a child of
	 * @param  string $name  Our field name
	 * @return null
	 */
	public function setup($model, $name) {
		$this->set_model($model);
		$this->set_name($name);
	}
	
	/**
	 * Set the value of this form
	 * 
	 * @param mixed $value The new value
	 */
	public function set_value($value) {
		$this->set = True;
		$this->value = $value;
	}
	
	/**
	 * Get our current value
	 * 
	 * @return mixed Our current value
	 */
	public function get_value() {
		return $this->value;
	}
	
	/**
	 * Returns the raw value, free of processing
	 * 
	 * @return mixed Raw value
	 */
	public final function raw_value() {
		return $this->value;
	}
	
	/**
	 * Returns a value suitable for use in a form
	 * 
	 * @return string Suitable form value
	 */
	public function get_form_value() {
		return $this->get_value();
	}
	
	/**
	 * Sets our name
	 * 
	 * @param string $name The new name of this field
	 */
	public function set_name($name) {
		$this->name = $name;
	}
	
	/**
	 * Get our name
	 * 
	 * @return string The name of this field
	 */
	public function get_name() {
		return $this->name;
	}
	
	/**
	 * Tells the modelfield whether or not it should be hidden
	 * @param boolean $hidden Should be hidden?
	 */
	public function set_hidden($hidden) {
		$this->hidden = $hidden;
	}
	
	/**
	 * Are we a hidden field?
	 * 
	 * @return boolean True if we are hidden, otherwise false
	 */
	public function get_hidden() {
		return $this->hidden;
	}
	
	/**
	 * Set the model we are related too
	 * 
	 * @param Model $model Our model
	 */
	public function set_model($model) {
		$this->model = $model;
	}
	
	/**
	 * Returns the model we are a part of
	 * 
	 * @return Model The model
	 */
	public function get_model() {
		return $this->model;
	}
	
	/**
	 * Get out database type
	 * 
	 * @return string The database type
	 */
	public function get_db_type() {
		return static::$db_type;
	}
	
	/**
	 * SQL value converts a given value into an appropriate value for an SQL query to this object
	 *
	 * @internal
	 * @param Database $db Database object
	 * @param string $val The value to convert to an SQL format
	 * @return string The value as an SQL-ready string
	 */
	public function sql_value($db, $val = NULL) {
		$val = ($val === NULL) ? $this->value : $val;
		return (strlen("" . $val)  > 0) ? $db->escape_string($val) : "NULL";
	}

	/**
	 * Returns our default value
	 * 
	 * @return mixed Default value
	 */
	public final function get_default() {
		return $this->default_value;
	}
	
	/**
	 * Reset the field to it's default value
	 * 
	 * @return null
	 */
	public function reset() {
		$this->value = $this->default_value;
	}
	
	/**
	 * Validate the field
	 *
	 * @param mixed $val Value to use
	 * @return boolean True if successful, false if not
	 */
	public abstract function validate($val = NULL);

	/**
	 * Returns this field's part of a table creation query
	 *
	 * @internal
	 * @param Database $db Database object
	 * @param string $name Field's name
	 * @param string $table_name Table name
	 * @return string An SQL query string
	 */
	public function db_create_query($db, $name, $table_name) {
		return "\"" . $name . "\" " . $this->get_db_type();
	}
	
	/**
	 * This allows subclasses to provide end-of-statement additions such as constraints
	 * 
	 * @param  Database $db         Database
	 * @param  string $name       Name of the field
	 * @param  string $table_name Name of the table
	 * @return string             Queries to send to the database
	 */
	public function db_post_create_query($db, $name, $table_name) {
		return "";
	}
	
	/**
	 * This allows subclasses to provide extra, separate queries on createdb such as sequences. These are put before the create table query.
	 * 
	 * @param  Database $db         Database
	 * @param  string $name       Name of the field
	 * @param  string $table_name Name of the table
	 * @return string             Queries to send to the database
	 */
	public function pre_model_create($db, $name, $table_name) {
		return "";
	}
	
	/**
	 * This allows subclasses to provide extra, separate queries on createdb such as sequences. These are put after the create table query.
	 * 
	 * @param  Database $db         Database
	 * @param  string $name       Name of the field
	 * @param  string $table_name Name of the table
	 * @return string             Queries to send to the database
	 */
	public function post_model_create($db, $name, $table_name) {
		return "";
	}
	
	/**
	 * This recieves pre-save signal from it's model.
	 * 
	 * @param  Model $model  The model calling
	 * @param  boolean $update Is it an update?
	 * @return null
	 */
	public function pre_save($model, $update) {}
	
	/**
	 * Is this a pk field?.
	 * 
	 * @return boolean True
	 */
	public function is_pk_field() {
		return false;
	}
	
	/**
	 * Could this have a relation to another model (e.g. FK Fields)
	 * 
	 * @param  Model $model A model to check against
	 * @return boolean        True if this field relates to the given model, otherwise false.
	 */
	public function relatesTo($model) {
		return false;
	}
}

