<?php
/**
 * Tikapot Config Manager
 *
 * @author James Thompson
 * @package Tikapot\Framework
 */

require_once(home_dir . "framework/models.php");

/**
 * ConfigException
 *
 */
class ConfigException extends Exception {}
/**
 * AppCacheException
 *
 */
class AppCacheException extends ConfigException {}
/**
 * CacheKeyException
 *
 */
class CacheKeyException extends ConfigException {}

/**
 * ConfigManager
 *
 * @package Tikapot\Framework
 */
class ConfigManager
{
	private static /** The internal cache */ 	$cache = array(), 
	               /** The app vars */ 			$app_vars = array(),
	               /** The internal cache */ 	$app_cache = array();

	/**
	 * Before you can have a configuration for your app you must register it.
	 * This function allows you to do that.
	 *
	 * @static
	 * @param string $app The name of the requesting application
	 * @param string $key The name of the config variable
	 * @param string $default_value The default value for this variable
	 * @return void
	 */
	public static function register_app_config($app, $key, $default_value) {
		if (!isset(ConfigManager::$app_vars[$app]))
			ConfigManager::$app_vars[$app] = array();
		ConfigManager::$app_vars[$app][$key] = $default_value;
		ConfigManager::$app_cache[$app] = array();
	}

	/**
	 * Goes through all configs and ensures theyre saved in the database.
	 * This should not be called very often as it is database-intensive.
	 *
	 * @static
	 * @internal
	 * @return void
	 */
	public static function init_app_configs() {
		foreach (ConfigManager::$app_vars as $app => $arr) {
			foreach ($arr as $key => $val) {
				list($obj, $created) = App_Config::get_or_create(array("app" => $app, "key" => $key));
				if ($created) {
					$obj->value = $val;
					$obj->save();
				}
				ConfigManager::$app_cache[$app][$key] = $obj->value;
			}
		}
	}

	/**
	 * Sets a new value for an application's config variable
	 *
	 * @static
	 * @param string $app The name of the requesting application
	 * @param string $key The name of the config variable
	 * @param string $value The new value for the application config
	 * @return void
	 */
	public static function set_app_config($app, $key, $value) {
		// Shouldnt exist!
		if (!isset(ConfigManager::$app_vars[$app][$key])) {
			console_log($GLOBALS['i18n']['framework']['appcachewarn'] . $app . "." . $key);
			return "";
		}
		
		// Get, update and cache
		list($obj, $created) = App_Config::get_or_create(array("app" => $app, "key" => $key));
		$obj->value = $value;
		$obj->save();
		ConfigManager::$app_cache[$app][$key] = $value;
	}

	/**
	 * Get a given config variable for a specified application
	 *
	 * @static
	 * @param string $app The name of the requesting application
	 * @param string $key The name of the config variable
	 * @return string|null The value or null if it didnt exist
	 */
	public static function get_app_config($app, $key) {
		// Does it exist?
		if (!isset(ConfigManager::$app_vars[$app][$key])) {
			console_log($GLOBALS['i18n']['framework']['appcachewarn'] . $app . "." . $key);
			return null;
		}
		
		// Try getting from cache
		if (isset(ConfigManager::$app_cache[$app][$key]))
			return ConfigManager::$app_cache[$app][$key];
		
		// Get or Create the given config, save the default value if its new
		list($obj, $created) = App_Config::get_or_create(array("app" => $app, "key" => $key));
		if ($created) {
			$obj->value = ConfigManager::$app_vars[$app][$key];
			$obj->save();
		}
		ConfigManager::$app_cache[$app][$key] = $obj->value;
		return $obj->value;
	}

	/**
	 * Returns every application configuration
	 *
	 * @static
	 * @internal
	 * @return void
	 */
	public static function get_all_app_configs() {
		$configs = ConfigManager::$app_vars;
		$objs = App_Config::objects();
		foreach ($objs as $obj) {
			$app = $obj->_app->__toString();
			if (isset($configs[$app]) && isset($configs[$app][$obj->key])) {
				$configs[$app][$obj->key] = ConfigManager::get_app_config($app, $obj->key);
			}
		}
		return $configs;
	}

	/**
	 * Set a standard configuration variable
	 *
	 * @static
	 * @param string $key The name of the config variable
	 * @param string $value The new value for this variable
	 * @return Config The config object that was created
	 */
	public static function set($key, $value) {
		list($obj, $created) = Config::get_or_create(array("key" => $key));
		$obj = $value;
		$obj->save();
		ConfigManager::$cache[$key] = $value;
		return $obj;
	}
	
	/**
	 * Get a standard configuration variable
	 *
	 * @static
	 * @param string $key The name of the config variable
	 * @param string $default An override if the variable hasnt been set (defaults to null)
	 * @return string The value of the config variable or $default if it hasnt been set
	 */
	public static function get($key, $default = null) {
		global $tp_options;
		if (isset($tp_options[$key]))
			return $tp_options[$key];
		if (isset(ConfigManager::$cache[$key]))
			return ConfigManager::$cache[$key];
		// Is it in the database?
		$obj = Config::get_or_ignore(array("key" => $key));
		if ($obj) {
			ConfigManager::$cache[$key] = $obj->value;
			return $obj->value;
		}
		return $default;
	}
	
	/**
	 * Similar to get(...) except this throws an exception if the variable doesnt exist
	 * 
	 * @see get
	 * @static
	 * @param string $key The name of the config variable
	 * @return string The value of the config variable
	 */
	public static function get_or_except($key) {
		$val = ConfigManager::get($key, null);
		if ($val === null)
			throw new ConfigException($GLOBALS['i18n']['framework']['config_except'] . $key);
		return $val;
	}
	
	/**
	 * Get App List is a utility function to get a list of all visible apps
	 *
	 * @todo 1.2 This will be moved to utils
	 * @internal
	 * @static
	 * @return array An array of apps
	 */
	public static function get_app_list() {
		if (isset(ConfigManager::$cache["int_app_list"]))
			return ConfigManager::$cache["int_app_list"];
		global $app_paths, $apps_list;
		$apps = array();
		foreach ($app_paths as $app_path) {
			foreach ($apps_list as $app) {
				$path = home_dir . $app_path . '/' . $app . '/';
				if (file_exists($path)) {
					$apps[] = $app_path . '/' . $app;
				}
			}
		}
		ConfigManager::$cache["int_app_list"] = $apps;
		return $apps;
	}
}

?>
