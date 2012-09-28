<?php
/**
 * Tikapot Form Fields
 *
 * @author James Thompson
 * @package Tikapot\Framework\Form_Fields
 */

require_once(home_dir . "framework/form_fields/formfield.php");

/**
 * A file upload form field provides file upload functionality to
 * Tikapot forms. It captures the event as well as initiating it.
 */
class FileUploadFormField extends FormField
{
	protected 	/** The location to save too */ $location,
				/** Accepted file types */ $types;
	
	/**
	 * Construct a file upload form field
	 * @param string $name          The name of the field
	 * @param string $location      The location to save files too
	 * @param array $types         An array containing allowed file types
	 * @param string $initial_value The initial value of the form field
	 * @param array  $options       Metadata
	 */
	public function __construct($name, $location, $types, $initial_value = "", $options = array()) {
		$this->location = $location;
		$this->types = $types;
		parent::__construct($name, $initial_value, $options);
	}
	
	/**
	 * Validate this field
	 * @param  string $base_id   The forms base ID
	 * @param  string $safe_name The field's printable name
	 * @return boolean            True if we validate, false if not
	 */
	public function validate($base_id, $safe_name) {
		return !$this->has_error();
	}
	
	/**
	 * Set the value of this field
	 * 
	 * @param string $val The new value
	 */
	public function set_value($val) {
		if (is_array($val) && isset($val['tmp_name'])) {
			// Check type
			$type = substr(strrchr($val['name'], '.'), 1);
			if (!in_array($type, $this->types)) {
				$this->set_error($GLOBALS['i18n']['framework']["fielderr18"]);
				return;
			}
			
			// Choose the name
			$filename = $this->location . basename($val['name'], "." . $type);
			
			// Ensure the file doesnt exist
			$old_filename = $filename;
			$i = 0;
			while (file_exists($filename . "." . $type)) {
				$filename = $old_filename . "_" . $i;
				$i++;
			}
			
			// Upload the file
			$filename .= "." . $type;
			if (move_uploaded_file($val['tmp_name'], $filename)) {
				return parent::set_value($filename);
			} else {
				$this->set_error($GLOBALS['i18n']['framework']["fielderr17"] . " " . (isset($php_errormsg) ? $php_errormsg : $GLOBALS['i18n']['framework']["error2"]));
				return;
			}
		}
		return parent::set_value($val);
	}
	
	/**
	 * Get the value of this field
	 * 
	 * @return string Our current value
	 */
	public function get_value() {
		return parent::get_value();
	}

	/**
	 * Returns the type of the field (to be used in <input type="..." />)
	 * 
	 * @return string The field type (text, date, etc)
	 */
	public function get_type() {
		return "file";
	}
	
	/**
	 * Claim ownership of an orphaned field (useful if one formfield has multiple field's in the form)
	 * 
	 * @param  string $my_name     This field's name
	 * @param  string $field_name  The orphaned field's name
	 * @param  string $field_value The orphaned field's value
	 * @return boolean              True if we want to claim ownership over this field
	 */
	public function claim_own($my_name, $field_name, $field_value) {
		if ($field_name == $my_name . "_check") {
			$this->set_value("");
			return true;
		}
		return parent::claim_own($my_name, $field_name, $field_value);
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
		$field = parent::get_input($base_id, $safe_name, $classes);
		$check_field_id = $this->get_field_id($base_id, $safe_name) . "_check";
		$field .= '<span class="checkfield_remove">
		<input type="checkbox" id="'.$check_field_id.'" name="'.$check_field_id.'" value="0" class="checkedfield" /> '.$GLOBALS['i18n']['framework']['remove'].'</span>';
		return $field;
	}

	/**
	 * Get the name of this field's class
	 *
	 * @return string The name of the field's primary class
	 */
	protected function get_field_class() {
		return "filefield";
	}
}
