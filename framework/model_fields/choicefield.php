<?php
/**
 * Tikapot Choice Field
 *
 * @author James Thompson
 * @package Tikapot\Framework\ModelFields
 */

require_once(home_dir . "framework/model_fields/charfield.php");
require_once(home_dir . "framework/form_fields/init.php");

/**
 * A ChoiceField allows you to specify a dataset a user may use for a model field
 * value. E.g. ["green", "yellow", "red"]
 */
class ChoiceField extends CharField implements ArrayAccess
{
	private /** The choices available */ $choices;
	
	/**
	 * Construct
	 * @param array  $choices    Choices array
	 * @param string  $default    The default value (must be a key in the array)
	 * @param integer $max_length The maximum length of the database field (automatically calculated if 0)
	 * @param string  $_extra     Any metadata for the field
	 */
	public function __construct($choices, $default = "", $max_length = 0, $_extra = "") {
		if (!is_array($choices))
			throw new Exception($GLOBALS['i18n']['framework']["fielderr12"]);
		
		if ($max_length === 0) {
			foreach ($choices as $val => $choice) {
				if (strlen($val) + 1 > $max_length)
					$max_length = strlen($val) + 1;
			}
		}
		
		parent::__construct($max_length, $default, $_extra);
		$this->choices = $choices;
	}
	
	/**
	 * Set an array offset
	 *
	 * @internal
	 * @param  mixed $offset The offset
	 * @param  mixed $value  The value
	 * @return null
	 */
	public function offsetSet($offset, $value) {
		throw new Exception($GLOBALS['i18n']['framework']["fielderr19"]);
	}
	
	/**
	 * Does the offset exist in the set?
	 * 
	 * @internal
	 * @param  mixed $offset Offset
	 * @return boolean         True if success, or false
	 */
	public function offsetExists($offset) {
		foreach ($this->choices as $val => $choice)
			if ($offset === $choice)
				return true;
		return false;
	}
	
	/**
	 * Unset the offset exist in the set
	 * 
	 * @internal
	 * @param  mixed $offset Offset
	 * @return null
	 */
	public function offsetUnset($offset) {
		foreach ($this->choices as $val => $choice)
			if ($offset === $choice)
				unset($this->choices[$val]);
	}
	
	/**
	 * Return the set value associated with the offset
	 * 
	 * @internal
	 * @param  mixed $offset Offset
	 * @return boolean         True if success, or false
	 */
	public function offsetGet($offset) {
		foreach ($this->choices as $val => $choice)
			if ($offset === $choice)
				return $val;
		return null;
	}
	
	/**
	 * Tostring()
	 *
	 * @internal
	 * @return string The value of this choice field
	 */
	public function __toString() {
		$value = $this->get_value();
		if (isset($this->choices[$value]))
			return $this->choices[$value];
		return $value;
	}
	
	/**
	 * The SQL value of this choice field
	 * 
	 * @internal
	 * @param Database $db Database object
	 * @param string $val The value to convert to an SQL format
	 * @return string The value as an SQL-ready string
	 */
	public function sql_value($db, $val = NULL) {
		$val = ($val === NULL) ? $this->value : $val;
		foreach ($this->choices as $key => $choice)
			if ($val == $choice)
				return parent::sql_value($db, $key);
		return parent::sql_value($db, $val);
	}
	
	/**
	 * Get the choices available in this field
	 * 
	 * @return array The choices this field has stored
	 */
	public function get_choices() {
		return $this->choices;
	}
	
	/**
	 * Set the value of this field. Value must be a valid choice.
	 * 
	 * @param string $value A value to set
	 */
	public function set_value($value) {
		if (strlen(trim($value)) === 0)
			$value = $this->default_value;
		foreach ($this->choices as $val => $choice) {
			if ($value == $val)
				return parent::set_value($value);
			if ($value == $choice) // In case they go by the right side of the array!
				return parent::set_value($val);
		}
		console_log($GLOBALS['i18n']['framework']["fielderr14"] . " " . $value);
	}
	
	/**
	 * Get Formfield returns a formfield object for this field
	 *
	 * @param string $name The name of the field
	 * @return SelectFormField A SelectFormField subclass object complete with names and values
	 */
	public function get_formfield($name) {
		return new SelectFormField($name, $this->choices, $this->get_value(), array("extra" => 'maxlength="'.$this->max_length.'"'));
	}
	
	/**
	 * Validate the field
	 *
	 * @param mixed $val Value to use
	 * @return boolean True if successful, false if not
	 */
	public function validate($val = NULL) {
		$mval = ($val === NULL) ? $this->get_value() : $val;
		foreach ($this->choices as $val => $choice)
			if ($mval == $val || $mval == $choice)
				return parent::validate($val);
		array_push($this->errors, $GLOBALS['i18n']['framework']["fielderr13"] . " " . $mval);
		return false;
	}
}

