<?php
/**
 * Tikapot Integer Field
 *
 * @author James Thompson
 * @package Tikapot\Framework\ModelFields
 */

require_once(home_dir . "framework/model_fields/modelfield.php");

/**
 * Integer Field holds integers
 */
class IntegerField extends ModelField
{
	protected static /** The Database Type for this field (e.g. INT) */ $db_type = "INT";
	private /** Maximum length of the field */ $max_length = 0,
			/** Auto increment? */ $auto_increment = False;
	
	/**
	 * Constructor
	 * 
	 * @param integer $max_length     Maximum Length of field
	 * @param integer $default        The default field value
	 * @param boolean $auto_increment Should we auto increment?
	 * @param string  $_extra         Metadata
	 */
	public function __construct($max_length = 50, $default = 0, $auto_increment = False, $_extra = "") {
		parent::__construct($default, $_extra);
		$this->max_length = ($max_length > 0) ? $max_length : 50;
		$this->auto_increment = $auto_increment;
		$this->hide_from_query = $auto_increment;
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
			return 0;
		return intval($val);
	}
	
	/**
	 * Validate the field
	 *
	 * @param mixed $val Value to use
	 * @return boolean True if successful, false if not
	 */
	public function validate($val = NULL) {
		$val = ($val === NULL) ? $this->get_value() : $val;
		$regex = "/^(\d{0,".$this->max_length."})$/";
		$valid = preg_match($regex, $val) == 1; // These == 1 are not needed but clarify test results
		if (!$valid)
			array_push($this->errors, $GLOBALS['i18n']['framework']["fielderr6"] . " " . $val);
		return $valid && (strpos($val, ".") == False);
	}
	
	/**
	 * The name of this field's SEQUENCE
	 *
	 * @internal
	 * @param Database $db Database object
	 * @param string $name Field's name
	 * @param string $table_name Table name
	 * @return string An SQL SEQUENCE name
	 */
	protected function sequence_name($db, $name, $table_name) {
		return $db->escape_string($table_name."_".$name."_seq");
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
		if ($db->get_type() != "psql" && $this->max_length > 0)
			$extra .= " (" . $this->max_length . ")";
		if (!$this->auto_increment && strlen($this->default_value) > 0)
			$extra .= " DEFAULT '" . $this->default_value . "'";
		if ($this->auto_increment) {
			if ($db->get_type() == "mysql")
				$extra .= " AUTO_INCREMENT";
			if ($db->get_type() == "psql")
				$extra .= " DEFAULT nextval('".$this->sequence_name($db, $name, $table_name)."')";
		}
		if (strlen($this->_extra) > 0)
			$extra .= ' ' . $this->_extra;
		return "\"" . $name . "\" " . $this::$db_type . $extra;
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
		if ($db->get_type() == "psql" && $this->auto_increment) {
			$seq = $this->sequence_name($db, $name, $table_name);
			$db->query('DROP SEQUENCE IF EXISTS '.$seq.';');
			return "CREATE SEQUENCE ".$seq.";";
		}
		return "";
	}
}
