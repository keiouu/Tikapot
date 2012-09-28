<?php
/**
 * Pre-Processors modify the request just before the output is to be sent
 * 
 * @author James Thompson
 * @package Tikapot\Framework\Processing
 */

require_once(home_dir . "framework/processing/processor.php");
require_once(home_dir . "framework/signal_manager.php");

/**
 * A post processor is defined by:
 *    -> An object that takes an input, modifys it, and then outputs a result
 *    -> Is used after a page load, where modify(&$data) is expected to modify the output of the page
 */
abstract class Post_Processor extends Processor
{
	/**
	 * Constructor
	 */
	public function __construct() {
		SignalManager::hook("page_load_end", "modify", $this);
	}
}
