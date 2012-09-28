<?php
/**
 * Tikapot Session Class
 *
 * @author James Thompson
 * @package Tikapot\Framework
 */

/**
 * Session contains helper functions for dealing with sessions
 *
 * @package Tikapot\Framework
 */
class Session
{
	/**
	 * Start a new session
	 */
	public static function start() {
		// Set session options
		$currentCookieParams = session_get_cookie_params(); 
		session_set_cookie_params($currentCookieParams["lifetime"], home_url, $currentCookieParams["domain"], $currentCookieParams["secure"], $currentCookieParams["httponly"]);

		session_start();
	}

	/**
	 * Store val in session under var, overwrites if necessary.
	 *
	 * @static
	 * @param string $key The name of the session element
	 * @param mixed $val The value of the session element
	 * @return string Previous value if it existed, or the new value if it didnt.
	 */
	static function store($key, $val) {
		$ret = $val;
		if (isset($_SESSION[$key]))
			$ret = $_SESSION[$key];
		$_SESSION[$key] = $val;
		return $ret;
	}
	
	/**
	 * Create a new session variable if it doesnt already exist
	 *
	 * @static
	 * @param string $key The name of the session element
	 * @param mixed $val The value of the session element
	 * @return boolean True if it added the variable, false if not
	 */
	static function put($key, $val) {
		if (isset($_SESSION[$key]))
			return false;
		Session::store($key, $val);
		return true;
	}
	
	/**
	 * Get a session variable
	 *
	 * @static
	 * @param string $key The name of the session element
	 * @return mixed|null The value of the session element or null
	 */
	static function get($key) {
		if (isset($_SESSION[$key]))
			return $_SESSION[$key];
		return null;
	}
	
	/**
	 * Deletes var from the session. Returns the old value (or null if it didnt exist)
	 *
	 * @static
	 * @param string $key The name of the session element
	 * @return mixed|null
	 */
	static function delete($key) {
		if (isset($_SESSION[$key])) {
			$ret = $_SESSION[$key];
			unset($_SESSION[$key]);
			
			// Remove key too
			$new_session = array();
			foreach ($_SESSION as $_key => $value)
				if ($_key !== $key)
					$new_session[$_key] = $value;
			$_SESSION = $new_session;
			return $ret;
		}
		return null;
	}
}

?>

