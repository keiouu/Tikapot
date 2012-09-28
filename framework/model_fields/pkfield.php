<?php
/**
 * Tikapot Primary Key Field
 *
 * @author James Thompson
 * @package Tikapot\Framework\ModelFields
 */

require_once(home_dir . "framework/model_fields/bigintfield.php");

/**
 * A PrimaryKey field
 */
class PKField extends BigIntField
{
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
		$val = parent::db_create_query($db, $name, $table_name);
		if ($db->get_type() == "mysql")
			$val .= " PRIMARY KEY";
		return $val;
	}
	
	/**
	 * Get Formfield returns a Formfield object for this field
	 *
	 * @param string $name The name of the field
	 * @return HiddenFormField A HiddenFormField subclass object complete with name and value
	 */
	public function get_formfield($name) {
		return new HiddenFormField($name, $this->get_value());
	}
	
	/**
	 * This allows subclasses to provide end-of-statement additions such as constraints
	 * 
	 * @internal
	 * @param Database $db Database object
	 * @param string $name Field's name
	 * @param string $table_name Table name
	 * @return  string A CONSTRAINT string if using psql, or an empty string.
	 */
	public function db_post_create_query($db, $name, $table_name) {
		if ($db->get_type() == "psql")
			return 'CONSTRAINT '.$db->escape_string($table_name).'_pkey PRIMARY KEY ("'.$db->escape_string($name).'")';
		return '';
	}
	
	/**
	 * Provides a CREATE INDEX string for the database subsystem
	 * 
	 * @internal
	 * @param Database $db Database object
	 * @param string $name Field's name
	 * @param string $table_name Table name
	 * @return  string The Database query for this post create call
	 */
	public function post_model_create($db, $name, $table_name) {
		$index = $db->escape_string('index_'.$table_name.'_'.$name);
		$db->query('DROP INDEX IF EXISTS '.$index.';');
		return 'CREATE INDEX '.$index.' ON "'.$db->escape_string($table_name).'" ("'.$db->escape_string($name).'");';
	}
	
	/**
	 * Is this a pk field?.
	 * 
	 * @return boolean True
	 */
	public function is_pk_field() {
		return true;
	}
}

