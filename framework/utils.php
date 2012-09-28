<?php
/**
 * Tikapot Utilities
 *
 * @author James Thompson
 * @package Tikapot\Framework
 */

/**
 * Does a string start with the needle?
 * 
 * @param  string $haystack The string to check
 * @param  string $needle   The needle to check against
 * @return boolean
 */
function starts_with($haystack, $needle) {
	return ($needle != "") && substr($haystack, 0, strlen($needle)) === $needle;
}

/**
 * Does a string end with a needle?
 * 
 * @param  string $haystack The string to check
 * @param  string $needle   The needle to check against
 * @return boolean
 */
function ends_with($haystack, $needle) {
	return strrpos($haystack, $needle) === strlen($haystack)-strlen($needle);
}

/**
 * Takes a string, and a needle, and gives you what was on the left and right, as well as returning the needle.
 * Basically mimic's Python's partition
 * 
 * @param  string $haystack Haystack is the string to partition
 * @param  string $needle   The point of partition
 * @return array (leftside, middle, rightside)
 */
function partition($haystack, $needle) {
	$pos = strpos($haystack, $needle);
	if ($pos !== false)
		return array(substr($haystack, 0, $pos), $needle, substr($haystack, $pos + strlen($needle), strlen($haystack)));
	return array($haystack, $needle, "");
}

/**
 * This returns a class, given a name and (optional) app_name
 * 
 * @internal
 * @param  string $class    The name of the class to get
 * @param  string $app_name The name of the application it is expected to be in
 * @return Object|Null
 */
function get_named_class($class, $app_name = null) {
	if (!class_exists($class)) {
		global $app_paths;
		foreach ($app_paths as $app_path) {
			$path = home_dir . $app_path . '/';
			if (file_exists($path) && ($handle = opendir($path))) {
				while (($entry = readdir($handle))  !== false) {
					if ($app_name !== null && $app_name !== $entry)
						continue;
					if ($entry !== "." && $entry !== "..") {
						$file = $path . $entry . "/models.php";
						if (is_file($file)) {
							include_once($file);
							if (class_exists($class))
								break;
						}
					}
				}
				closedir($handle);
			}
		}
	}
	if (class_exists($class))
		return new $class();
	return null;
}

/**
 * Get a file extension for a given file
 * 
 * @param  string $filename  The name of the file
 * @param  string $delimiter The delimiter to use (defaults to '.')
 * @return string The file exension (e.g. 'png')
 */
function get_file_extension($filename, $delimiter = ".") {
	return substr(strrchr($filename, $delimiter), 1);
}

/**
 * Returns a filename without the extension, e.g. "/home/example.png" would return "example"
 * 
 * @param string $filename The filename as we currently know it
 * @param string $delimiter (optional) The delimiter (e.g. a '.' for a standard filename). Most of the time you should ignore this.
 * @return string The base filename, without an extension
 */
function get_file_name($filename, $delimiter = ".") {
	$filename = basename($filename, "." . get_file_extension($filename, $delimiter));
	$pos = strpos($filename, $delimiter);
	if ($pos === 0 && strrpos($filename, $delimiter) == $pos)
		return "";
	return $filename;
}

/**
 * Attempts to sanitize a string for use in email sending (header injection, etc)
 * @param  string $str The unsanitised string
 * @return string The sanitised string
 */
function email_sanitize($str) {
	$injections = array(
		'/(\n+)/i',
		'/(\r+)/i',
		'/(\t+)/i',
		'/(%0A+)/i',
		'/(%0D+)/i',
		'/(%08+)/i',
		'/(%09+)/i'
	);
	return preg_replace($injections, '', $str);
}

/**
 * Prettifys some Text, converting "THIS_EXample" to "This Example"
 * 
 * @param  string $string The string to prettify
 * @return string
 */
function prettify($string) {
	// Add underscores before capitol letters. Turns "AnExample" into "An_Example"
	// An underscore is more reliable than a space
	$string = preg_replace('/([a-z])([A-Z])/', '$1_$2', $string);
	// Turn underscores from above, and before that, into spaces
	$string = str_replace("_", " ", $string);
	// Upperwords!
	$string = ucwords($string);
	return $string;
}

/**
 * Is this a URL?
 * 
 * @param  string $url The string to check
 * @return boolean
 */
function urlCheck($url) {
	if (function_exists('idn_to_ascii'))
		$url = idn_to_ascii($url);
	return filter_var($url, FILTER_VALIDATE_URL);
}

