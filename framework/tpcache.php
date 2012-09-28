<?php
/**
 * Tikapot Memcached extension
 *
 * @author James Thompson
 * @package Tikapot\Framework
 */

require_once(home_dir . "framework/config_manager.php");
require_once(home_dir . "framework/utils.php");
require_once(home_dir . "framework/drivers/generic_cache.php");

/**
 * The main Caching class for Tikapot
 */
abstract class TPCache
{
	/** The cache engine store */
	static private $engines = array();

	/**
	 * Provision and store a Cache engine
	 * 
	 * @param string $name The name of this engine
	 * @param string $settings The settings of this engine
	 */
	private static function provision($name, $settings) {
		TPCache::$engines[$name] = null;
		switch ($settings['type']) {
			case 'memcached':
				require_once(home_dir . "framework/drivers/memcached.php");
				TPCache::$engines[$name] = new MemcachedDriver($settings);
				break;
			default:
				console_warning($GLOBALS['i18n']['framework']["cacheerr3"] . $name);
				break;
		}
	}
	
	/**
	 * Is a cache provider available?
	 * Defaults to the 'default' specification in config.php
	 * 
	 * @param string $name The config set to use
	 * @return boolean True/False
	 */
	public static function avaliable($name = "default") {
		if (!ConfigManager::get('enable_cache', true))
			return false;

		global $caches;

		if (!isset($caches[$name])) {
			console_warning($GLOBALS['i18n']['framework']["cacheerr2"] . $name);
			return false;
		}

		if (!isset(TPCache::$engines[$name])) {
			TPCache::provision($name, $caches[$name]);
		}

		$engine = TPCache::$engines[$name];
		if ($engine) {
			return $engine->available($caches[$name]);
		}

		return false;
	}
	
	/**
	 * Specify a cache engine to use
	 * 
	 * @param string $name The config set to use
	 * @return CacheDriver
	 */
	public static function using($name = 'default') {
		if (!TPCache::avaliable($name) && ConfigManager::get('enable_cache', true)) {
			if (!isset(TPCache::$engines['tp_generic_driver'])) {
				TPCache::$engines['tp_generic_driver'] = new GenericCacheDriver();
			}
			console_warning($GLOBALS['i18n']['framework']["cachegenericdriver"] . $name);
			return TPCache::$engines['tp_generic_driver'];
		}
		return TPCache::$engines[$name];
	}
	
	/**
	 * Get a key from this cache
	 * 
	 * @param string $key The key to use
	 * @return Mixed The value related to the key or null if no value was associated
	 */
	public static function get($key) {
		return TPCache::using('default')->get($key);
	}
	
	/**
	 * Set a key on this cache
	 * 
	 * @param string $key The key to use
	 * @param string $value The value to use
	 * @param string $expire The expiry time to use, should be (time() + milliseconds)
	 * @return boolean True if success, false if failure
	 */
	public static function set($key, $value, $expire = 0) {
		return TPCache::using('default')->set($key, $value, $expire);
	}

	/**
	 * Delete a key from the cache
	 * 
	 * @param string $key The key to use
	 * @return null
	 */
	public function delete($key) {
		return TPCache::using('default')->delete($key);
	}
	
	/**
	 * Depcrecated use "using()->getEngine()" instead
	 * @return null
	 */
	public static function getCache() {
		console_deprecation("TPCache::getCache", "TPCache::using");
		return TPCache::using()->getEngine();
	}
}
