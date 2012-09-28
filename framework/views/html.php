<?php
/**
 * Tikapot HTML View
 *
 * @author James Thompson
 * @package Tikapot\Framework\Views
 */

require_once(home_dir . "framework/view.php");

/**
 * A simple HTML page printer
 */
class BasicHTMLView extends View
{
	protected 	/** The title of this view */ $title, 
				/** Any extra CSS to send to the page */ $style, 
				/** Any Javascript to send to the page */ $script, 
				/** MetaData for the page */ $meta;
	
	/**
	 * Construct
	 * 
	 * @param string $url    The URL for this view
	 * @param string $title  The title of this view
	 * @param string $style  Any extra CSS to send to the page
	 * @param string $script Any Javascript to send to the page
	 * @param string $meta   MetaData for the page
	 */
	public function __construct($url, $title = "", $style = "", $script = "", $meta = "") {
		parent::__construct($url);
		$this->title = $title;
		$this->style = $style;
		$this->script = $script;
		$this->meta = $meta;
	}
	
	/**
	 * Pre-Render the view
	 * 
	 * @param  Tikapot\Framework\Request $request The Request object for this view chain
	 * @param  array $args    Arguments sent to this view
	 * @return string          The value to print to the screen
	 */
	public function pre_render($request, $args = array()) {
		return '<!DOCTYPE html><html lang="en"><head><meta charset="utf-8" /><title>'.$this->title.'</title>'.$this->style.$this->script.$this->meta.'</head><body>';
	}
	
	/**
	 * Post-Render the view
	 * 
	 * @param  Tikapot\Framework\Request $request The Request object for this view chain
	 * @param  array $args    Arguments sent to this view
	 * @return string          The value to print to the screen
	 */
	public function post_render($request, $args = array()) {
		return '</body></html>';
	}
}