/**
 * Cuts text off as close as possible to the end of the string, given a maximum length of characters.
 * Detects word boundaries and attempts to cut off at words.
 * 
 * @param  string $string The string to ellipsize
 * @param  integer $length The desired length of the string
 * @return string
 */
function ellipsize($string, $length) {
	if (strlen($string) <= $length)
		return $string;
	$new_string = "";
	foreach (explode(" ", $string) as $word) {
		$result = $new_string . ($new_string == "" ? "" : " ") . $word;
		if ($new_string == "" || strlen($result) <= $length - 3)
			$new_string = $result;
	}
	return substr($new_string, 0, $length - 3) . "...";
}

/**
 * Removes a directory. Use with caution!
 *
 * @internal
 * @param  string $dir The directory to wipe
 * @return null
 */
function rmrf($dir) {
	if (!is_dir($dir))
		return;
	$objects = scandir($dir);
	foreach ($objects as $object) {
		if ($object == "." || $object == "..")
			continue;
		
		if (is_dir($dir . "/" . $object))
			rmrf($dir . "/" . $object);
		else
			unlink($dir . "/" . $object);
	}
	rmdir($dir);
}

/**
 * Attempts to fix up unicode strings by converting unknown characters to html entities
 * 
 * @param  string $str UTF32 string
 * @return string UTF8 string
 */
function string_encode($str) {
    $str = mb_convert_encoding($str , 'UTF-32', 'UTF-8');
    $t = unpack("N*", $str);
    $t = array_map(function($n) { return "&#$n;"; }, $t);
    return implode("", $t);
}

/**
 * Fetch a remote page
 * 
 * @param string $url The url to fetch
 * @param int $cache Cache this query? If 0, dont cache. Otherwise, cahce for this number of seconds.
 * @return string page contents
 */
function fetch($url, $cache = 0) {
	if ($cache !== 0) {
		require_once(home_dir . "framework/tpcache.php");
		$cache_val = TPCache::get($url);
		if ($cache_val !== false)
			return $cache_val;
	}
	
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	$ret = curl_exec($ch);
	curl_close($ch);
	
	if ($cache !== 0) {
		TPCache::set($url, $ret, $cache);
	}
	
	return $ret;
}

/**
 * Turns console logging on
 */
function console_on() {
	$GLOBALS['enable_console'] = true;
}

/**
 * Turns console logging off
 */
function console_off() {
	$GLOBALS['enable_console'] = false;
}

/**
 * Analyze a variable and print to console
 * 
 * @param  Mixed $var A variable to analyze
 * @return null
 */
function analyze($var) {
	ob_start();
	print_r($var);
	console_log(ob_get_clean());
}

/**
 * Log an entry in the console
 * 
 * @param  string $val A string to add to console
 * @return null
 */
function console_log($val) {
	if (isset($GLOBALS['enable_console']) && !$GLOBALS['enable_console']) {
		return;
	}
	
	if (!isset($GLOBALS['console'])) {
		$GLOBALS['console'] = array();
	}
	
	$GLOBALS['console'][] = $val;
	if (PHP_SAPI === 'cli') {
		print $val;
	}
}

/**
 * Send a warning to the console
 * @param  string $val The variable to add to the console
 * @return null
 */
function console_warning($val) {
	console_log('<span class="console_warning">'.$val.'</span>');
}

/**
 * Send a warning to the console
 * @param  string $val The variable to add to the console
 * @return null
 */
function console_error($val) {
	console_log('<span class="console_error">'.$val.'</span>');
}

/**
 * Alias of analyze()
 *
 * @see  analyze()
 * @param  Mixed $var A variable to analyze
 * @return null
 */
function console_inspect($var) {
	ob_start();
	print_r($var);
	console_log(ob_get_clean());
}

/**
 * Log a message related to caching to the console
 * 
 * @param  string $val The variable to add to the console
 * @return null
 */
function console_cache($val) {
	console_log('<span class="console_cache">'.$val.'</span>');
}

/**
 * Trigger an E_USER_DEPRECATD error
 *
 * @param string $method The name of the deprecated method
 * @param string $new The new method that replaces it (if any)
 */
function console_deprecation($method, $new = "") {
    $backtrace_data = debug_backtrace();
	$backtrace = " In: " . $backtrace_data[1]["file"] . " (line " . $backtrace_data[1]["line"] . ")";
	if ($new !== "")
		trigger_error($method . "() deprecated, use ".$new."() instead." . $backtrace, E_USER_DEPRECATED);
	else
		trigger_error($method . "() deprecated." . $backtrace, E_USER_DEPRECATED);
}
