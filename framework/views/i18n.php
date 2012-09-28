<?php
/**
 * Tikapot i18n View
 *
 * @author James Thompson
 * @package Tikapot\Framework\Views
 */

require_once(home_dir . "framework/view.php");

/**
 * The i18nJS view prints out the i18n scripts as Javascript variables
 */
class i18nJSView extends View
{
	/**
	 * Render the view
	 * 
	 * @param  Tikapot\Framework\Request $request The Request object for this view chain
	 * @param  array $args    Arguments sent to this view
	 * @return string          The value to print to the screen
	 */
	public function render($request, $args) {
		header('Content-type: text/javascript');
		print( $request->i18n->buildJS() );
	}
}

