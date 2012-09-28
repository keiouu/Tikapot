<?php
/**
 * Tikapot default 500 View
 *
 * @author James Thompson
 * @package Tikapot\Framework\Views
 */

require_once(home_dir . "framework/view.php");
require_once(home_dir . "framework/views/html.php");

/**
 * A 500 error page
 */
class Default500 extends BasicHTMLView
{
	/**
	 * Construct
	 */
	public function __construct() {
		parent::__construct("/500.php");
	}
	
	/**
	 * Render the view
	 * 
	 * @param  Tikapot\Framework\Request $request The Request object for this view chain
	 * @param  array $args    Arguments sent to this view
	 * @return string          The value to print to the screen
	 */
	public function render($request, $args) {
		print $GLOBALS['i18n']['framework']["500"];
	}
}

