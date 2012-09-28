<?php
/**
 * Pre-Processors modify the request before any output has been sent
 * 
 * @author James Thompson
 * @package Tikapot\Framework\Processing
 */

require_once(home_dir . "framework/processing/processor.php");
require_once(home_dir . "framework/signal_manager.php");

/**
 * A pre processor is defined by:
 *    -> An object that takes an input, modifys it, and then outputs a result
 *    -> Is used before a page load, where modify(&$data) is expected to modify the request variables of a request
 */
abstract class Pre_Processor extends Processor
{
	/**
	 * Constructor
	 */
	public function __construct() {
		SignalManager::hook("page_load_start", "modify", $this);
	}
}
