<?php
/**
 * Tikapot Framework models
 * 
 * @author James Thompson
 * @package Tikapot\Framework
 */

require_once(home_dir . "framework/config_manager.php");
require_once(home_dir . "framework/model.php");
require_once(home_dir . "framework/model_fields/init.php");
require_once(home_dir . "framework/utils.php");

/**
 * Config model for Tikapot, simple key/value
 */
class Config extends Model
{
	/**
	 * Construct
	 */
	public function __construct() {
		parent::__construct();
		$this->add_field("key", new CharField(250, ""));
		$this->add_field("value", new CharField(250, ""));
	}
}

/**
 * Application-Specific Config
 */
class App_Config extends Model
{
	/**
	 * Construct
	 */
	public function __construct() {
		parent::__construct();
		// TODO - forms
		/*$apps = ConfigManager::get_app_list();
		$choices = array();
		foreach ($apps as $name) {
			list($dir, $app) = explode("/", $name);
			$choices[$app] = $app;
		}*/
		$this->add_field("app", new CharField(250, ""));
		$this->add_field("key", new CharField(250, ""));
		$this->add_field("value", new CharField(250, ""));
	}
}

/**
 * Each model has a Content Type (a UID)
 */
class ContentType extends Model
{
	/**
	 * Construct
	 */
	public function __construct() {
		parent::__construct();
		$this->add_field("name", new CharField(150));
		$this->add_field("version", new NumericField(1.0, "10,4"));
		
		$this->_version = "1.1";
	}
	
	/**
	 * Upgrade to Tikapot 1.1
	 *
	 * @internal
	 * @param  Tikapot\Framework\Database $db          The database
	 * @param  string $old_version Old Version Number
	 * @param  string $new_version New Version Number
	 * @return boolean              Success
	 */
	public function upgrade($db, $old_version, $new_version) {
		if ($old_version == "1.0") {
			$db->query('ALTER TABLE contenttype ADD COLUMN "version" numeric(10,4) DEFAULT 1.0;');
		}
		return parent::upgrade($db, $old_version, $new_version);
	}
	
	/**
	 * Returns an object related to this content type
	 * 
	 * @return Object An object form of this CT
	 */
	public function obtain() {
		return get_named_class($this->name);
	}
	
	/**
	 * Returns the content type of a given model
	 * 
	 * @param  Tikapot\Framework\Model $obj The model to check
	 * @return Tikapot\Framework\ContentType      The content type object
	 */
	public static function of($obj) {
		static $ctypes = array();
		$class = get_class($obj);
		if (!isset($ctypes[$class])) {
			list($ctobj, $created) = ContentType::get_or_create(array("name"=>$class));
			if ($created) {
				$ctobj->version = $obj->get_version();
				$ctobj->save();
			}
			$ctypes[$class] = $ctobj;
		}
		return $ctypes[$class];
	}
}