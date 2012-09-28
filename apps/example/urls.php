<?php
/*
 * Tikapot Example App URLs
 *
 */

require_once(home_dir . "apps/example/views.php");
require_once(home_dir . "framework/view.php");
require_once(home_dir . "framework/views/init.php");

// Load views
new View("/", home_dir . "apps/example/templates/index.php");
new View("/test/", home_dir . "apps/example/templates/tests.php");

?>

