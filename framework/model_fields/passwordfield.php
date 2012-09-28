<?php
/**
 * Tikapot Password Field
 *
 * @author James Thompson
 * @package Tikapot\Framework\ModelFields
 */

require_once(home_dir . "framework/model_fields/charfield.php");
require_once(home_dir . "framework/form_fields/init.php");

/**
 * Password Field
 */
class PasswordField extends CharField
{
	/**
	 * Encodes a string into a salted string of hashy goodness fit for a king
	 * 
	 * @param  string $password The original password
	 * @return string           Hashed and salted chips
	 */
	public static function encode($password) {
		$salted = ConfigManager::get('password_salt', "") . $password . ConfigManager::get('password_salt2', "");
		return hash("sha512", $salted);
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
		$value = ($value === NULL) ? $this->value : $value;
		return parent::sql_value($db, PasswordField::encode($value));
	}
	
	/**
	 * Returns a value suitable for use in a form
	 * 
	 * @return string Suitable form value
	 */
	public function get_form_value() {
		return "";
	}
	
	/**
	 * Get Formfield returns a formfield object for this field
	 *
	 * @param string $name The name of the field
	 * @return FormField A FormField subclass object complete with names and values
	 */
	public function get_formfield($name) {
		return new PasswordFormField($name, $this->get_value(), array("extra" => 'maxlength="'.$this->max_length.'"'));
	}
}

