<?php
/**
 * Tikapot Static View
 *
 * @author James Thompson
 * @package Tikapot\Framework\Views
 */

require_once(home_dir . "framework/view.php");
require_once(home_dir . "framework/tpcache.php");

/**
 * A StaticView heavily caches pages, given a version and expiry
 */
class StaticView extends View
{
	protected 	/** The version number for this page */ $version, 
				/** The expiry date for this page's cache */ $expiry;

	/**
	 * Construct
	 * 
	 * @param string  $url     This view's URL
	 * @param string  $page    The filename for this View
	 * @param string  $version A version number for this page, used to reset cache
	 * @param integer $expiry  An expiry time for this page
	 */
	public function __construct($url, $page = "", $version = "1", $expiry = 86400) {
		$this->version = $version;
		$this->expiry = $expiry;
		parent::__construct($url, $page);
	}
	
	/**
	 * Render the view
	 * 
	 * @param  Tikapot\Framework\Request $request The Request object for this view chain
	 * @param  array $args    Arguments sent to this view
	 * @return string          The value to print to the screen
	 */
	public function render($request, $args) {
		// First, check to ensure memcache works
		if (!TPCache::avaliable()) {
			return parent::render($request, $args);
		}
		
		// It does! Check the cache out..
		$cache = TPCache::get($this->version.$this->page);
		if ($cache !== false) {
			return $cache;
		}
		
		// Doesnt exist! Render and add
		ob_start();
		print parent::render($request, $args);
		$page = ob_get_clean();
		TPCache::set($this->version.$this->page, $page, $this->expiry);
			
		return $page;
	}
}

