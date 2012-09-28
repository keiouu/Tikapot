<?php
/**
 * Tikapot error View
 *
 * @author James Thompson
 * @package Tikapot\Framework\Views
 */

require_once(home_dir . "framework/view.php");
require_once(home_dir . "framework/views/html.php");

/**
 * Displays errors in a nice format
 */
class ErrorView extends BasicHTMLView
{
	/**
	 * Construct
	 * 
	 * @param string $url    The URL for this view
	 * @param string $title  The title of this view
	 * @param string $style  Any extra CSS to send to the page
	 * @param string $script Any Javascript to send to the page
	 * @param string $meta   MetaData for the page
	 */
	public function __construct($url = "", $title = "", $style = "", $script = "", $meta = "") {
		$style .= '<style type="text/css">
			body {
				margin: 0;
				font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
				font-size: 13px;
				font-weight: normal;
				line-height: 18px;
				color: #404040;
			}
			.container {width: 50%; margin: auto auto; text-align: center;}
			.left, .stack {text-align: left;}
			
			.stack {line-height: 6px; padding-bottom: 10px;}
			.stack ul {line-height: 17px;}
		</style>';
		parent::__construct($url, $title, $style, $script, $meta);
	}
	
	/**
	 * Render the view
	 * 
	 * @param  Tikapot\Framework\Request $request The Request object for this view chain
	 * @param  Exception $error    The exception to view
	 * @return string          The value to print to the screen
	 */
	public function render($request, $error) {
		print '<div class="container">';
		print '<h1>' . $request->i18n['framework']["stack_title"] . '</h1>';
		print '<h3>' . $request->i18n['framework']["stack_desc"] . '</h3>';
		print '<p>' . $request->i18n['framework']["stack_err"] . '<br /><strong>' . $error->getMessage() . '</strong></p>';
		print '<h2 class="left">' . $request->i18n['framework']["stack"] . '</h2>';
		foreach ($error->getTrace() as $issue) {
			print '<div class="stack">';
			print '<p class="file"><strong>' . $request->i18n['framework']["stack_file"] . '</strong> ' . $issue["file"] . ' (' . $request->i18n['framework']["stack_line"] . ' '. $issue["line"].')</p>';
			print '<p class="func"><strong>' . $request->i18n['framework']["stack_func"] . '</strong> ' . $issue["function"] . '</p>';
			print '<p class="args"><strong>' . $request->i18n['framework']["stack_args"] . '</strong><ul>';
			foreach ($issue["args"] as $arg) {
				print '<li>';
				if (is_array($arg)) {
					print_r($arg);
				} else {
					if (!is_object($arg) || (is_object($arg) && method_exists($arg, '__toString')))
						print $arg;
					else
						print $request->i18n['framework']['stack_objtyp'] . ' "' . get_class($arg) . '"';
				}
				print '</li>';
			}
			print '</ul></p>';
			print '</div>';
		}
		print '</div>';
	}
}

