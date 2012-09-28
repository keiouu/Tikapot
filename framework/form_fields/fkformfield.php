<?php
/**
 * Tikapot Form Fields
 *
 * @author James Thompson
 * @package Tikapot\Framework\Form_Fields
 */

require_once(home_dir . "framework/form_fields/selectformfield.php");

/**
 * A foreign key form field, to represent an FKField in forms
 */
class FKFormField extends SelectFormField
{
	protected static $_FKCache = array();
	protected $model_string, $obj;
	
	public function __construct($name, $model_string, $obj, $initial_value = "", $options = array()) {
		$this->model_string = $model_string;
		$this->obj = $obj;
		parent::__construct($name, $this->get_field_options(), (($initial_value === "" && isset($this->obj) && $this->obj->fromDB()) ? "" . $this->obj->pk : $initial_value), $options);
	}
	
	public function get_field_options() {
		$class = get_class($this->obj);
		if (!isset(FKFormField::$_FKCache[$class])) {
			$field_options = array("0" => "-");
			foreach ($this->obj->objects()->all() as $object) {
				$field_options[$object->pk] = $object->__toString();
			}
			FKFormField::$_FKCache[$class] = $field_options;
		}
		return FKFormField::$_FKCache[$class];
	}
	
	public function get_object() {
		return $this->obj;
	}
	
	public function get_model_string() {
		return $this->model_string;
	}

	/**
	 * Get the name of this field's class
	 *
	 * @return string The name of the field's primary class
	 */
	protected function get_field_class() {
		return parent::get_field_class() . " fkfield";
	}
}