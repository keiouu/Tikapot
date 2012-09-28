<?php
/*
 * Tikapot Version 2.0
 * 
 * For installation instructions see README
 * For license information please see LICENSE
 */

// ---------------------------------
// Best not edit below this line!
// ---------------------------------
define("page_def", 'tpage'); // This must match the .htaccess file's redirect variable
define("home_dir", dirname(__FILE__) . '/');
define("home_url", substr($_SERVER['SCRIPT_NAME'], 0, strrpos($_SERVER['SCRIPT_NAME'], "/") + 1));

if (!file_exists(home_dir . "config.php")) {
	die("You must supply a config file!");
}

require_once(home_dir . "config.php");

if (debug) {
	ini_set('display_errors', '1');
}

if (!defined("bootstrap") || bootstrap) {
	require_once(home_dir . "framework/init.php");
}
