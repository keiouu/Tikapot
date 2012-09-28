<?php
/**
 * Tikapot Boolean Field
 *
 * @author James Thompson
 * @package Tikapot\Framework\ModelFields
 */

require_once(home_dir . "framework/model_fields/modelfield.php");

/**
 * Boolean Field
 *
 */
class BooleanField extends ModelField
{
	protected static /** Database Type */ $db_type = "boolean";
	
	/**
	 * __construct override
	 *
	 * @param boolean $default Default value for the field
	 */
	public function __construct($default = false) {
		parent::__construct($default);
	}
	
	/**
	 * Get the value of this field
	 *
	 * @return boolean True or False
	 */
	public function get_value() {
		return $this->value === true || strtolower($this->value) === 'true' || strtolower($this->value) === "t" || $this->value === "1" || $this->value === 1;
	}
	
	/**
	 * __toString returns this field's value as a string
	 *
	 * @return string "True" or "False"
	 */
	public function __toString() {
		return $this->get_value() ? "True" : "False";
	}
	
	/**
	 * Get Formfield returns a formfield object for this field
	 *
	 * @param string $name The name of the field
	 * @return CheckedFormField A CheckedFormField subclass object complete with name and value
	 */
	public function get_formfield($name) {
		return new CheckedFormField($name, $this->get_value());
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
		$val = ($val === NULL) ? $this->get_value() : $val;
		return ($val) ? "true" : "false";
	}

	/**
	 * Validate the field
	 *
	 * @param mixed $val Value to use
	 * @return boolean True if successful, false if not
	 */
	public function validate($val = NULL) {
		$val = ($val === NULL) ? $this->value : $val;
		$valid = $val === true || $val === false || $val === 0 || $val === 1 || $val === NULL || strtolower($val) === 'true' || strtolower($val) === "t" || $val === "1" || strtolower($val) === 'false' || strtolower($val) === "f" || $val === "0";
		if (!$valid)
			array_push($this->errors, $GLOBALS['i18n']['framework']["fielderr1"] . " " . $val);
		return $valid;
	}
}

