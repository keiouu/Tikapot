<?php
/*
 * Tikapot Multi Foreign Key Field
 *
 */

require_once(home_dir . "framework/model_fields/fkfield.php");
require_once(home_dir . "framework/utils.php");

class MultiFKField extends FKField
{
	protected static $db_type = "varchar";
	private $_names = array();
	
	public function __construct() {
		$arg_list = func_get_args();
		foreach ($arg_list as $arg) {
			list($app, $model) = explode(".", $arg);
			$this->_names[$model] = $app;
		}
		return parent::__construct("");
	}
	
	public function __toString() {
		if (strpos($this->raw_value(), "|") === false)
			return "";
		list($class, $pk) = explode("|", $this->value);
		$loaded_class = get_named_class($class, $this->_names[$class]);
		if (isset($this->_names[$class]) && $loaded_class !== false) {
			$object = $class::get_or_ignore(array("pk" => $pk));
			if ($object)
				return "" . $object;
		}
		return "";
	}
	
	public function _appName() {
		if (strpos($this->raw_value(), "|") === false)
			return "";
		list($class, $pk) = explode("|", $this->raw_value());
		foreach ($this->_names as $_class => $_app) {
			if ($_class == $class)
				return $_app;
		}
		return "";
	}
	
	public function _className() {
		if (strpos($this->raw_value(), "|") === false)
			return "";
		list($class, $pk) = explode("|", $this->raw_value());
		return $class;
	}
	
	protected function _grabObject($pk = null) {
		$app = $this->_appName();
		$class = $this->_className();
		if ($app == "" || $class == "" || $pk == null)
			return null;
		return parent::_grabObject($pk);
	}
	
	public function get_db_type() {
		return MultiFKField::$db_type;
	}
	
	/* Used by the formfield to turn app.model into a model instance */
	public function _determine_object($string) {
		if (strpos($string, ".") !== false) {
			list($app, $model) = explode(".", $string);
			return new $model();
		}
	}
	
	public function set_value($value) {
		if (is_object($value)) {
			$class = get_class($value);
			if (!isset($this->_names[$class]))
				return console_log($GLOBALS['i18n']['framework']['mfk_err1'] . $class);
			return parent::set_value($class."|".$value->pk);
		}
		// Sanity Checks
		if (strpos($value, "|") === false)
			return console_log($GLOBALS['i18n']['framework']['mfk_err2'] . $value);
		list($class, $pk) = explode("|", $value);
		if (!isset($this->_names[$class]))
			return console_log($GLOBALS['i18n']['framework']['mfk_err1'] . $class);
		return parent::set_value($value);
	}
	
	public function get_value() {
		if (!strpos($this->raw_value(), "|"))
			return null;
		list($class, $pk) = explode("|", $this->value);
		$this->_name = $this->_names[$class] . "." . $class;
		if ($this->_object !== null && $this->_object->pk != $pk)
			$this->_object = null;
		return $this->_grabObject($pk);
	}
	
	public function sql_value($db, $val = NULL) {
		$val = ($val === NULL) ? $this->raw_value() : $val;
		if (is_object($val))
			$val = get_class($val) . "|" . $val->pk;
		return "'" . (($val !== null) ? $db->escape_string($val) : "0") . "'";
	}
	
	public function post_model_create($db, $name, $table_name) {
		return "";
	}
	
	public function db_create_query($db, $name, $table_name) {
		return "\"" . $name . "\" " . MultiFKField::$db_type;
	}
	
	public function get_formfield($name) {
		return new MultiFKFormField($name, $this->_names, $this, $this->_grabObject());
	}
	
	public function validate($val = NULL) {
		$val = ($val === NULL) ? $this->raw_value() : $val;
		if (strpos($val, "|") === false)
			return false;
		list($model, $pk) = explode("|", $val);
		return isset($this->_names[$model]);
	}
	
	public function relatesTo($model) {
		foreach ($this->_names as $m => $a) {
			if ($m == $model || (is_object($model) && $m == get_class($model)))
				return true;
		}
		return false;
	}
}
?>

