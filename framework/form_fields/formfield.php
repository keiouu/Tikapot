<?php
/**
 * Tikapot Form Field
 *
 * @author James Thompson
 * @package Tikapot\Framework\Form_Fields
 */

require_once(home_dir . "framework/utils.php");

/**
 * Tikapot form field base class
 */
abstract class FormField
{
	protected 	/** Name of this field */ $name,
				/** Value of this field */ $value,
				/** Options for this field */ $options,
				/** Current error log */ $error,
				/** Help text associated with this field */ $helptext,
				/** This field's default value */ $default_value;
	
	/**
	 * Construct
	 * 
	 * @param string $name          The field's name
	 * @param string $initial_value Our initial value
	 * @param array  $options       An array of options (e.g. helptext)
	 */
	public function __construct($name, $initial_value = "", $options = array()) {
		$this->name = $name;
		$this->default_value = $initial_value;
		$this->value = $initial_value;
		$this->options = $options;
		$this->error = "";
		$this->helptext = isset($options['helptext']) ?  $options['helptext'] : "";
	}
	
	/**
	 * Validate this field
	 * @param  string $base_id   The forms base ID
	 * @param  string $safe_name The field's printable name
	 * @return boolean            True if we validate, false if not
	 */
	public function validate($base_id, $safe_name) {
		return true;
	}
	
	/**
	 * Set the field name
	 * 
	 * @param string $val New name
	 */
	public function set_name($val) {
		$this->name = $val;
	}
	
	/**
	 * Returns this field's name
	 * 
	 * @return string Field's name
	 */
	public function get_name() {
		return $this->name;
	}
	
	/**
	 * Set the value of this field
	 * 
	 * @param string $val The new value
	 */
	public function set_value($val) {
		$this->value = $val;
	}
	
	/**
	 * Get the value of this field
	 * 
	 * @return string Our current value
	 */
	public function get_value() {
		return $this->value;
	}
	
	/**
	 * Sets the field's value back to default
	 * @return  null
	 */
	public function clear_value() {
		$this->value = $this->default_value;
	}
	
	/**
	 * Returns a printable (html-safe) value of the field
	 * 
	 * @return string Safe value
	 */
	public function get_display_value() {
		return htmlentities($this->value);
	}
	
	/**
	 * Returns the type of the field (to be used in <input type="..." />)
	 * 
	 * @return string The field type (text, date, etc)
	 */
	public function get_type() {
		return "";
	}
	
	/**
	 * Sets the current error associated with the field
	 * 
	 * @param string $val The error string
	 */
	public function set_error($val) {
		$this->error = $val;
	}
	
	/**
	 * Returns true if there is an error on the field, or false if not
	 * 
	 * @return boolean True if there is an error on the field
	 */
	public function has_error() {
		return $this->error != "";
	}
	
	/**
	 * Get the current error associated with the field
	 * 
	 * @return string Field error
	 */
	public function get_error() {
		return $this->error;
	}
	
	/**
	 * Set help text for this field
	 * 
	 * @param string $val Help text for this field
	 */
	public function set_helptext($val) {
		$this->helptext = $val;
	}
	
	/**
	 * Returns true if there is help text on the field, or false if not
	 * 
	 * @return boolean True if there is help text on the field
	 */
	public function has_helptext() {
		return $this->helptext != "";
	}
	
	/**
	 * Get the current help text associated with the field
	 * 
	 * @return string Field help text
	 */
	public function get_helptext() {
		return $this->helptext;
	}
	
	/**
	 * Get the current placeholder associated with the field
	 * 
	 * @return string Placeholder text for the form field
	 */
	public function get_placeholder() {
		return isset($this->options['placeholder']) ? $this->options['placeholder'] : "";
	}
	
	/**
	 * Get the current metadata associated with the field
	 * 
	 * @return string Any field metadata
	 */
	public function get_extras() {
		return isset($this->options['extra']) ? $this->options['extra'] : "";
	}
	
	/**
	 * Returns an option of this field
	 * @param  string $key The name of the option to grab
	 * @return string | array      The option (or "" if key was not found) or the full array if $key == ""
	 */
	public function get_options($key = "") {
		if ($key !== "")
			return isset($this->options[$key]) ? $this->options[$key] : "";
		return $this->options;
	}
	
	/**
	 * Return a HTML safe version of the error of this form
	 * 
	 * @param  string $base_id   Base ID of the form
	 * @param  string $safe_name Our safe field name
	 * @return string            Error as escaped text
	 */
	public function get_error_html($base_id, $safe_name) {
		return htmlentities($this->error);
	}
	
	/**
	 * Get the field ID
	 * 
	 * @param  string $base_id   Base ID of the form
	 * @param  string $safe_name Our safe field name
	 * @return string            This field's ID
	 */
	public function get_field_id($base_id, $safe_name) {
		return $base_id . '_' . $safe_name;
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
		return false;
	}
	
	/**
	 * Returns a HTML label for this field
	 * 
	 * @param  string $base_id   Our form's base ID
	 * @param  string $safe_name Our html-safe name
	 * @param  string $extra     Any extra HTML (classes, id, etc)
	 * @return string            HTML <label /> element
	 */
	public function get_label($base_id, $safe_name, $extra = "") {
		$field_id = $this->get_field_id($base_id, $safe_name);
		if ($this->get_type() !== "hidden")
			return '<label for="'.$field_id.'"'.$extra.'>'.prettify($this->name).'</label>';
		return '';
	}
	
	/**
	 * Get the name of this field's class
	 *
	 * @return string The name of the field's primary class
	 */
	protected function get_field_class() {
		return get_called_class();
	}
	
	/**
	 * Get the CSS classes associated with this field
	 * @param  string $safe_name Our safe name
	 * @param  string $classes   Extra classes to use
	 * @return string            A string containing space-delimited classes
	 */
	protected function get_classes($safe_name, $classes = "") {
		$classes = trim($classes . ' ' .  $safe_name . '_field');
		$classes = trim($classes . ' ' . $this->get_options("classes"));
		$classes = trim($classes . ' ' . $this->get_field_class());
		return $classes;
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
		$ret = "";
		$field_id = $this->get_field_id($base_id, $safe_name);
		$ret .= '<input';
		if ($base_id !== "control")
			$ret .= ' id="'.$field_id.'"';
		$ret .= ' class="'.$this->get_classes($safe_name, $classes).'" type="'.$this->get_type().'" name="'.$field_id.'"';
		if (strlen($this->get_display_value()) > 0)
			$ret .= ' value="'.$this->get_display_value().'"';
		if ($this->get_placeholder() !== "")
			$ret .= ' placeholder="'.$this->get_placeholder().'"';
		if ($this->get_extras() !== "")
			$ret .= ' ' . $this->get_extras();
		$ret .= ' />';
		return $ret;
	}
	
	/**
	 * Hook, called before _POST data is loaded into the parent form
	 */
	public function pre_postdata_load() {
		// - 
	}
}
