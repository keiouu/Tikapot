<?php
/**
 * Tikapot CLI "Test" Method
 * Run by typing: php index.php test
 *
 * @author James Thompson
 * @package Tikapot\Framework\CLI_Methods
 */

require_once(home_dir . "framework/cli_method.php");
require_once(home_dir . "framework/cli_manager.php");
require_once(home_dir . "lib/simpletest/test_case.php");
require_once(home_dir . "lib/simpletest/unit_tester.php");

/**
 * CLI_Test class
 *
 * @package Tikapot\Framework\CLI_Methods
 */
class CLI_Test implements CLI_Method
{
	/**
	 * The override to provide help for this method
	 *
	 * @param array $args Any arguments passed to the "help test" command
	 */
	public function print_help($args) {
		print $GLOBALS['i18n']['framework']['cli_test_help'];
	}
	
	/**
	 * Discover any tests.php files in $dir
	 *
	 * @internal
	 * @param string $dir The directory to search
	 * @param TestSuite $suite The test suite to add files too
	 */
	private function _test_discover($dir, $suite) {
		$objects = scandir($dir);
		foreach ($objects as $object) {
			if ($object == "." || $object == "..")
				continue;
			if (is_dir($dir . $object)) {
				if (file_exists($dir . $object . "/tests.php"))
					$suite->addFile($dir . $object . "/tests.php");
			}
		}
	}
	
	/**
	 * Run override to run this command
	 *
	 * @param array $args Any arguments that have been passed to this method
	 */
	public function run($args) {
		if (count($args) == 0)
			$args = array("all");
		
		$suite = new TestSuite("Tikapot " . $GLOBALS['i18n']['framework']['tests']);		
		foreach ($args as $arg) {
			if ($arg === "")
				continue;
			switch ($arg) {
				case "all":
					$suite->addFile(home_dir . "framework/tests.php");
					global $app_paths;
					foreach ($app_paths as $app_path) {
						$this->_test_discover(home_dir . $app_path . "/", $suite);
					}
					break;
				case "framework":
					$suite->addFile(home_dir . "framework/tests.php");
					break;
				default:
					global $app_paths;
					foreach ($app_paths as $app_path) {
						if ($arg == $app_path) {
							$this->_test_discover(home_dir . $arg . "/", $suite);
							break;
						}
						if (file_exists(home_dir . $app_path . "/".$arg."/tests.php"))
							$suite->addFile(home_dir . $app_path . "/".$arg."/tests.php");
					}
					break;
			}
		}
		$suite->run(new TextReporter());
	}
}

CLI_Manager::register_command($GLOBALS['i18n']['framework']['cli_test'], new CLI_Test());
