<?php
/**
 * Tikapot Numeric Field
 *
 * @author James Thompson
 * @package Tikapot\Framework\ModelFields
 */

require_once(home_dir . "framework/model_fields/modelfield.php");

/**
 * Numeric Field allows decimal points
 */
class NumericField extends ModelField
{
	protected static /** The Database Type for this field (e.g. NUMERIC) */ $db_type = "NUMERIC";
	private /** Precision (2dp etc) */ $precision = 0;
	
	/**
	 * Constructor
	 * 
	 * @param string $default   Default value
	 * @param string $precision Precision (2, 3 etc)
	 * @param string $_extra    Metadata
	 */
	public function __construct($default = "", $precision = "", $_extra = "") {
		parent::__construct($default, $_extra);
		$this->precision = $precision;
	}
	
	/**
	 * Get Formfield returns a formfield object for this field
	 *
	 * @param string $name The name of the field
	 * @return FormField A FormField subclass object complete with names and values
	 */
	public function get_formfield($name) {
		return new NumberFormField($name, $this->get_value());
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
			return "0";
		return $db->escape_string($val);
	}

	/**
	 * Validate the field
	 *
	 * @param mixed $val Value to use
	 * @return boolean True if successful, false if not
	 */
	public function validate($val = NULL) {
		$val = ($val === NULL) ? $this->get_value() : $val;
		if (strlen($this->precision) > 0) {
			$parts = split(',', $this->precision);
			if (count($parts) < 2 || !preg_match('/^\d+$/', $parts[0]) || !preg_match('/^\d+$/', $parts[1])) {
				array_push($this->errors, $GLOBALS['i18n']['framework']["fielderr9"]);
				return False;
			}
		}
		if (strlen($val) > 0 && !is_numeric($val)) {
			array_push($this->errors, $GLOBALS['i18n']['framework']["fielderr10"]);
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
	 * @return string An SQL query string
	 */
	public function db_create_query($db, $name, $table_name) {
		$extra = "";
		if (strlen($extra) > 0)
			$extra = ' ' . $extra;
		if ($this->precision !== "")
			$extra .= " (" . $this->precision . ")";
		if (strlen($this->default_value) > 0)
			$extra .= " DEFAULT '" . $this->default_value . "'";
		if (strlen($this->_extra) > 0)
			$extra .= ' ' . $this->_extra;
		return "\"" . $name . "\" " . $this::$db_type . $extra;
	}
}
