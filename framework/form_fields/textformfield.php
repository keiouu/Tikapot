<?php
/**
 * Tikapot Form Fields
 *
 * @author James Thompson
 * @package Tikapot\Framework\Form_Fields
 */

require_once(home_dir . "framework/form_fields/formfield.php");

class TextFormField extends FormField
{
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
		$field = '<textarea id="'.$field_id.'" name="'.$field_id.'" class="'.$this->get_classes($safe_name, $classes).'"';
		if ($this->get_placeholder() !== "")
			$field .= ' placeholder="'.$this->get_placeholder().'"';
		if ($this->get_extras() !== "")
			$field .= ' ' . $this->get_extras();
		$field .= '>';
		$field .= $this->get_display_value();
		$field .= '</textarea>';
		return $field;
	}

	/**
	 * Get the name of this field's class
	 *
	 * @return string The name of the field's primary class
	 */
	protected function get_field_class() {
		return "textfield";
	}
}

class TextAreaFormField extends TextFormField {} // Alias
