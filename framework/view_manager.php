<?php
/*
 * Tikapot View Manager
 *
 */

require_once(home_dir . "framework/view.php");
require_once(home_dir . "framework/utils.php");
require_once(home_dir . "framework/signal_manager.php");
require_once(home_dir . "framework/structures/ARadix.php");

SignalManager::register("page_load_404");

class ViewException extends Exception {}

/*
 * The View Manager is responsible for storing,
 * parsing and displaying views.
 *
 * URLs are comprised of a number of steps and regexes
 * that allow parameters to be inserted directly into
 * the URL itself. E.g. /posts/(?P<post_name>\w+)/
 * Note: The regexes do not require wrapping
 *
 */
class ViewManager
{
	private $views;
	
	public function __construct() {
		$this->views = new ARadix();
	}
	
	public function setup($request) {
		list($view, $args) = $this->get($request->page);
		$request->args = $args;
		if ($view->setup($request, $args)) {
			$view->on_setup_success($request, $args);
			return true;
		}
		return false;
	}
	
	public function render_setup_fail($request) {
		list($view, $args) = $this->get($request->page);
		print $view->on_setup_failure($request, $args);
	}
	
	public function render($request) {
		list($view, $args) = $this->get($request->page);
		print $view->pre_render($request, $args);
		print $view->render($request, $args);
		print $view->post_render($request, $args);
	}
	
	// Trim leading and trailing /
	public function strip_url($url) {
		if (starts_with($url, "/"))
			$url  = substr($url, 1);
		if (ends_with($url, "/"))
			$url  = substr($url, 0, -1);
		return $url;
	}

	/*
	 * Splits the URL into sub-sections and adds it to a tree
	 */
	public function add($view) {
		$url = $this->strip_url($view->get_url());
		$pieces = explode("/", $url);
		$count = count($pieces) - 1;
		$branches = array();
		foreach ($pieces as $i => $piece) {
			$ar = new ARadix($piece);
			if ($i == $count)
				$ar = new ARadix($piece, $view);
			$branches[$i] = $ar;
			if ($i > 0)
				$branches[$i - 1]->add($ar);
		}
		if (count($branches) > 0)
			$this->views->add($branches[0]);
	}
	
	/*
	 * Search the tree, find the right view
	 */
	public function get($url) {
		$url = $this->strip_url($url);
		$pieces = explode("/", $url);
		try {
			$result = $this->views->query($pieces);
			$view = $result[0];
			$args = $result[1];
			if (!$view)
				throw new Exception($GLOBALS['i18n']['framework']["pnfe"]);
			return array($view, $args);
		}
		catch (Exception $e) {
			SignalManager::fire("page_load_404", $url);
			if ($url == "404.php")
				return array(new Default404(), 0);
			return $this->get("/404.php");
		}
	}
}

?>

