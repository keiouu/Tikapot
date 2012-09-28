<?php

require_once(home_dir . "framework/form_fields/init.php");

/**
 * Tikapot Fieldset
 *
 * A Fieldset is a set of form fields for display on a Form
 *
 * @author James Thompson
 * @package Tikapot\Framework
 */
class Fieldset implements ArrayAccess, Iterator
{
	protected $fields = array(), $legend = "", $id_override = "", $parent = NULL;
	
	public function __construct($legend, $fields, $id_override = "") {
		$this->legend = $legend;
		$this->id_override = $id_override;
		$this->load($fields);
	}
	
	public function get_id($default) {
		return (strlen($this->id_override) > 0) ? $this->id_override : $default;
	}
	
	public function get_fields() {
		return $this->fields;
	}
	
	public function get_legend() {
		return $this->legend;
	}
	
	public function set_legend($legend) {
		$this->legend = $legend;
		return $this;
	}
	
	public function add_item($name, $field) {
		$this->fields[$name] = $field;
		return $this;
	}
	
	/**
	 * Tikapot 2.0 new-style forms
	 * Set the form this fieldset belongs too
	 * 
	 * @param Form $form The form this fieldset belongs too
	 * @internal
	 */
	public function set_form($form) {
		$this->parent = $form;
	}
	
	/**
	 * New-Style Forms (Tikapot 2.0)
	 * Begin a new fieldset
	 * 
	 * @param string $name The name of the fieldset to obtain, or begin
	 * @return Fieldset A Fieldset Object
	 */
	public function fieldset($name) {
		return $this->parent->fieldset($name);
	}
	
	/**
	 * Tikapot 2.0 new-style forms
	 * Append a new field
	 * 
	 * @param string $name The name of this field (must be unique to this form)
	 * @param string|FormField $description The description of this field (Name of the field that is displayed to the end-user) or a formfield object (an object will ignore type, value and placeholder)
	 * @param string $type The type of this field
	 * @param string $value The initial value of the field
	 * @param string $placeholder The placeholder for this field
	 * @return Fieldset The owning fieldset
	 */
	public function append($name, $description, $type = "text", $value = "", $placeholder = "") {
		if (!class_exists($type)) {
			$type = ucwords($type) . "FormField";
			if (!class_exists($type)) {
				console_error($GLOBALS['i18n']['framework']["new-form-class-error"] . $type);
				return $this;
			}
		}
		if (is_object($description)) {
			$this->fields[$name] = $description;
		} else {
			$this->fields[$name] = new $type($description, $value, array("placeholder" => $placeholder));
		}
		return $this;
	}
	
	public function load($arr) {
		foreach($arr as $name => $field) {
			$this->add_item($name, $field);
		}
	}
	
	public function offsetSet($offset, $value) {
		if (is_null($offset)) {
			$this->fields[] = $value;
		} else {
			$this->fields[$offset] = $value;
		}
	}
	
	public function offsetExists($offset) {
		return isset($this->fields[$offset]);
	}
	
	public function offsetUnset($offset) {
		unset($this->fields[$offset]);
	}
	
	public function offsetGet($offset) {
		return isset($this->fields[$offset]) ? $this->fields[$offset] : null;
	}

    public function rewind() {
        reset($this->fields);
    }
  
    public function current() {
        return current($this->fields);
    }
  
    public function key() {
        return key($this->fields);
    }
    
    public function next() {
        return next($this->fields);
    }
    
    public function valid() {
        $key = key($this->fields);
        return ($key !== NULL && $key !== FALSE);
    }
}

?>
