<?php
/**
 * Tikapot Internationalisation
 *
 * @author James Thompson
 * @package Tikapot\Framework
 */

require_once(home_dir . "framework/config_manager.php");

/**
 * Tikapot Internationalisation Base Class
 *
 * @package Tikapot\Framework
 */
class i18n implements Iterator, Countable, arrayaccess
{
	/** The i18n language map for this object */
	private $map;
	
	/**
	 * Constructs a new i18n object with a given map
	 *
	 * @param array $map A map containing references and translations
	 */
	public function __construct($map) {
		if (is_array($map)) {
			$this->map = $map;
		}
	}
	
	/**
	 * Initialize the i18n subsystem
	 *
	 * @internal
	 */
	public static function Init() {
		global $app_paths, $apps_list;
		Profiler::start("load_i18n");
		
		// Decide on file to load
		$file = isset($_SESSION['lang']) ? $_SESSION['lang'] : ConfigManager::get('default_i18n', "en");
		$file = str_replace(".", "", $file);
		@setlocale(LC_ALL, $file);
		
		$GLOBALS['i18n'] = array();
		
		// Load Framework i18n
		$filename = home_dir . "framework/i18n/" . $file . ".php";
		if (file_exists($filename))
			require($filename);
		else
			require(home_dir . "framework/i18n/en.php");
		$GLOBALS['i18n']['framework'] = $i18n_data;
		$i18n_data = array();
		
		// Per-App i18n
		foreach ($apps_list as $app) {
			foreach ($app_paths as $app_path) {
				$dir = home_dir . $app_path . "/" . $app . "/i18n/";
				$filename = $dir . $file . ".php";
				if (!file_exists($filename))
					$filename = $dir . "en.php";
				if (file_exists($filename)) {
					include($filename);
					$GLOBALS['i18n'][$app] = $i18n_data;
					$i18n_data = array();
					break;
				}
			}
		}
		Profiler::end("load_i18n");
	}
	
	/**
	 * Used to access entries in the map
	 *
	 * @param string $name A reference
	 */
	public function __get($name) {
		return $this->map[$name];
	}

	/**
	 * Used to check entries in the map exist
	 *
	 * @param string $name A reference
	 */
	public function __isset($name) {
		return isset($this->map[$name]);
	}

	/**
	 * Converts the map to JS
	 *
	 * @internal
	 * @param string $name A name for the JS array
	 * @param string $val A map
	 * @return string The Javascript for this map
	 */
	private function toJS($name, $val) {
		if (is_array($val)) {
			$js = "";
			foreach ($val as $_name => $_val)
				$js .= $this->toJS($_name, $_val);
			return $js;
		}
		$name = str_replace(" ", "_", $name);
		if (!preg_match("/^[a-z]/", $name))
			$name = "i" . $name;
		$val = str_replace("'", "\\'", $val);
		$val = str_replace("\n", "\\n", $val);
		return "i18n." . $name . " = '".$val."';\n";
	}

	/**
	 * Converts the current map to JS
	 *
	 * @return string The Javascript for this map
	 */
	public function buildJS() {
		return "var i18n = new Object();\n" . $this->toJS("", $this->map);
	}
	
	/**
	 * The number of elements in the current map
	 *
	 * @internal
	 * @return integer The number of elements in the current map
	 */
	public function count() {
		return count($map);
	}
	
	/**
	 * An Iterator interface implementation
	 *
	 * @internal
	 */
	public function rewind() {
		reset($this->map);
	}
	
	/**
	 * An Iterator interface implementation
	 *
	 * @internal
	 */
	public function current() {
		return current($this->map);
	}
	
	/**
	 * An Iterator interface implementation
	 *
	 * @internal
	 */
	public function key() {
		return key($this->map);
	}
	
	/**
	 * An Iterator interface implementation
	 *
	 * @internal
	 */
	public function next() {
		return next($this->map);
	}
	
	/**
	 * An Iterator interface implementation
	 *
	 * @internal
	 */
	public function valid() {
		$key = key($this->map);
		return $key !== NULL && $key !== FALSE;
	}
	
	/**
	 * An Array interface implementation
	 *
	 * @param string $offset An offset
	 * @param string $value A value
	 * @internal
	 */
	public function offsetSet($offset, $value) {
		if (is_null($offset)) {
			$this->map[] = $value;
		} else {
			$this->map[$offset] = $value;
		}
	}
	
	/**
	 * An Array interface implementation
	 *
	 * @param string $offset An offset
	 * @internal
	 */
	public function offsetExists($offset) {
		return isset($this->map[$offset]);
	}
	
	/**
	 * An Array interface implementation
	 *
	 * @param string $offset An offset
	 * @internal
	 */
	public function offsetUnset($offset) {
		unset($this->map[$offset]);
	}
	
	/**
	 * An Array interface implementation
	 *
	 * @param string $offset An offset
	 * @internal
	 */
	public function offsetGet($offset) {
		if (isset($this->map[$offset]))
			return $this->map[$offset];
		return debug ? "#mtrns#" : "";
	}
}

