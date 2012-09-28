<?php
/**
 * Tikapot default 403 View
 *
 * @author James Thompson
 * @package Tikapot\Framework\Views
 */

require_once(home_dir . "framework/view.php");
require_once(home_dir . "framework/views/html.php");

/**
 * The default 403 error page view
 */
class Default403 extends BasicHTMLView {
	/**
	 * Construct the view
	 */
	public function __construct() {
		parent::__construct("/403.php");
	}
	
	/**
	 * Render the view
	 * 
	 * @param  Tikapot\Framework\Request $request The Request object for this view chain
	 * @param  array $args    Arguments sent to this view
	 * @return string          The value to print to the screen
	 */
	public function render($request, $args) {
		print $GLOBALS['i18n']['framework']["403"];
	}
}

