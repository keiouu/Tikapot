<?php
/**
 * Tikapot Text Field
 *
 * @author James Thompson
 * @package Tikapot\Framework\ModelFields
 */

require_once(home_dir . "framework/model_fields/modelfield.php");

/**
 * Text Field
 */
class TextField extends CharField
{
	protected static /** The Database Type for this field (e.g. TEXT) */ $db_type = "TEXT";
	
	/**
	 * Construct
	 * 
	 * @param string $default Default value
	 * @param string $_extra  Metadata
	 */
	public function __construct($default = "", $_extra = "") {
		parent::__construct(0, $default, $_extra);
	}
	
	/**
	 * Get Formfield returns a formfield object for this field
	 *
	 * @param string $name The name of the field
	 * @return FormField A FormField subclass object complete with names and values
	 */
	public function get_formfield($name) {
		return new TextFormField($name, $this->get_value());
	}
	
	/**
	 * Validate the field
	 *
	 * @param mixed $val Value to use
	 * @return boolean True if successful, false if not
	 */
	public function validate($val = NULL) {
		// Not much to validate...
		return True;
	}
}

