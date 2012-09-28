<?php
/**
 * Tikapot Form Fields
 *
 * @author James Thompson
 * @package Tikapot\Framework\Form_Fields
 */

require_once(home_dir . "framework/form_fields/formfield.php");

/**
 * Creates a <select> form field
 */
class SelectFormField extends FormField
{
	private /** An array of options for the field */ $field_options;
	
	/**
	 * Construct
	 * 
	 * @param string $name          The field's name
	 * @param array  $field_options       An array of options for the select field (i.e. the <option> elements)
	 * @param string $initial_value Our initial value
	 * @param array  $options       An array of options (e.g. helptext)
	 */
	public function __construct($name, $field_options, $initial_value = "0", $options = array()) {
		if (is_array($field_options)) {
			$this->field_options = $field_options;
		} else {
			throw new Exception($GLOBALS['i18n']['framework']["fielderr11"]);
		}
		parent::__construct($name, $initial_value, $options);
	}
	
	/**
	 * Shortcut to create a dropdown fed by a model
	 * 
	 * @param  string $name    Name of the field
	 * @param  Model $model   A model object
	 * @param  array  $options Metadata
	 * @return SelectFormField          A SelectFormField object
	 */
	public static function from_model($name, $model, $options = array()) {
			$arr = array();
			$objects = $model::objects()->all();
			foreach ($objects as $object) {
				$arr[$object->pk] = $object->__toString();
			}
			return new static($name, $arr, ($model->fromDB() ? "".$model->pk : "0"), $options);
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
		$field = '<select id="'.$field_id.'" name="'.$field_id.'" class="'.$this->get_classes($safe_name, $classes).'"';
		if ($this->get_extras() !== "")
			$field .= ' ' . $this->get_extras();
		$field .= '>';
		foreach($this->field_options as $value => $name) {
			$field .= '<option value="'.$value.'"';
			if ($value == $this->get_value())
				$field .= ' selected="selected"';
			$field .= '>'.$name.'</option>';
		}
		$field .= "</select>";
		return $field;
	}

	/**
	 * Get the name of this field's class
	 *
	 * @return string The name of the field's primary class
	 */
	protected function get_field_class() {
		return "selectfield";
	}
}