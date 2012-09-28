<?php
/**
 * Tikapot CLI Method Interface
 *
 * @author James Thompson
 * @package Tikapot\Framework
 * @subpackage Interfaces
 */
interface CLI_Method
{
	/**
	 * Print help for this command
	 * @param args $args The arguments for this command
	 */
	public function print_help($args);
	/**
	 * Run this command
	 * @param args $args The arguments for this command
	 */
	public function run($args);
}
