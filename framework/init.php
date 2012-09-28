<?php
/**
 * Tikapot Core Framework Setup Script
 *
 * @author James Thompson
 * @package Tikapot\Framework
 */

define("tikapot_version", 'Tikapot 2.1');

// First, set the error handler
require_once(home_dir . "framework/error_handler.php");
set_error_handler("tpErrorHandler");

require_once(home_dir . "framework/utils.php");
require_once(home_dir . "framework/tpcache.php");

if (defined("enable_session_handler") && enable_session_handler && TPCache::avaliable()) {
	require_once(home_dir . "framework/session_handler.php");
	$handler = new MemcachedSession();
	session_set_save_handler(
	    array($handler, 'open'),
	    array($handler, 'close'),
	    array($handler, 'read'),
	    array($handler, 'write'),
	    array($handler, 'destroy'),
	    array($handler, 'gc')
	);
	register_shutdown_function('session_write_close');
}

ob_start();
@session_start();

require_once(home_dir . "framework/profiler.php");
Profiler::start("total");

require_once(home_dir . "framework/config_manager.php");
require_once(home_dir . "framework/i18n.php");

global $app_paths, $apps_list;

/* Setup i18n */
i18n::Init();

/* Start up the signal manager, register some signals */
require_once(home_dir . "framework/signal_manager.php");
SignalManager::register("page_load_start", "page_load_setup", "page_load_render", "page_load_failure", "page_load_setup_failure", "page_load_end");

/* Should we load the console post-processor? */
if (debug) {
	require_once(home_dir . "framework/processing/processors/console_pre_processor.php");
	new Console_Pre_Processor();
	require_once(home_dir . "framework/processing/processors/console_post_processor.php");
	new Console_Post_Processor();
}

/* Start up the view manager */
require_once(home_dir . "framework/view_manager.php");
$view_manager = new ViewManager();
require_once(home_dir . "framework/urls.php");

/* Load the apps */
Profiler::start("load_apps");
foreach ($apps_list as $app) {
	foreach ($app_paths as $app_path) {
		$filename = home_dir . $app_path . "/" . $app . "/init.php";
		if (file_exists($filename)) {
			include($filename);
			break;
		}
	}
}
Profiler::end("load_apps");

/* Create the request */
require_once(home_dir . "framework/request.php");
$request = new Request();

/* Check if we are CLI calling a method */
if (PHP_SAPI === 'cli') {
	require_once(home_dir . "framework/cli_manager.php");
	require_once(home_dir . "framework/cli_methods/init.php");
	$args = $request->cmd_args;
	if (!isset($args[page_def])) {
		global $argv;
		if (isset($argv[1]) && CLI_Manager::has_command($argv[1])) {
			ob_get_clean();
			unset($args[$argv[1]]);
			unset($args[0]);
			CLI_Manager::parse($argv[1], $args);
			return;
		}
	}
}

/* Set mimetype, if known */
if ($request->mimeType !== "unknown")
	header('Content-type: ' . $request->mimeType);

try {
	Profiler::start("render_page");
	SignalManager::fire("page_load_setup", $request);
	SignalManager::fire("page_load_start", $request);

	/* Setup the page */
	$request->output = "";
	Profiler::start("page_setup");
	ob_start();
	$setup_result = $view_manager->setup($request);
	$setup_output = ob_get_clean();
	Profiler::end("page_setup");

	if ($setup_result) {
		/* Render the page */
		SignalManager::fire("page_load_render", $request);
		ob_start();
		Profiler::start("page_render");
		$view_manager->render($request);
		Profiler::end("page_render");
		$request->output = ob_get_clean();
	} else {
		SignalManager::fire("page_load_setup_failure", $request);
		ob_start();
		$view_manager->render_setup_fail($request);
		$request->output = ob_get_clean();
	}
	
	Profiler::end("render_page");
	Profiler::end("total");
	
	SignalManager::fire("page_load_end", $request);

	while (ob_get_length() > 0)
		ob_get_clean();

	if (strlen(trim($request->output)) !== 0) {
		print $request->output;
	} else {
		if (strlen($setup_output) > 0) { // If there is no output from render or render_setup_fail but there is from setup, just print that..
			print $setup_output;
		}
	}
} catch (Exception $e) {
	SignalManager::fire("page_load_failure", $request);
	while (ob_get_length() > 0)
		ob_get_clean();
	$error = new ErrorView();
	print $error->pre_render($request, $e);
	print $error->render($request, $e);
	print $error->post_render($request, $e);
	Profiler::end("total");
}
