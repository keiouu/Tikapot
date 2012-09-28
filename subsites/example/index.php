<?php
/*
 * Subsite Entry Point
 */
 
define("subsite", "example");
define("bootstrap", false);

require_once("../../index.php");

if (file_exists("config.php")) {
	require_once("config.php");
}

require_once(home_dir . "framework/init.php");
?>
