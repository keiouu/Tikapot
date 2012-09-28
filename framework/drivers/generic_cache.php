<?php
/**
 * This is a Generic Cache Driver
 * 
 * @author James Thompson
 * @package Tikapot\Framework
 */

require_once(home_dir . "framework/drivers/cache_driver.php");

/**
 * This is a Generic Cache Driver
 */
class GenericCacheDriver extends CacheDriver
{
	/** The cache */
	private $cache;

	/**
	 * Is this cache driver available?
	 * 
	 * @param array $settings The settings to check with
	 * @return boolean True
	 */
	public function available($settings) {
		return true;
	}

	/**
	 * Returns the raw engine (of which, this class has none)
	 * 
	 * @return Null
	 */
	public function getEngine() {
		return null;
	}

	/**
	 * Get a key from the cache
	 * 
	 * @param string $key The key to use
	 * @return Mixed The value related to the key or null if no value was associated
	 */
	public function get($key) {
		$result = isset($this->cache[$key]) ? $this->cache[$key] : null;
		if (debug_cache && $result) {
			console_cache($GLOBALS['i18n']['framework']["found"] . ": " . $key);
		}
		return $result;
	}

	/**
	 * Set a key on the cache
	 * 
	 * @param string $key The key to use
	 * @param string $value The value to use
	 * @param string $expire The expiry time to use, should be (time() + milliseconds)
	 * @return boolean True if success, false if failure
	 */
	public function set($key, $value, $expire = 0) {
		$this->cache[$key] = $value;
		if (debug_cache) {
			console_cache($GLOBALS['i18n']['framework']["set"] . ": " . $key);
		}
		return true;
	}

	/**
	 * Delete a key from the cache
	 * 
	 * @param string $key The key to use
	 * @return null
	 */
	public function delete($key) {
		unset($this->cache[$key]);
	}
}