<?php
/**
 * Tikapot Memcached Session Handler
 *
 * @author James Thompson
 * @package Tikapot\Framework
 */

require_once(home_dir . "framework/tpcache.php");

/**
 * A Memcached Session Handler
 */
class MemcachedSession
{
	/**
	 * Close the session
	 * 
	 * @return boolean True if success, false if failure
	 */
	public function close() {
		return true;
	}

	/**
	 * Destroy a given session
	 * 
	 * @param  string $session_id A session ID
	 * @return boolean True if success, false if failure
 	 */
	public function destroy(string $session_id) {
		$cache = TPCache::delete("SESSION_" . $session_id);
		return true;
	}

	/**
	 * Garbage collector, does nothing
	 * 
	 * @param  int    $maxlifetime The manimum lifetime of a session
	 * @return boolean True if success, false if failure
	 */
	public function gc(int $maxlifetime) {
		return true; // Memcached is already this awesome
	}

	/**
	 * Open a new session
	 * 
	 * @param  string $save_path  The path (not used)
	 * @param  string $session_id A sessionID
	 * @return boolean True if success, false if failure
	 */
	public function open(string $save_path, string $session_id) {
		return true;
	}

	/**
	 * Read from the session
	 * 
	 * @param  string $session_id Session ID
	 * @return Mixed             Data from the session var
	 */
	public function read(string $session_id) {
		return TPCache::get("SESSION_" . $session_id);
	}

	/**
	 * Write to the session
	 * 
	 * @param  string $session_id   Session ID
	 * @param  string $session_data The data to write
	 * @return boolean True if success, false if failure
	 */
	public function write(string $session_id, string $session_data) {
		return TPCache::set("SESSION_" . $session_id, $session_data, 86400);
	}
}