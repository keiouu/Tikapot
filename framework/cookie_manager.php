<?php
/**
 * Tikapot Cookie Manager
 *
 * @author James Thompson
 * @package Tikapot\Framework
 */

require_once(home_dir . "framework/config_manager.php");

/**
 * Cookie Manager is used to easily set, get and delete cookies
 *
 * @package Tikapot\Framework
 */
class CookieManager
{
	/**
	 * Set a cookie
	 *
	 * @param string $key The name of the cookie
	 * @param string $value The new value of the cookie
	 * @param integer $duration The duration of the cookie in seconds (e.g. 60*60*4 for 4 hours)
	 * @param string $path The controlling path of the cookie
	 */
	public static function set($key, $value, $duration = 3600, $path = '/') {
		if (!ConfigManager::get("disable_cookies"))
			setcookie($key, $value, time() + $duration, $path);
	}
	
	/**
	 * Get a cookie
	 *
	 * @param string $key The name of the cookie
	 * @return string|null The value of the cookie, or null if it wasnt found
	 */
	public static function get($key) {
		if (!ConfigManager::get("disable_cookies"))
			return isset($_COOKIE[$key]) ? $_COOKIE[$key] : null;
		return null;
	}
	
	/**
	 * Delete a cookie
	 *
	 * @param string $key The name of the cookie
	 * @param string $path The controlling path of the cookie
	 */
	public static function delete($key, $path = '/') {
		if (!ConfigManager::get("disable_cookies"))
			setcookie($key, "", time() - 3600, $path);
	}
}

?>
