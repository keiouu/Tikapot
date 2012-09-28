<?php
/**
 * Tikapot Model Cache System
 * 
 * @author James Thompson
 * @package Tikapot\Framework
 */

/**
 * Provides cross-model links and object caching.
 */
class ModelCache
{
	private static /** A cache of objects */ $_cache;

	/**
	 * Set the cache entry for a model object
	 * @param Model $obj The model object to cache
	 * @return boolean True if set, otherwise false.
	 */
	public static function set($obj) {
		// Must be from the database!
		if (!$obj->from_db()) {
			return false;
		}

		$class = get_class($obj);
		$pk = $obj->pk;

		if (!isset(ModelCache::$_cache[$class])) {
			ModelCache::$_cache[$class] = array();
		}

		if (isset(ModelCache::$_cache[$class][$pk])) {
			return true;
		}

		ModelCache::$_cache[$class][$pk] = $obj;
		return true;
	}

	/**
	 * Get an object from this cache
	 * @param  string $class Class name to go after
	 * @param  string|integer $pk    The primary key for the object
	 * @return Model|null        The model object in the cache or null if not found
	 */
	public static function get($class, $pk) {
		global $tp_options;
		return $tp_options["enable_modelcache"] == true && isset(ModelCache::$_cache[$class][$pk]) ? ModelCache::$_cache[$class][$pk] : null;
	}

	/**
	 * Delete the cache entry for a model object
	 * @param Model $obj The model object to delete
	 */
	public static function delete($obj) {
		// Must be from the database!
		if (!$obj->from_db()) {
			return false;
		}

		$class = get_class($obj);
		if (isset(ModelCache::$_cache[$class])) {
			unset(ModelCache::$_cache[$class][$obj->pk]);
		}
	}
}