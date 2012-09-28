<?php
/*
 * Tikapot Many-To-Many Field
 *
 */

require_once(home_dir . "framework/model_fields/modelfield.php");
require_once(home_dir . "framework/model_query.php");
require_once(home_dir . "framework/utils.php");

class M2MField extends ModelField
{
	protected static $db_type = "TEXT";
	private $model_str, $ids;
	
	public function __construct($model) {
		parent::__construct();
		$this->model_str = $model;
		$this->ids = "";
		$this->dummy_object = get_named_class($this->_className(), $this->_appName());
	}
	
	public function _appName() {
		if (strpos($this->model_str, ".") === false)
			return $this->model_str;
		list($app, $class) = explode(".", $this->model_str);
		return $app;
	}
	
	public function _className() {
		if (strpos($this->model_str, ".") === false)
			return $this->model_str;
		list($app, $class) = explode(".", $this->model_str);
		return $class;
	}
	
	public function set_value($value) {
		if (is_array($value)) {
			$this->ids = join(",", $value);
		} else {
			$this->ids = $value;
		}
	}
	
	public function get_value() {
		return $this;
	}
	
	public function sql_value($db, $val = NULL) {
		$val = ($val === NULL) ? $this->get_value() : $val;
		if (is_array($val))
			$val = join(",", $val);
		return "'" . (($val !== null) ? $db->escape_string($val) : "") . "'";
	}
	
	public function add($obj) {
		if (get_class($obj) != $this->_className())
			throw new Exception($GLOBALS['i18n']['framework']['m2m_err1'] . get_class($obj) . " and " . $this->_className());
		if (!$obj->from_db())
			throw new Exception($GLOBALS['i18n']['framework']['m2m_err2']);
		
		$array = explode(",", $this->ids);
		$array = array_filter($array, 'strlen');
		$array[] = $obj->pk;
		$this->ids = join(",", $array);
	}
	
	public function objects() {
		return $this->dummy_object->find(array("pk" => array("(".$this->ids.")", "IN")));
	}
	
	public function get_formfield($name) {
		return new HiddenFormField($name, $this->get_value());
	}
	
	public function validate($val = NULL) {
		// TODO
		return True;
	}
}

?>

