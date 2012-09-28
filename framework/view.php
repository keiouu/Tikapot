<?php
/*
 * Tikapot View System
 *
 */

require_once(home_dir . "framework/views/init.php");

class View
{
	protected $url, $page;
	
	public function __construct($url, $page = "") {
		$this->set_url($url);
		$this->set_page($page);
		
		global $view_manager;
		$view_manager->add($this);
	}
	
	public function set_url($url) {
		$this->url = $url;
	}
	
	public function get_url() {
		return $this->url;
	}
	
	public function set_page($page) {
		$this->page = $page;
	}
	
	public function get_page() {
		return $this->page;
	}
	
	public function setup($request, $args) {
		return true;
	}
	
	public function on_setup_success($request, $args) {}
	public function on_setup_failure($request, $args) {}
	public function pre_render($request, $args) {}
	public function post_render($request, $args) {}
	
	/* Request is a 'Request' object. By default this simply includes $this->page be sure to override for more complex things! */
	public function render($request, $args) {
		include($this->page);
	}
}
?>

