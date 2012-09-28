<?php
/**
 * Tikapot Form Fields
 *
 * @author James Thompson
 * @package Tikapot\Framework\Form_Fields
 */

require_once(home_dir . "framework/form_fields/formfield.php");

/**
 * A checkbox form field
 */
class CheckedFormField extends FormField
{
	/**
	 * Construct
	 * 
	 * @param string $name          The field's name
	 * @param boolean $initial_value Our initial value
	 * @param array  $options       An array of options (e.g. helptext)
	 */
	public function __construct($name, $initial_value = false, $options = array()) {
		if ($initial_value === "")
			$initial_value = false;
		parent::__construct($name, $initial_value, $options);
	}
	
	/**
	 * Get the HTML <input /> element for this field_id
	 * 
	 * @param  string $base_id   Our form's base ID
	 * @param  string $safe_name Our html-safe name
	 * @param  string $classes   CSS classes to use
	 * @return string            HTML <input /> element
	 */
	public function get_input($base_id, $safe_name, $classes = "") {
		$field_id = $this->get_field_id($base_id, $safe_name);
		$field = '<input type="checkbox" id="'.$field_id.'" name="'.$field_id.'" value="1" class="'.$this->get_classes($safe_name, $classes).'"';
		if ($this->get_value() == true)
			$field .= ' checked="yes"';
		if ($this->get_extras() !== "")
			$field .= ' ' . $this->get_extras();
		$field .= ' />';
		return $field;
	}

	/**
	 * Get the name of this field's class
	 *
	 * @return string The name of the field's primary class
	 */
	protected function get_field_class() {
		return "checkedfield";
	}
	
	/**
	 * Hook, called before _POST data is loaded into the parent form
	 */
	public function pre_postdata_load() {
		$this->set_value(false);
	}
}
