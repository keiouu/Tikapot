<?php
/**
 * Processors are Tikapot 2.0's way of pre and post-processing
 * request output. Think of it like anti-aliasing for HTML.
 * 
 * @author James Thompson
 * @package Tikapot\Framework\Processing
 */

/**
 * A processor is defined by:
 *    -> An object that takes an input, modifys it, and then outputs a result
 */
abstract class Processor
{	
	/**
	 * Take $data and modify it
	 * 
	 * @param mixed $data The data to modify
	 */
	abstract function modify($data);
}
