<?php
/*
 * Tikapot Foreign Key Field
 *
 */

require_once(home_dir . "framework/model_fields/modelfield.php");
require_once(home_dir . "framework/form_fields/init.php");
require_once(home_dir . "framework/utils.php");
require_once(home_dir . "framework/model.php");

class FKField extends ModelField
{
	protected $_name, $_object, $_enforce;
	
	public function __construct($name, $enforce_checking = true) {
		parent::__construct(null);
		$this->_name = $name;
		$this->_object = null;
		$this->_enforce = $enforce_checking;
	}
	
	public function __toString() {
		$obj = $this->_grabObject();
		$string = "" . ($obj === null ? "-" : $this->raw_value());
		if ($obj && method_exists($obj, "__toString"))
			$string = $obj->__toString();
		return $string;
	}
	
	public function get_formfield($name) {
		return new FKFormField($name, $this->_name, $this->_grabObject());
	}
	
	public function _getName() {
		return $this->_name;
	}
	
	public function get_db_type() {
		try {
			$class = $this->_className();
			$obj = new $class();
			return $obj->get_field("pk")->get_db_type();
		} catch (Exception $e) {
			return static::$db_type;
		}
	}
	
	/**
	 * Returns the name of the app this field refers too
	 *
	 * @internal
	 * @return string The application part of this FK Field's name
	 */
	public function _appName() {
		if (strpos($this->_name, ".") === false)
			return "";
		list($app, $n, $class) = partition($this->_name, '.');
		return $app;
	}
	
	/**
	 * Returns the name of the class this field refers too
	 *
	 * @internal
	 * @return string The class part of this FK Field's name
	 */
	public function _className() {
		if (strpos($this->_name, ".") === false)
			return $this->_name;
		list($app, $n, $class) = partition($this->_name, '.');
		return $class;
	}
	
	/**
	 * Grab an object, it may return a fully blown object with database values
	 * or, if there is no current foreign PK set, it may just return a new
	 * object of the correct class.
	 *
	 * @return mixed An object this FK could relate to, usually the one it does relate to
	 */
	protected function _grabObject($pk = null) {
		if ($this->_object !== null)
			return $this->_object;
		
		// Need to find an object
		$app = $this->_appName();
		$class = $this->_className();
		
		$default = get_named_class($class, $app); // Make sure $class is avaliable
		if ($default == null) {
			console_warning($GLOBALS['i18n']['framework']["error1"] . " '" . $app . "." . $class . "' " . $GLOBALS['i18n']['framework']["fielderr5"]);
			return null;
		}
		
		if ($pk === null) {
			$pk = $this->raw_value();
		}
		
		if ($pk === null) {
			return $default;
		}
		
		// Need to get an object from the database
		$object = $class::get_or_ignore($pk);
		if ($object === null) {
			return $default;
		}
		
		$this->_object = $object;
		return $object;
	}
	
	public function is_set() {
		return $this->raw_value() !== null;
	}
	
	/**
	 * Set a new value for this FK
	 *
	 * @param object|int $value Either an object or a PK, i.e. the foreign key.
	 */
	public function set_value($value) {
		if (is_object($value))
			$value = $value->pk;
		
		if ($value)
			parent::set_value($value);
		
		// Reset the object
		if ($this->_object !== null)
			$this->_object = null;
	}
	
	/**
	 * Get the object this FK field is referring too, or null if it isnt
	 *
	 * @return object|null Object this FK field is referring too, or null if it isnt
	 */
	public function get_value() {
		if (!$this->is_set())
			return null;
		return $this->_grabObject();
	}
	
	public function get_form_value() {
		$obj = $this->get_value();
		return $obj !== null ? $obj->pk : "null";
	}
	
	public function sql_value($db, $val = NULL) {
		$val = ($val === NULL) ? $this->raw_value() : $val;
		if (is_object($val))
			$val = $val->pk;
		return ($val !== null) ? $db->escape_string($val) : "0";
	}
	
	public function validate($val = NULL) {
		return $this->_className() !== "";
	}
	
	public function post_model_create($db, $name, $table_name) {
		// Create valid REFERENCE
		if ($db->get_type() == "psql" && $this->_enforce) {
			$constraint = $table_name . "_" . $name . "_fkey";
			$class = $this->_className();
			$obj = new $class();
			return 'ALTER TABLE "'.$table_name.'" ADD CONSTRAINT '.$constraint.' FOREIGN KEY ("'.$name.'") REFERENCES "'.$obj->get_table_name().'" ('.$obj->get_pk_name().') ON DELETE CASCADE DEFERRABLE INITIALLY DEFERRED;';
		}
	}
	
	public function relatesTo($class) {
		return $class == $this->_className();
	}
}

?>

