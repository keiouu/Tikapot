<?php
/**
 * Tikapot Char Field
 *
 * @author James Thompson
 * @package Tikapot\Framework\ModelFields
 */

require_once(home_dir . "framework/model_fields/modelfield.php");
require_once(home_dir . "framework/form_fields/init.php");

/**
 * Char Field
 */
class CharField extends ModelField
{
	protected static /** Database Type */ $db_type = "VARCHAR";
	protected /** Maximum Character Length for this field */ $max_length = 0;
	
	/**
	 * __construct override
	 *
	 * @param integer $max_length The maximum allowed length of this field's value
	 * @param string $default The default value for this field
	 */
	public function __construct($max_length = 0, $default = "") {
		parent::__construct($default);
		$this->max_length = $max_length;
	}
	
	/**
	 * Get Formfield returns a formfield object for this field
	 *
	 * @param string $name The name of the field
	 * @return CharFormField A CharFormField subclass object complete with name and value
	 */
	public function get_formfield($name) {
		return new CharFormField($name, $this->get_value(), array("extra" => 'maxlength="'.$this->max_length.'"'));
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
		if (strlen($val) <= 0)
			return "''";
		return "'" . $db->escape_string($val) . "'";
	}
	
	/**
	 * Validate the field
	 *
	 * @param mixed $val Value to use
	 * @return boolean True if successful, false if not
	 */
	public function validate($val = NULL) {
		$val = ($val === NULL) ? $this->get_value() : $val;
		if ($this->max_length > 0 && strlen($val) > $this->max_length) {
			array_push($this->errors, $GLOBALS['i18n']['framework']["fielderr2"]);
			return False;
		}
		return True;
	}
	
	/**
	 * Returns this field's part of a table creation query
	 *
	 * @internal
	 * @param Database $db Database object
	 * @param string $name Field's name
	 * @param string $table_name Table name
	 */
	public function db_create_query($db, $name, $table_name) {
		$extra = "";
		if ($this->max_length > 0)
			$extra .= " (" . $this->max_length . ")";
		if (strlen($this->default_value) > 0)
			$extra .= " DEFAULT '" . $this->default_value . "'";
		return "\"" . $name . "\" " . $this::$db_type . $extra;
	}
}

