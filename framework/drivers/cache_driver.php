<?php
/**
 * This is a generic Cache Driver for TPCache
 * 
 * @author James Thompson
 * @package Tikapot\Framework
 */

abstract class CacheDriver
{
	/**
	 * Is this cache driver available?
	 * 
	 * @param array $settings The settings to check with
	 * @return boolean True/False
	 */
	public abstract function available($settings);

	/**
	 * Returns the raw engine
	 * 
	 * @return Mixed
	 */
	public abstract function getEngine();

	/**
	 * Get a key from the cache
	 * 
	 * @param string $key The key to use
	 * @return Mixed The value related to the key or null if no value was associated
	 */
	public abstract function get($key);

	/**
	 * Set a key on the cache
	 * 
	 * @param string $key The key to use
	 * @param string $value The value to use
	 * @param string $expire The expiry time to use, should be (time() + milliseconds)
	 * @return boolean True if success, false if failure
	 */
	public abstract function set($key, $value, $expire = 0);

	/**
	 * Delete a key from the cache
	 * 
	 * @param string $key The key to use
	 * @return null
	 */
	public abstract function delete($key);
}