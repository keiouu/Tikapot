<?php
/**
 * Tikapot Template Tag base
 *
 * @author James Thompson
 * @package Tikapot\Framework
 */

require_once(home_dir . "framework/views/template.php");

/**
 * An abstract Template Tag class
 */
abstract class TplTag
{
	/**
	 * Register this tag with a given view
	 * 
	 * @param  Tikapot\Framework\View $view A view
	 * @return null
	 */
	public static function register($view) {
		$view->register_tag(new static());
	}
	
	/**
	 * Register this tag with the global template manager
	 * 
	 * @return null
	 */
	public static function register_global() {
		TemplateView::register_global_tag(new static());
	}
	
	/**
	 * Render the tag
	 *
	 * @internal
	 * @param  Framework\Request $request   Request Object
	 * @param  Array $args      The arguments passed by the render manager
	 * @param  string $page      The current HTML of the page
	 * @param  string $local_app The name of the application this page belongs too (if any)
	 * @return string            The resulting page
	 */
	public function render($request, $args, $page, $local_app) {
		return $page;
	}
}
