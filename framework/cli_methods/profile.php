<?php
/**
 * CLI Profiling Functions
 * Run by typing: php index.php profile
 *
 * @author James Thompson
 * @package Tikapot\Framework\CLI_Methods
 */

require_once(home_dir . "framework/cli_method.php");
require_once(home_dir . "framework/cli_manager.php");
require_once(home_dir . "framework/database.php");
require_once(home_dir . "framework/model.php");
require_once(home_dir . "framework/model_fields/init.php");

/**
 * Test model for Profiler
 */
class ProfilerTests extends Model
{
	/**
	 * Construct
	 */
	public function __construct() {
		parent::__construct();
		$this->add_field("key", new CharField(250, ""));
		$this->add_field("value", new CharField(250, ""));
	}
}

/**
 * CLI_Profile class
 */
class CLI_Profile implements CLI_Method
{
	/**
	 * The override to provide help for this method
	 *
	 * @param array $args Any arguments passed to the "help profile" command
	 */
	public function print_help($args) {
		print $GLOBALS['i18n']['framework']['cli_profile_help'];
	}
	
	/**
	 * Run override to run this command
	 *
	 * @param array $args Any arguments that have been passed to this method
	 */
	public function run($args) {
		console_log("~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~\n");
		console_log("Welcome to the Tikapot Profiler!\n");
		console_log("~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~\n");
		console_log("\n");

		// Try to connect to DB
		console_log("Attempting to form Database connection... ");
		if (!Database::create()) {
			console_log("failed!\nCould not connect to database! Aborting...\n");
		}
		console_log("success!\n");

		// Run tests
		console_log("\n");
		$this->virtual_model_creation();
		console_log("\n");
		$this->solid_model_creation();
		console_log("\n");
		$this->model_lookup();
		console_log("\n");

		// Delete all ProfilerTests objects
		console_log("Deleting Objects...\n");
		$tests = new ProfilerTests();
		$db = Database::create($tests->get_db());
		$db->query("DELETE FROM \"".$tests->get_table_name($db)."\";");


		console_log("Profiling Complete, Goodbye!\n");
	}

	/**
	 * Virtual Model Creation Test
	 */
	private function virtual_model_creation() {
		console_log("Running Virtual Model Creation test...\n");
		console_log("Creating 1000 virtual test objects...\n");

		$time = microtime(True);
		for ($i = 0; $i < 1000; $i++) {
			$o = new ProfilerTests();
			$o->key = "a";
			$o->value = "a";
		}
		$time = microtime(True) - $time;

		console_log("Virtual Model Creation test completed in " . round($time, 4) . " seconds!\n");
	}

	/**
	 * Solid Model Creation Test
	 */
	private function solid_model_creation() {
		console_log("Running Solid Model Creation test...\n");
		console_log("Creating 1000 test objects...\n");

		$time = microtime(True);
		for ($i = 0; $i < 1000; $i++) {
			ProfilerTests::create(array(
				"key" => "a",
				"value" => "b"
			));
		}
		$time = microtime(True) - $time;

		console_log("Solid Model Creation test completed in " . round($time, 4) . " seconds!\n");
	}

	/**
	 * Model Lookup Test
	 */
	private function model_lookup() {
		console_log("Running Model Lookup test...\n");
		console_log("Looking up all test objects...\n");

		$time = microtime(True);
		ProfilerTests::objects()->all();
		$time = microtime(True) - $time;

		console_log("Model Lookup test completed in " . round($time, 4) . " seconds!\n");

		$time = microtime(True);
		ProfilerTests::objects()->all();
		$time = microtime(True) - $time;

		console_log("Secondary Model Lookup test completed in " . round($time, 4) . " seconds!\n");
	}
}

CLI_Manager::register_command($GLOBALS['i18n']['framework']['cli_profile'], new CLI_Profile());
