<?php
/**
 * Tikapot Form Fields
 *
 * @author James Thompson
 * @package Tikapot\Framework\Form_Fields
 */

require_once(home_dir . "framework/form_fields/fkformfield.php");

/**
 * MultiFK Form Field
 */
class MultiFKFormField extends FKFormField
{
	protected $model_strings, $model_field;
	
	public function __construct($name, $model_strings, $model_field, $obj, $initial_value = "", $options = array()) {
		$this->model_strings = $model_strings;
		$this->model_field = $model_field;
		parent::__construct($name, implode(",", $this->model_strings), $obj, $initial_value, $options);
	}
	
	public function get_field_options() {
		if (!isset(FKFormField::$_FKCache[$this->model_string])) {
			$field_options = array();
			foreach ($this->model_strings as $model => $app) {
				$object = $this->model_field->_determine_object($app . "." . $model);
				if ($object) {
					foreach ($object->objects()->all() as $object) {
						$field_options[$model."|".$object->pk] = $object->__toString();
					}
				}
			}
			FKFormField::$_FKCache[$this->model_string] = $field_options;
		}
		return FKFormField::$_FKCache[$this->model_string];
	}

	/**
	 * Get the name of this field's class
	 *
	 * @return string The name of the field's primary class
	 */
	protected function get_field_class() {
		return parent::get_field_class() . " multifkfield";
	}
}