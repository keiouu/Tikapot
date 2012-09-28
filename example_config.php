<?php
/**
 * Config file
 */
define("debug", true);
define("debug_cache", true);
define("debug_show_queries", false);

define("project_name", 'Tikapot');                    	// The name of your project
define("project_version", '2.0');                 		// The version string of your project (used for things like cache keys)

define("media_dir", home_dir . "media/");
define("media_url", home_url . "media/");
define("font_dir",  media_dir . "fonts/");
define("site_logo", media_url . "images/logo.png");   	// The URL to a logo for your project

if (PHP_SAPI !== 'cli') {
	define("site_url", $_SERVER['SERVER_ADDR']);      	// Change this to the URL of your website.
} else {
	define("site_url", "localhost");
}

/* Tikapot Options */
$tp_options = array(
	"dev_mode" => debug, // Enables some development-specific features in the core
	
	"disable_cookies" => false,
	
	"default_i18n" => "en",
	
	/* The following are used by TPCache */
	"enable_cache" => true,
	"cache_prefix" => "tp",
);

/* Databases */
$databases = array(
	"default" => array(
		"type" => "psql",
		"host" => "localhost",
		"port" => "",
		"name" => "",
		"username" => "",
		"password" => "",
		"timeout" => "5"
	)
);

/* Memcached */
$caches = array(
	"default" => array(
		"prefix" => "tp_",
		"type" => "memcached",
		"host" => "localhost",
		"port" => 11211
	)
);

$app_paths = array("apps");
$apps_list = array("example");
if (file_exists(home_dir . "app_list.php")) {
	require_once(home_dir . "app_list.php");
}

date_default_timezone_set("Europe/London");
