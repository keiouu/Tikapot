<?php
/**
 * Tikapot Form Fields
 *
 * @author James Thompson
 * @package Tikapot\Framework\Form_Fields
 */

require_once(home_dir . "framework/form_fields/formfield.php");

/**
 * Hidden Form Field (type=hidden)
 */
class HiddenFormField extends FormField
{

	/**
	 * Returns the type of the field (to be used in <input type="..." />)
	 * 
	 * @return string The field type (text, date, etc)
	 */
	public function get_type() {
		return "hidden";
	}

	/**
	 * Get the name of this field's class
	 *
	 * @return string The name of the field's primary class
	 */
	protected function get_field_class() {
		return "hiddenfield";
	}
}