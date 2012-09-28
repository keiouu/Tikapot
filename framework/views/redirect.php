<?php
/**
 * Tikapot redirect View
 *
 * @author James Thompson
 * @package Tikapot\Framework\Views
 */

require_once(home_dir . "framework/view.php");

/**
 * A simple way to redirect from one place to another
 */
class RedirectView extends View
{
	protected /** The URL we are to redirect too */ $redirect_url;

	/**
	 * Construct
	 * 
	 * @param string $url          The URL of this view
	 * @param string $redirect_url The URL to redirect too
	 */
	public function __construct($url, $redirect_url) {
		parent::__construct($url);
		$this->redirect_url = $redirect_url;
	}
	
	/**
	 * Setup the view
	 * 
	 * @param  Tikapot\Framework\Request $request The Request object for this view chain
	 * @param  array $args    Arguments sent to this view
	 * @return boolean True if we should proceed with the render, false if not
	 */
	public function setup($request, $args) {
		header("Location: " . $this->redirect_url);
	}
}

