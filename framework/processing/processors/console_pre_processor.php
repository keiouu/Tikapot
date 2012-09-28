<?php
/**
 * This is for adding media to the manager
 * 
 * @author James Thompson
 * @package Tikapot\Framework\Processing\Processors
 */

require_once(home_dir . "framework/processing/pre_processor.php");

/**
 * Add console media files to the media manager
 */
class Console_Pre_Processor extends Pre_Processor
{	
	/**
	 * Take $data and modify it to include the TP console css/js
	 * 
	 * @param Framework\Request $data The request object to work on
	 */
	public function modify($data) {
		$data->media->add_file(home_dir . "media/css/tp_console.css");
		$data->media->add_file(home_dir . "media/js/jquery.min.js");
		$data->media->add_file(home_dir . "media/js/tp_console.js");
		$data->media->enable_processor();
	}
}
