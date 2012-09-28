<?php
/**
 * Tikapot CLI Manager
 *
 * @author James Thompson
 * @package Tikapot\Framework
 */

require_once(home_dir . "framework/cli_method.php");
require_once(home_dir . "framework/utils.php");

/**
 * A CLI Manager is used to register CLI commands, commands
 * which are used in the CLI interface only.
 *
 * @package Tikapot\Framework
 */
class CLI_Manager
{
	/** An array of registered CLI commands */
	private static $commands = array();
	
	/**
	 * Register a new command
	 *
	 * @param string $name The name of the new command (e.g. "help")
	 * @param CLI_Method $callback_obj The CLI Method object to call
	 */
	public static function register_command($name, $callback_obj) {
		if (!isset(CLI_Manager::$commands[$name])) {
			if ($callback_obj instanceof CLI_Method)
				CLI_Manager::$commands[$name] = $callback_obj;
			else
				print $GLOBALS['i18n']['framework']['cli_err3'] . "\n";
		} else {
			print $GLOBALS['i18n']['framework']['cli_err1'] . "\n";
		}
	}
	
	/**
	 * Do we have a given command?
	 *
	 * @param string $name The name of the command to check
	 * @return boolean True if we have the specified command, false if not
	 */
	public static function has_command($name) {
		return $name == $GLOBALS['i18n']['framework']['cli_help'] || isset(CLI_Manager::$commands[$name]);
	}
	
	/**
	 * Print a CLI Banner
	 *
	 * @internal
	 */
	public static function print_banner() {
		print $GLOBALS['i18n']['framework']['cli_banner'] . "\n";
	}
	
	/**
	 * Print Help Text
	 *
	 * @internal
	 * @param args $args The arguments for the command
	 */
	public static function print_help($args) {
		CLI_Manager::print_banner();
		if (count($args) == 0) {
			print $GLOBALS['i18n']['framework']['cli_helpintro'] . "\n";
			print "\033[1;33mhelp\033[0m\n";
			foreach (CLI_Manager::$commands as $name => $obj) {
				print "\033[1;33m" . $name . "\033[0m\n";
			}
			print $GLOBALS['i18n']['framework']['cli_helpintro2'];
		} else {
			$command = $args[1];
			if (isset(CLI_Manager::$commands[$command]))
				CLI_Manager::$commands[$command]->print_help(array_slice($args, 1));
			else
				print $GLOBALS['i18n']['framework']['cli_err2'] . "\n";
		}
	}
	
	/**
	 * Parse a method that was input by the user
	 *
	 * @internal
	 * @param string $method The name of the method to call
	 * @param args $args The arguments for the command
	 */
	public static function parse($method, $args = array()) {
		if ($method == $GLOBALS['i18n']['framework']['cli_help'])
			return CLI_Manager::print_help($args);
		foreach (CLI_Manager::$commands as $name => $obj) {
			if ($name == $method)
				return $obj->run($args);
		}
		print $GLOBALS['i18n']['framework']['cli_err2'] . "\n";
	}
}

