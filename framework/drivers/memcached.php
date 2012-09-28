<?php
/**
 * This is the Memcached Driver for TPCache
 * 
 * @author James Thompson
 * @package Tikapot\Framework
 */

require_once(home_dir . "framework/drivers/cache_driver.php");

/**
 * This is the Memcached Driver for TPCache
 */
class MemcachedDriver extends CacheDriver
{
	/** The cache engine (Memcached class) */
	private $engine;

	/**
	 * Constructor
	 * @param array $settings The settings to initialize with
	 */
	public function __construct($settings) {
		$this->engine = new Memcached(project_name);
		$this->engine->addServer($settings["host"], $settings["port"]);
		$this->engine->setOption(Memcached::OPT_PREFIX_KEY, $settings['prefix']);
	}

	/**
	 * Is this cache driver available?
	 * 
	 * @param array $settings The settings to check with
	 * @return boolean True/False
	 */
	public function available($settings) {
		return $settings['type'] == 'memcached' && class_exists("Memcached");
	}

	/**
	 * Returns the raw engine
	 * 
	 * @return Mixed
	 */
	public function getEngine() {
		return $this->engine;
	}

	/**
	 * Get a key from this cache
	 * 
	 * @param string $key The key to use
	 * @return Mixed The value related to the key or null if no value was associated
	 */
	public function get($key) {
		$result = $this->engine->get($key);
		if (debug_cache && $result) {
			console_cache($GLOBALS['i18n']['framework']["found"] . ": " . $key);
		}
		return $result;
	}

	/**
	 * Set a key on this cache
	 * 
	 * @param string $key The key to use
	 * @param string $value The value to use
	 * @param string $expire The expiry time to use, should be (time() + milliseconds)
	 * @return boolean True if success, false if failure
	 */
	public function set($key, $value, $expire = 0) {
		$result = $this->engine->set($key, $value, $expire);
		if (debug_cache && $result) {
			console_cache($GLOBALS['i18n']['framework']["set"] . ": " . $key);
		}
		return $result;
	}

	/**
	 * Delete a key from the cache
	 * 
	 * @param string $key The key to use
	 * @return null
	 */
	public function delete($key) {
		return $this->engine->delete($key);
	}
}